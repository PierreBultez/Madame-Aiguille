#!/usr/bin/env bash
# Installation neuve de Magento dans une version déjà envoyée par GitHub Actions (une seule fois).
#
# Génère sur le serveur, sans jamais les afficher, les mots de passe de la base et de RabbitMQ et le chemin de
# l'administration : ils ne vivent que dans shared/app/etc/env.php (0600, utilisateur madame-aiguille).
# Aucun compte administrateur n'est créé ici : Pierre le crée lui-même (commande affichée en fin de script).
#
# Réglages propres à ce serveur : robots NOINDEX,NOFOLLOW, Varnish, webhooks Mollie, Mollie en mode test,
# double authentification par Google Authenticator. Les secrets (SMTP, clés Mollie et reCAPTCHA) se saisissent
# ensuite dans l'administration.
#
# Usage : depuis une copie du dossier deploy/ envoyée sur le serveur (deploy/serveur/README.md) :
#   bash serveur/80-installer-magento.sh <version>
set -euo pipefail

HERE="$(cd "$(dirname "$0")" && pwd)"
BASE=/var/www/madame-aiguille
RUN_USER=madame-aiguille
DOMAIN=madame-aiguille.fr
DB=madame_aiguille
AMQP_USER=madame-aiguille
AMQP_VHOST=/madame-aiguille

RELEASE="${1:?Version attendue (nom du dossier dans releases/)}"
DIR="$BASE/releases/$RELEASE"
[ -d "$DIR" ] || { echo "Version introuvable : $DIR"; exit 1; }
[ ! -f "$BASE/shared/app/etc/env.php" ] || { echo "Déjà installé : utiliser deploy/bascule.sh"; exit 1; }

secret() { openssl rand -base64 48 | tr -d '/+=\n' | cut -c1-32; }
magento() { sudo -u "$RUN_USER" php "$DIR/bin/magento" --no-ansi "$@"; }

# 1. Base de données et file de messages dédiées
DB_PASSWORD="$(secret)"
sudo mariadb <<EOF
CREATE DATABASE IF NOT EXISTS $DB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE OR REPLACE USER '$DB'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON $DB.* TO '$DB'@'localhost';
EOF

AMQP_PASSWORD="$(secret)"
sudo rabbitmqctl -q add_vhost "$AMQP_VHOST" 2>/dev/null || true
sudo rabbitmqctl -q add_user "$AMQP_USER" "$AMQP_PASSWORD" 2>/dev/null \
    || sudo rabbitmqctl -q change_password "$AMQP_USER" "$AMQP_PASSWORD"
sudo rabbitmqctl -q set_permissions -p "$AMQP_VHOST" "$AMQP_USER" '.*' '.*' '.*'

# 2. Version : éléments partagés ; première installation seulement, setup:install peut réécrire ces dossiers
rm -rf "$DIR/var" "$DIR/pub/media"
ln -sfn "$BASE/shared/var" "$DIR/var"
ln -sfn "$BASE/shared/pub/media" "$DIR/pub/media"
chmod -R g+w "$DIR/app/etc" "$DIR/generated" "$DIR/pub/static"

ADMIN_PATH="gestion_$(openssl rand -hex 5)"
magento setup:install --no-interaction \
    --base-url="https://$DOMAIN/" --base-url-secure="https://$DOMAIN/" \
    --use-secure=1 --use-secure-admin=1 --use-rewrites=1 \
    --backend-frontname="$ADMIN_PATH" \
    --db-host=localhost --db-name="$DB" --db-user="$DB" --db-password="$DB_PASSWORD" \
    --language=fr_FR --currency=EUR --timezone=Europe/Paris \
    --search-engine=opensearch --opensearch-host=127.0.0.1 --opensearch-port=9200 \
    --opensearch-index-prefix=madame_aiguille \
    --cache-backend=valkey --cache-backend-valkey-server=127.0.0.1 --cache-backend-valkey-port=6380 \
    --cache-backend-valkey-db=0 \
    --page-cache=valkey --page-cache-valkey-server=127.0.0.1 --page-cache-valkey-port=6380 \
    --page-cache-valkey-db=1 \
    --session-save=redis --session-save-redis-host=127.0.0.1 --session-save-redis-port=6381 \
    --session-save-redis-db=0 \
    --amqp-host=127.0.0.1 --amqp-port=5672 --amqp-user="$AMQP_USER" --amqp-password="$AMQP_PASSWORD" \
    --amqp-virtualhost="$AMQP_VHOST" \
    --http-cache-hosts=127.0.0.1:6081 --consumers-wait-for-messages=0
unset DB_PASSWORD AMQP_PASSWORD

# 3. env.php rejoint les éléments partagés, lisible par le seul utilisateur d'exécution
sudo mv "$DIR/app/etc/env.php" "$BASE/shared/app/etc/env.php"
sudo chown "$RUN_USER:$RUN_USER" "$BASE/shared/app/etc/env.php"
sudo chmod 0600 "$BASE/shared/app/etc/env.php"
ln -sfn "$BASE/shared/app/etc/env.php" "$DIR/app/etc/env.php"

magento deploy:mode:set production --skip-compilation
# Si setup:install a vidé le code généré ou les fichiers statiques du build, ils sont reconstruits ici
[ -d "$DIR/generated/metadata" ] || magento setup:di:compile
[ -d "$DIR/pub/static/frontend/MadameAiguille/default/fr_FR" ] || {
    magento setup:static-content:deploy --jobs 4 --area frontend \
        --theme MadameAiguille/default --theme MadameAiguille/checkout fr_FR
    magento setup:static-content:deploy --jobs 4 --area adminhtml --theme Magento/backend fr_FR en_US
}

# 4. Réglages propres à ce serveur
magento config:set design/search_engine_robots/default_robots NOINDEX,NOFOLLOW
magento config:set system/full_page_cache/caching_application 2
magento config:set system/full_page_cache/varnish/backend_host 127.0.0.1
magento config:set system/full_page_cache/varnish/backend_port 8080
magento config:set system/full_page_cache/varnish/access_list 127.0.0.1
magento config:set system/full_page_cache/varnish/grace_period 300
magento config:set payment/mollie_general/type test
magento config:set payment/mollie_general/use_webhooks enabled
magento config:set twofactorauth/general/force_providers google
magento indexer:set-mode schedule
magento madameaiguille:shipping:import-rates
magento madameaiguille:catalog:check-weight

# 5. VCL de Magento pour Varnish 7
magento varnish:vcl:generate --export-version=7 --access-list=127.0.0.1 \
    --backend-host=127.0.0.1 --backend-port=8080 --grace-period=300 --output-file="$BASE/shared/var/default.vcl"
sudo install -m 0644 "$BASE/shared/var/default.vcl" /etc/varnish/default.vcl
sudo systemctl reload varnish

# 6. Cron Magento sous l'utilisateur d'exécution
echo "* * * * * /usr/bin/php8.5 $BASE/current/bin/magento cron:run 2>&1 | grep -v 'Ran jobs by schedule' >> $BASE/shared/var/log/magento.cron.log" \
    | sudo crontab -u "$RUN_USER" -

# 7. Mise en ligne de cette version, puis activation du site dans nginx
bash "$HERE/../bascule.sh" "$RELEASE"
sudo install -m 0644 "$HERE/nginx/madame-aiguille.conf" /etc/nginx/sites-available/madame-aiguille
sudo ln -sfn /etc/nginx/sites-available/madame-aiguille /etc/nginx/sites-enabled/madame-aiguille
sudo nginx -t
sudo systemctl reload nginx

echo
echo "Administration : https://$DOMAIN/$ADMIN_PATH/ (chemin à conserver dans votre gestionnaire de mots de passe)"
echo "Créer votre compte administrateur (le mot de passe vous sera demandé) :"
echo "  sudo -u $RUN_USER php $BASE/current/bin/magento admin:user:create"
