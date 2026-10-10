#!/usr/bin/env bash
# Restreint l'administration : une IP de la liste ouvre directement la page Magento ; depuis ailleurs (déplacement,
# smartphone), nginx demande d'abord l'identifiant et le mot de passe HTTP (satisfy any). La vitrine, le tunnel et
# le webhook Mollie restent publics. Le robot de Google ne voit plus le formulaire de connexion, signalé à tort
# comme hameçonnage le 10/10/2026.
#
# Le chemin de l'administration est lu dans env.php et n'est écrit que dans /etc/nginx/snippets/ : jamais dans le
# dépôt public. Le mot de passe HTTP est créé par Pierre au préalable (deploy/serveur/README.md).
#
# Rejouable pour changer la liste d'IP, depuis une copie du dossier deploy/ envoyée sur le serveur :
#   bash serveur/90-acces-admin.sh <ip> [<ip>…]
set -euo pipefail

HERE="$(cd "$(dirname "$0")" && pwd)"
HTPASSWD=/etc/nginx/madame-aiguille-admin.htpasswd
SNIPPET=/etc/nginx/snippets/madame-aiguille-admin.conf

[ "$#" -ge 1 ] || { echo "Au moins une IP autorisée est attendue"; exit 1; }
for ip in "$@"; do
    [[ "$ip" =~ ^[0-9a-fA-F:.]+(/[0-9]{1,3})?$ ]] || { echo "IP invalide : $ip"; exit 1; }
done
sudo test -s "$HTPASSWD" || { echo "Créer d'abord le mot de passe HTTP (README)"; exit 1; }

ADMIN="$(sudo -u madame-aiguille php -r '$e = require $argv[1]; echo $e["backend"]["frontName"];' \
    /var/www/madame-aiguille/shared/app/etc/env.php)"
[[ "$ADMIN" =~ ^[A-Za-z0-9_]+$ ]] || { echo "Chemin d'administration illisible"; exit 1; }

{
    echo "# Généré par deploy/serveur/90-acces-admin.sh le $(date '+%d/%m/%Y %H:%M') — hors dépôt"
    for prefix in "/$ADMIN" "/index.php/$ADMIN"; do
        echo "location ^~ $prefix {"
        echo "    satisfy any;"
        for ip in "$@"; do echo "    allow $ip;"; done
        echo "    deny all;"
        echo "    auth_basic \"Madame Aiguille\";"
        echo "    auth_basic_user_file $HTPASSWD;"
        echo "    proxy_pass http://127.0.0.1:6081;"
        echo "}"
    done
} | sudo tee "$SNIPPET" >/dev/null
sudo chmod 0640 "$SNIPPET"
sudo chown root:www-data "$SNIPPET"

sudo install -m 0644 "$HERE/nginx/madame-aiguille.conf" /etc/nginx/sites-available/madame-aiguille
sudo nginx -t
sudo systemctl reload nginx

# Depuis le serveur, absent de la liste : le mot de passe doit être exigé ; la vitrine reste ouverte
echo "Administration sans mot de passe : $(curl -s -o /dev/null -w '%{http_code}' "https://madame-aiguille.fr/$ADMIN/") (401 attendu)"
echo "Vitrine : $(curl -s -o /dev/null -w '%{http_code}' https://madame-aiguille.fr/) (200 attendu)"
echo "Webhook Mollie : $(curl -s -o /dev/null -w '%{http_code}' -X POST -d testByMollie=1 https://madame-aiguille.fr/mollie/checkout/webhook/) (200 attendu)"
echo "IP autorisées : $*"
