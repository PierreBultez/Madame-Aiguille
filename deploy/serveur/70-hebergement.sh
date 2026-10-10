#!/usr/bin/env bash
# Hébergement de la boutique sur le VPS, avant la première installation (une seule fois, rejouable).
#
# - Utilisateur système `madame-aiguille`, sans connexion, qui exécute PHP-FPM, le cron et les commandes Magento.
#   Pierre (envoi du code) et www-data (nginx sert pub/static et pub/media) rejoignent son groupe.
# - /var/www/madame-aiguille : releases/, shared/ (env.php, var, pub/media, sauvegardes), lien current.
# - Pool PHP-FPM dédié, nginx (TLS → Varnish → backend 8080), certificat Let's Encrypt unique pour
#   madame-aiguille.fr ET www.madame-aiguille.fr, rotation des journaux, réglages MariaDB pour Magento.
#
# Les gabarits sont à côté du script : il s'exécute depuis une copie du dossier deploy/ envoyée sur le serveur
# (commande dans deploy/serveur/README.md).
set -euo pipefail

HERE="$(cd "$(dirname "$0")" && pwd)"
BASE=/var/www/madame-aiguille
RUN_USER=madame-aiguille
DEPLOY_USER="$(id -un)"
DOMAIN=madame-aiguille.fr

# 1. Utilisateur d'exécution et groupes
id -u "$RUN_USER" >/dev/null 2>&1 \
    || sudo useradd --system --no-create-home --home-dir "$BASE" --shell /usr/sbin/nologin "$RUN_USER"
sudo usermod -aG "$RUN_USER" "$DEPLOY_USER"
sudo usermod -aG "$RUN_USER" www-data

# 2. Arborescence : code lisible par le groupe ; var privé ; médias lisibles par nginx
sudo install -d -o "$DEPLOY_USER" -g "$RUN_USER" -m 2750 \
    "$BASE" "$BASE/releases" "$BASE/shared" "$BASE/shared/app" "$BASE/shared/pub"
sudo install -d -o "$DEPLOY_USER" -g "$RUN_USER" -m 2770 "$BASE/shared/app/etc"
sudo install -d -o "$RUN_USER" -g "$RUN_USER" -m 2750 "$BASE/shared/pub/media"
sudo install -d -o "$RUN_USER" -g "$RUN_USER" -m 0700 "$BASE/shared/var"
sudo install -d -o "$RUN_USER" -g "$RUN_USER" -m 0750 "$BASE/shared/var/log"
sudo install -d -o "$DEPLOY_USER" -g "$DEPLOY_USER" -m 0700 "$BASE/shared/sauvegardes"

# 3. MariaDB : tampon InnoDB et paquets à la taille de Magento (le serveur a 45 Go de RAM)
sudo tee /etc/mysql/mariadb.conf.d/60-madame-aiguille.cnf >/dev/null <<'EOF'
# Madame Aiguille — réglages recommandés par Magento 2.4
[mysqld]
innodb_buffer_pool_size = 2G
max_allowed_packet = 64M
EOF
sudo systemctl restart mariadb

# 4. PHP-FPM
sudo install -m 0644 "$HERE/php-fpm/madame-aiguille.conf" /etc/php/8.5/fpm/pool.d/madame-aiguille.conf
sudo php-fpm8.5 -t
sudo systemctl reload php8.5-fpm

# 5. Certificat unique pour les deux noms (greffon nginx de certbot, renouvelé par certbot.timer)
if ! sudo test -f "/etc/letsencrypt/live/$DOMAIN/fullchain.pem"; then
    sudo certbot certonly --nginx --non-interactive --cert-name "$DOMAIN" -d "$DOMAIN" -d "www.$DOMAIN"
fi
sudo certbot certificates --cert-name "$DOMAIN" 2>/dev/null | grep -E 'Domains|Expiry'

# Copiée seulement : elle inclut current/nginx.conf.sample, activée par 80-installer-magento.sh une fois la
# première version en place
sudo install -m 0644 "$HERE/nginx/madame-aiguille.conf" /etc/nginx/sites-available/madame-aiguille

# 6. Journaux de Magento : rotation hebdomadaire, huit semaines
sudo tee /etc/logrotate.d/madame-aiguille >/dev/null <<EOF
$BASE/shared/var/log/*.log {
    weekly
    rotate 8
    compress
    delaycompress
    missingok
    notifempty
    copytruncate
    su $RUN_USER $RUN_USER
}
EOF

echo "Hébergement prêt. Prochaine étape : une version envoyée par GitHub Actions, puis 80-installer-magento.sh."
