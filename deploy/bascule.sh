#!/usr/bin/env bash
# Bascule vers une version déjà envoyée dans releases/ (appelé par GitHub Actions, ou à la main pour un retour
# arrière) : ssh <vps> 'bash -s -- <version>' < deploy/bascule.sh
#
# 1. Branche les éléments partagés : app/etc/env.php, var/, pub/media/.
# 2. Si la base ou la configuration importée doivent évoluer : mode maintenance, sauvegarde de la base, puis
#    setup:upgrade --keep-generated (le code généré vient du build). Sinon, aucune coupure.
# 3. Lien current changé atomiquement, PHP-FPM rechargé (OPcache), caches vidés (Varnish compris).
# 4. Contrôle d'environnement ; garde les cinq dernières versions.
#
# Toutes les commandes Magento tournent sous l'utilisateur du pool PHP, pour que les fichiers de var/ lui
# appartiennent. Retour arrière : relancer ce script avec le nom d'une version précédente (et restaurer la
# sauvegarde de la base si setup:upgrade a tourné).
set -euo pipefail

BASE=/var/www/madame-aiguille
RUN_USER=madame-aiguille
FPM=php8.5-fpm
KEEP=5

RELEASE="${1:?Version attendue (nom du dossier dans releases/)}"
DIR="$BASE/releases/$RELEASE"
[ -d "$DIR" ] || { echo "Version introuvable : $DIR"; exit 1; }

if [ ! -f "$BASE/shared/app/etc/env.php" ]; then
    echo "Aucune installation : lancer d'abord deploy/serveur/80-installer-magento.sh $RELEASE"
    exit 1
fi

magento() {
    sudo -u "$RUN_USER" php "$DIR/bin/magento" --no-ansi "$@"
}

ln -sfn "$BASE/shared/app/etc/env.php" "$DIR/app/etc/env.php"
# Magento vérifie que app/etc est inscriptible avant d'écrire env.php ou config.php
chmod g+w "$DIR/app/etc" "$DIR/app/etc/config.php"
rm -rf "$DIR/var" "$DIR/pub/media"
ln -sfn "$BASE/shared/var" "$DIR/var"
ln -sfn "$BASE/shared/pub/media" "$DIR/pub/media"

# setup:db:status / app:config:status : 0 = à jour, 2 = mise à jour nécessaire, autre = erreur
NEEDS_UPGRADE=0
for check in setup:db:status app:config:status; do
    code=0
    magento "$check" >/dev/null || code=$?
    case "$code" in
        0) ;;
        2) NEEDS_UPGRADE=1 ;;
        *) echo "$check : code $code, bascule annulée"; exit 1 ;;
    esac
done

if [ "$NEEDS_UPGRADE" -eq 1 ]; then
    magento maintenance:enable
    SAVE="$BASE/shared/sauvegardes/avant-$RELEASE.sql.gz"
    DB="$(sudo -u "$RUN_USER" php -r '$e = require $argv[1]; echo $e["db"]["connection"]["default"]["dbname"];' "$BASE/shared/app/etc/env.php")"
    sudo mariadb-dump --single-transaction --routines --events --triggers "$DB" | gzip > "$SAVE"
    echo "Sauvegarde de la base : $SAVE"
    magento setup:upgrade --keep-generated
fi

ln -sfn "$DIR" "$BASE/current.next"
mv -Tf "$BASE/current.next" "$BASE/current"
sudo systemctl reload "$FPM"
# nginx inclut current/nginx.conf.sample : relu à chaque version
sudo nginx -t -q && sudo systemctl reload nginx || echo "nginx -t en échec : configuration nginx non rechargée"
magento cache:flush
if [ "$NEEDS_UPGRADE" -eq 1 ]; then
    magento maintenance:disable
fi

echo "Version en ligne : $RELEASE"
magento madameaiguille:env:check --serveur --noindex || echo "Contrôle d'environnement en erreur : voir ci-dessus"

# Ménage : les KEEP versions les plus récentes, jamais celle en ligne
CURRENT="$(basename "$(readlink -f "$BASE/current")")"
ls -1d "$BASE"/releases/*/ | sort -r | tail -n +"$((KEEP + 1))" | while read -r old; do
    [ "$(basename "$old")" = "$CURRENT" ] || rm -rf "$old"
done
