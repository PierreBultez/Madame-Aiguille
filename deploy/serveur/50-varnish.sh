#!/usr/bin/env bash
# Varnish devant Magento uniquement : nginx (TLS, 443) → Varnish 127.0.0.1:6081 → nginx 127.0.0.1:8080 → PHP-FPM.
#
# Écoute locale seulement, cache de 1 Go en mémoire, tailles d'en-têtes relevées pour les étiquettes de cache de
# Magento (X-Magento-Tags). Le VCL de Magento remplace default.vcl après l'installation de la boutique :
#   bin/magento varnish:vcl:generate --export-version=7 --backend-host=127.0.0.1 --backend-port=8080
#
# Déjà appliqué le 09/10/2026 (Varnish 7.7.3 d'Ubuntu). Usage :
#   ssh <vps> 'bash -s' < deploy/serveur/50-varnish.sh
set -euo pipefail

sudo DEBIAN_FRONTEND=noninteractive apt-get install -y varnish

sudo install -d /etc/systemd/system/varnish.service.d
sudo tee /etc/systemd/system/varnish.service.d/madame-aiguille.conf >/dev/null <<'EOF'
# Madame Aiguille — Varnish devant Magento uniquement : nginx (TLS) -> 127.0.0.1:6081 -> nginx 127.0.0.1:8080
[Service]
ExecStart=
ExecStart=/usr/sbin/varnishd \
          -F \
          -a 127.0.0.1:6081 \
          -T 127.0.0.1:6082 \
          -f /etc/varnish/default.vcl \
          -S /etc/varnish/secret \
          -s malloc,1G \
          -p http_resp_hdr_len=65536 \
          -p http_resp_size=98304 \
          -p workspace_backend=131072 \
          -p feature=+http2
EOF

sudo systemctl daemon-reload
sudo systemctl enable varnish >/dev/null
sudo systemctl restart varnish
varnishd -V 2>&1 | head -1
sudo ss -ltnp | grep varnishd | awk '{print $4}'
