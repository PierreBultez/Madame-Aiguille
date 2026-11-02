#!/usr/bin/env bash
# Site de documentation guide-magento.pierrebultez.com (VitePress, statique), à côté des autres sites du VPS.
#
# Prérequis : un enregistrement DNS A « guide-magento.pierrebultez.com » vers l'IP du VPS.
# Crée /var/www/guide-magento (propriété de l'utilisateur qui lance le script, pour docs-site/publier.sh),
# installe la configuration nginx, la teste, recharge nginx, puis obtient le certificat Let's Encrypt
# (certbot --nginx ajoute le bloc TLS et la redirection HTTPS). Rejouable. Usage, depuis la racine du dépôt :
#   tar -C deploy -cz . | ssh <vps> 'D=$(mktemp -d) && tar -C "$D" -xz && bash "$D/serveur/96-guide-magento.sh"; rm -rf "$D"'
set -euo pipefail

HERE="$(cd "$(dirname "$0")" && pwd)"
DOMAIN=guide-magento.pierrebultez.com
ROOT=/var/www/guide-magento
CONF=/etc/nginx/sites-available/guide-magento
OWNER="$(id -un)"

# 1. Le DNS doit déjà pointer ici, sinon Let's Encrypt refusera le certificat
IP_SERVEUR="$(curl -s4 https://ifconfig.me || true)"
IP_DNS="$(getent ahostsv4 "$DOMAIN" | awk 'NR==1 {print $1}')"
if [ -z "$IP_DNS" ] || [ "$IP_DNS" != "$IP_SERVEUR" ]; then
    echo "ARRÊT : $DOMAIN résout vers « ${IP_DNS:-rien} », le serveur est « $IP_SERVEUR ». Ajouter l'enregistrement A d'abord."
    exit 1
fi

# 2. Dossier du site, lisible par nginx ; page d'attente jusqu'à la première publication
sudo install -d -o "$OWNER" -g www-data -m 2755 "$ROOT"
[ -f "$ROOT/index.html" ] || echo '<!doctype html><meta charset="utf-8"><title>Guide Madame Aiguille</title><p>Publication en cours.</p>' > "$ROOT/index.html"

# 3. nginx : la configuration n'est copiée qu'une fois, Certbot la complète ensuite (bloc TLS)
if ! sudo test -f "$CONF"; then
    sudo install -m 0644 "$HERE/nginx/guide-magento.conf" "$CONF"
    sudo ln -sf "$CONF" /etc/nginx/sites-enabled/guide-magento
fi
sudo nginx -t
sudo systemctl reload nginx

# 4. Certificat et redirection HTTPS
if ! sudo test -f "/etc/letsencrypt/live/$DOMAIN/fullchain.pem"; then
    sudo certbot --nginx --non-interactive --redirect --cert-name "$DOMAIN" -d "$DOMAIN"
fi
sudo nginx -t
sudo systemctl reload nginx

sleep 2
echo "HTTP  : $(curl -s -o /dev/null -w '%{http_code} → %{redirect_url}' "http://$DOMAIN/") (301 vers https attendu)"
echo "HTTPS : $(curl -s -o /dev/null -w '%{http_code}' "https://$DOMAIN/") (200 attendu)"
echo "Ensuite, depuis le poste : docs-site/publier.sh"
