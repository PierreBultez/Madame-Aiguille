#!/usr/bin/env bash
# Mesure d'audience Matomo (lot 9) sur stats.madame-aiguille.fr, installée nativement à côté de la boutique.
#
# Prérequis : un enregistrement DNS A « stats.madame-aiguille.fr » vers l'IP du VPS.
# - Paquet officiel Matomo, signature GPG vérifiée contre l'empreinte publiée par Matomo (FAQ « How do I verify the
#   cryptographic GPG signature of Matomo packages? ») ;
# - utilisateur système `matomo` sans connexion, /var/www/matomo, pool PHP-FPM 8.5 dédié ;
# - base et utilisateur MariaDB `matomo`, mot de passe généré ici et rangé dans ~/matomo-mariadb.txt (0600), jamais
#   affiché ni versionné ;
# - hôte nginx durci (nginx/matomo.conf) et certificat Let's Encrypt propre ;
# - archivage des rapports par cron (core:archive chaque heure), rotation des journaux.
#
# Rejouable : ce qui existe est gardé, ce qui est remplacé est copié avant (`*.avant-99-<date>`), et un `nginx -t`
# ou `php-fpm -t` en échec remet la copie en place et arrête le script. Ne met pas Matomo à jour : voir
# docs/guide-exploitation.md §8. Usage, depuis la racine du dépôt :
#   tar -C deploy -cz . | ssh <vps> 'D=$(mktemp -d) && tar -C "$D" -xz && bash "$D/serveur/99-matomo.sh"; rm -rf "$D"'
set -euo pipefail

HERE="$(cd "$(dirname "$0")" && pwd)"
DOMAIN=stats.madame-aiguille.fr
VERSION=5.14.2
# Clé de signature des paquets depuis Matomo 4.8.0
FINGERPRINT=F529A27008477483777FC23D63BB30D0E5D2C749
ROOT=/var/www/matomo
RUN_USER=matomo
DB=matomo
SECRET="$HOME/matomo-mariadb.txt"
POOL=/etc/php/8.5/fpm/pool.d/matomo.conf
CONF=/etc/nginx/sites-available/matomo
LOGS=/var/log/matomo
STAMP="$(date +%Y%m%d-%H%M%S)"
WORK="$(mktemp -d)"
trap 'rm -rf "$WORK"' EXIT

# Copie un gabarit à sa place ; garde l'ancien fichier s'il diffère. Renvoie le chemin de la copie (vide sinon).
installer() {
    local source="$1" cible="$2" copie=""
    if sudo test -f "$cible"; then
        if sudo cmp -s "$source" "$cible"; then
            return 0
        fi
        copie="$(dirname "$cible")/$(basename "$cible").avant-99-$STAMP"
        # Hors des dossiers lus par nginx et PHP-FPM, sinon la copie serait chargée comme une configuration
        case "$cible" in
            /etc/nginx/sites-available/*) copie="/etc/nginx/$(basename "$cible").avant-99-$STAMP" ;;
            /etc/php/*/pool.d/*) copie="/etc/php/8.5/fpm/$(basename "$cible").avant-99-$STAMP" ;;
        esac
        sudo cp -a "$cible" "$copie"
    fi
    sudo install -m 0644 "$source" "$cible"
    echo "$copie"
}

# 1. Le DNS doit déjà pointer ici, sinon Let's Encrypt refusera le certificat
IP_SERVEUR="$(curl -s4 https://ifconfig.me || true)"
IP_DNS="$(getent ahostsv4 "$DOMAIN" | awk 'NR==1 {print $1}')"
if [ -z "$IP_DNS" ] || [ "$IP_DNS" != "$IP_SERVEUR" ]; then
    echo "ARRÊT : $DOMAIN résout vers « ${IP_DNS:-rien} », le serveur est « $IP_SERVEUR ». Ajouter l'enregistrement A d'abord."
    exit 1
fi

# 2. Extensions PHP demandées par Matomo
MANQUE="$(for m in pdo_mysql mysqli gd mbstring xml curl zip intl; do php8.5 -m | grep -qix "$m" || echo "$m"; done)"
if [ -n "$MANQUE" ]; then
    echo "ARRÊT : extensions PHP 8.5 absentes : $MANQUE"
    exit 1
fi

# 3. Utilisateur d'exécution ; nginx (www-data) lit les ressources statiques par le groupe
id -u "$RUN_USER" >/dev/null 2>&1 \
    || sudo useradd --system --no-create-home --home-dir "$ROOT" --shell /usr/sbin/nologin "$RUN_USER"
sudo usermod -aG "$RUN_USER" www-data
sudo install -d -o "$RUN_USER" -g "$RUN_USER" -m 2750 "$ROOT"
sudo install -d -o "$RUN_USER" -g adm -m 0750 "$LOGS"

# 4. Paquet officiel, signature vérifiée, seulement pour une installation neuve
INSTALLEE="$(sudo sed -n "s/.*const VERSION = '\([^']*\)'.*/\1/p" "$ROOT/core/Version.php" 2>/dev/null || true)"
if [ -z "$INSTALLEE" ]; then
    curl -sfL -o "$WORK/matomo.tar.gz" "https://builds.matomo.org/matomo-$VERSION.tar.gz"
    curl -sfL -o "$WORK/matomo.tar.gz.asc" "https://builds.matomo.org/matomo-$VERSION.tar.gz.asc"
    curl -sfL -o "$WORK/signature.asc" https://builds.matomo.org/signature.asc
    export GNUPGHOME="$WORK/gnupg"
    install -d -m 0700 "$GNUPGHOME"
    gpg --quiet --batch --import "$WORK/signature.asc"
    # VALIDSIG porte l'empreinte de la clé principale en dernier champ : elle doit être celle publiée par Matomo
    if ! gpg --batch --status-fd 1 --verify "$WORK/matomo.tar.gz.asc" "$WORK/matomo.tar.gz" 2>/dev/null \
        | grep -q "^\[GNUPG:\] VALIDSIG .* $FINGERPRINT$"; then
        echo "ARRÊT : signature du paquet Matomo $VERSION invalide ou clé inattendue. Rien n'a été installé."
        exit 1
    fi
    sudo tar -xzf "$WORK/matomo.tar.gz" -C "$ROOT" --strip-components=1 --no-same-owner matomo/
    sudo chown -R "$RUN_USER:$RUN_USER" "$ROOT"
    sudo find "$ROOT" -type d -exec chmod 2750 {} +
    sudo find "$ROOT" -type f -exec chmod 0640 {} +
    echo "Matomo $VERSION installé (signature vérifiée, clé $FINGERPRINT)."
elif [ "$INSTALLEE" = "$VERSION" ]; then
    echo "Matomo $VERSION déjà installé : fichiers gardés."
else
    echo "Matomo $INSTALLEE déjà installé (script écrit pour $VERSION) : fichiers gardés. Mise à jour : guide d'exploitation §8."
fi

# 5. Base et utilisateur MariaDB dédiés ; le mot de passe n'est créé qu'une fois et ne passe jamais en argument
sudo mariadb <<SQL
CREATE DATABASE IF NOT EXISTS \`$DB\` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
SQL
EXISTE="$(sudo mariadb -N -e "SELECT COUNT(*) FROM mysql.user WHERE User = '$DB' AND Host = 'localhost'")"
if [ "$EXISTE" = "0" ]; then
    MOT="$(openssl rand -base64 48 | tr -dc 'A-Za-z0-9' | head -c 32)"
    sudo mariadb <<SQL
CREATE USER '$DB'@'localhost' IDENTIFIED BY '$MOT';
SQL
    install -m 0600 /dev/null "$SECRET"
    printf "Base MariaDB de Matomo, pour l'assistant d'installation\nserveur : localhost\nutilisateur : %s\nmot de passe : %s\nbase : %s\npréfixe des tables : matomo_\n" \
        "$DB" "$MOT" "$DB" > "$SECRET"
    unset MOT
    echo "Utilisateur MariaDB « $DB » créé ; identifiants dans $SECRET (0600)."
elif [ -f "$SECRET" ]; then
    echo "Utilisateur MariaDB « $DB » déjà présent ; identifiants dans $SECRET."
else
    echo "ATTENTION : l'utilisateur MariaDB « $DB » existe mais $SECRET est absent. Mot de passe inchangé :"
    echo "            il est dans $ROOT/config/config.ini.php si l'assistant a été fait."
fi
# Droits demandés par Matomo (FAQ « What are the MySQL database privileges required? »), sur sa seule base
sudo mariadb <<SQL
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, ALTER, CREATE TEMPORARY TABLES, LOCK TABLES
    ON \`$DB\`.* TO '$DB'@'localhost';
SQL

# 6. PHP-FPM
COPIE="$(installer "$HERE/php-fpm/matomo.conf" "$POOL")"
if ! sudo php-fpm8.5 -t; then
    if [ -n "$COPIE" ]; then sudo cp -a "$COPIE" "$POOL"; else sudo rm -f "$POOL"; fi
    echo "ARRÊT : configuration PHP-FPM invalide, état d'origine remis en place."
    exit 1
fi
sudo systemctl reload php8.5-fpm

# 7. Certificat : un hôte HTTP provisoire le temps du défi ACME, si le certificat n'existe pas encore
if ! sudo test -f "/etc/letsencrypt/live/$DOMAIN/fullchain.pem"; then
    if ! sudo test -f "$CONF"; then
        sudo tee "$CONF" >/dev/null <<EOF
# Provisoire (99-matomo.sh) : remplacé par nginx/matomo.conf dès le certificat obtenu
server {
    listen 80;
    listen [::]:80;
    server_name $DOMAIN;
    location / { return 404; }
}
EOF
        sudo ln -sfn "$CONF" /etc/nginx/sites-enabled/matomo
        if ! sudo nginx -t; then
            sudo rm -f /etc/nginx/sites-enabled/matomo "$CONF"
            echo "ARRÊT : configuration nginx invalide, hôte provisoire retiré."
            exit 1
        fi
        sudo systemctl reload nginx
    fi
    sudo certbot certonly --nginx --non-interactive --cert-name "$DOMAIN" -d "$DOMAIN"
fi

# 8. Hôte définitif (TLS, durcissement)
COPIE="$(installer "$HERE/nginx/matomo.conf" "$CONF")"
sudo ln -sfn "$CONF" /etc/nginx/sites-enabled/matomo
if ! sudo nginx -t; then
    if [ -n "$COPIE" ]; then
        sudo cp -a "$COPIE" "$CONF"
    else
        sudo rm -f /etc/nginx/sites-enabled/matomo "$CONF"
    fi
    echo "ARRÊT : configuration nginx invalide, état d'origine remis en place."
    exit 1
fi
sudo systemctl reload nginx
if [ -n "$COPIE" ]; then echo "Ancienne configuration nginx gardée : $COPIE"; fi

# 9. Archivage des rapports chaque heure (les tâches planifiées de Matomo, rapports par e-mail et mise à jour de la
#    base de géolocalisation, passent aussi par là) ; rien tant que l'assistant n'a pas écrit config.ini.php
echo "5 * * * * [ -f $ROOT/config/config.ini.php ] && /usr/bin/php8.5 -d memory_limit=1G $ROOT/console core:archive --url=https://$DOMAIN/ >> $LOGS/archive.log 2>&1" \
    | sudo crontab -u "$RUN_USER" -
sudo tee /etc/logrotate.d/matomo >/dev/null <<EOF
$LOGS/*.log {
    weekly
    rotate 8
    compress
    delaycompress
    missingok
    notifempty
    copytruncate
    su $RUN_USER adm
}
EOF

# 10. Contrôles
sleep 2
echo "HTTP              : $(curl -s -o /dev/null -w '%{http_code} → %{redirect_url}' "http://$DOMAIN/") (301 vers https attendu)"
echo "Interface         : $(curl -s -o /dev/null -w '%{http_code}' "https://$DOMAIN/index.php") (200 attendu)"
echo "matomo.js         : $(curl -s -o /dev/null -w '%{http_code}' "https://$DOMAIN/matomo.js") (200 attendu)"
for chemin in config/config.ini.php config/global.ini.php tmp/ lang/fr.json core/Version.php plugins/Login/Login.php vendor/autoload.php console tests/README.md; do
    echo "/$chemin : $(curl -s -o /dev/null -w '%{http_code}' "https://$DOMAIN/$chemin") (403 attendu)"
done
echo "Ensuite : https://$DOMAIN/ pour l'assistant d'installation (identifiants de la base : cat $SECRET)."
