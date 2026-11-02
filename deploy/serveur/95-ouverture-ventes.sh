#!/usr/bin/env bash
# Ouverture des ventes : retire l'en-tête « X-Robots-Tag: noindex, nofollow » de la configuration nginx installée
# (posée par 70-hebergement.sh pour la préproduction). À lancer après le déploiement qui passe robots en
# INDEX,FOLLOW (patch OpenStoreToSearchEngines). Copie de sauvegarde, test nginx avant rechargement, retour à la
# copie en cas d'échec. Rejouable : sans l'en-tête, il ne fait que les contrôles. Usage :
#   ssh <vps> 'bash -s' < deploy/serveur/95-ouverture-ventes.sh
set -euo pipefail

CONF=/etc/nginx/sites-available/madame-aiguille
COPIE=/etc/nginx/madame-aiguille.conf.avant-ouverture
SITE=https://madame-aiguille.fr

if sudo grep -q 'X-Robots-Tag' "$CONF"; then
    sudo cp "$CONF" "$COPIE"
    sudo sed -i \
        -e '/Avant l.ouverture des ventes : aucune indexation/d' \
        -e '/À retirer le jour de l.ouverture, avec le réglage robots/d' \
        -e '/add_header X-Robots-Tag "noindex, nofollow" always;/d' \
        "$CONF"
    if ! sudo nginx -t; then
        sudo cp "$COPIE" "$CONF"
        echo "ARRÊT : configuration nginx invalide, copie d'origine remise en place."
        exit 1
    fi
    sudo systemctl reload nginx
    echo "En-tête X-Robots-Tag retiré (copie : $COPIE)."
else
    echo "En-tête X-Robots-Tag déjà absent."
fi

echo "En-tête de l'accueil : $(curl -sI "$SITE/" | grep -i '^x-robots-tag' || echo 'aucun X-Robots-Tag (attendu)')"
echo "Balise robots de l'accueil : $(curl -s "$SITE/?ouverture=$(date +%s)" | grep -o '<meta name="robots"[^>]*>' | head -1)"
echo "robots.txt :"
curl -s "$SITE/robots.txt"
echo
echo "Sitemap : $(curl -s -o /dev/null -w '%{http_code}' "$SITE/media/sitemap.xml") (200 après la première génération, chaque nuit à 0 h ou depuis Marketing › Plan du site)"
