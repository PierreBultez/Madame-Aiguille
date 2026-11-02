#!/usr/bin/env bash
# Ajoute « client_max_body_size 64m » au bloc nginx backend (127.0.0.1:8080) de la boutique, déjà installé.
#
# Sans cette ligne, nginx garde sa limite par défaut de 1 Mo derrière Varnish : une photo de 3,7 Mo envoyée par le
# formulaire de contact reçoit « 413 Request Entity Too Large » (recette du 02/11/2026). Le gabarit versionné
# deploy/serveur/nginx/madame-aiguille.conf porte déjà la correction. Copie, test, retour arrière ; rejouable.
# Usage : ssh <vps> 'bash -s' < deploy/serveur/97-taille-envois.sh
set -euo pipefail

CONF=/etc/nginx/sites-available/madame-aiguille
COPIE=/etc/nginx/madame-aiguille.conf.avant-taille-envois

if sudo awk '/listen 127\.0\.0\.1:8080;/{b=1} b&&/client_max_body_size/{f=1} b&&/^}/{exit} END{exit !f}' "$CONF"; then
    echo "Limite déjà présente dans le bloc backend : rien à faire."
else
    sudo cp "$CONF" "$COPIE"
    sudo sed -i '/listen 127\.0\.0\.1:8080;/a\    client_max_body_size 64m;' "$CONF"
    if ! sudo nginx -t; then
        sudo cp "$COPIE" "$CONF"
        echo "ARRÊT : configuration nginx invalide, copie d'origine remise en place."
        exit 1
    fi
    sudo systemctl reload nginx
    echo "Limite ajoutée au bloc backend (copie : $COPIE)."
fi

echo "Bloc backend :"
sudo awk '/listen 127\.0\.0\.1:8080;/{b=1} b{print} b&&/^}/{exit}' "$CONF"
