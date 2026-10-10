#!/usr/bin/env bash
# MariaDB 12.3 depuis le dépôt officiel (10-depots.sh), aligné sur le poste de développement.
#
# Avant toute mise à jour : dump complet de toutes les bases (y compris celles des autres sites du VPS) et copie
# de /etc/mysql dans ~/sauvegardes/. Une version majeure de MariaDB ne se rétrograde pas : le dump est le retour
# arrière. Les fichiers de configuration existants sont conservés (--force-confold).
#
# Usage : ssh <vps> 'bash -s' < deploy/serveur/20-mariadb.sh
set -euo pipefail

BACKUP="$HOME/sauvegardes/mariadb-$(date +%Y%m%d-%H%M)"
mkdir -p "$BACKUP"
chmod 700 "$HOME/sauvegardes" "$BACKUP"

if systemctl is-active --quiet mariadb; then
    echo "Version avant : $(mariadb --version)"
    sudo mariadb-dump --all-databases --single-transaction --routines --events --triggers \
        | gzip > "$BACKUP/toutes-bases.sql.gz"
    sudo cp -a /etc/mysql "$BACKUP/etc-mysql"
    echo "Sauvegarde : $BACKUP ($(du -sh "$BACKUP" | cut -f1))"
    zcat "$BACKUP/toutes-bases.sql.gz" | tail -1 | grep -q 'Dump completed' || { echo 'Dump incomplet : arrêt'; exit 1; }
fi

sudo DEBIAN_FRONTEND=noninteractive apt-get install -y \
    -o Dpkg::Options::=--force-confold \
    mariadb-server mariadb-client mariadb-backup

sudo systemctl enable --now mariadb
sudo mariadb-upgrade
echo "Version après : $(mariadb --version)"
sudo mariadb -N -e 'SELECT VERSION(); SELECT table_schema, COUNT(*) FROM information_schema.tables
    WHERE table_schema NOT IN ("mysql", "information_schema", "performance_schema", "sys") GROUP BY table_schema;'
