#!/usr/bin/env bash
# Ajoute « Cache-Control: no-cache » aux pages du site guide-magento.pierrebultez.com, déjà installé.
#
# Sans en-tête de cache, le navigateur gardait d'anciennes pages et l'ancien favicon après une publication
# (constat du 02/11/2026 : publication en ligne, pas visible chez Pierre). Le gabarit versionné
# deploy/serveur/nginx/guide-magento.conf porte déjà la correction. Copie, test, retour arrière ; rejouable.
# Usage : ssh <vps> 'bash -s' < deploy/serveur/98-cache-guide.sh
set -euo pipefail

CONF=/etc/nginx/sites-available/guide-magento
COPIE=/etc/nginx/guide-magento.conf.avant-cache
URL=https://guide-magento.pierrebultez.com/

if sudo grep -q 'Cache-Control "no-cache"' "$CONF"; then
    echo "En-tête déjà présent : rien à faire."
else
    sudo cp "$CONF" "$COPIE"
    # Après la ligne Referrer-Policy du niveau server (4 espaces), une seule fois même si Certbot a dupliqué le bloc
    sudo sed -i '0,/^    add_header Referrer-Policy .*always;$/s//&\n    add_header Cache-Control "no-cache" always;/' "$CONF"
    if ! sudo nginx -t; then
        sudo cp "$COPIE" "$CONF"
        echo "ARRÊT : configuration nginx invalide, copie d'origine remise en place."
        exit 1
    fi
    sudo systemctl reload nginx
    echo "En-tête ajouté (copie : $COPIE)."
fi

echo "Page d'accueil : $(curl -sI "$URL" | grep -i '^cache-control' || echo 'Cache-Control absent')"
echo "Fichier empreinté : $(curl -sI "${URL}assets/$(curl -s "$URL" | grep -o 'assets/[^"]*\.css' | head -1 | cut -d/ -f2)" | grep -i '^cache-control' || echo '?')"
