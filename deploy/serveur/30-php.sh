#!/usr/bin/env bash
# PHP 8.5 (paquets natifs d'Ubuntu 26.04), mêmes extensions que le poste de développement et que l'ancien PHP 8.4
# du VPS. OPcache est intégré à PHP 8.5 : pas de paquet séparé.
#
# Réglages repris du PHP 8.4 du VPS dans conf.d/99-serveur.ini (le php.ini du paquet n'est pas modifié).
# Le pool `www` sert les autres sites du VPS. Magento aura son propre pool, livré avec sa configuration nginx.
#
# Déjà appliqué le 10/10/2026. Usage : ssh <vps> 'bash -s' < deploy/serveur/30-php.sh
set -euo pipefail

sudo DEBIAN_FRONTEND=noninteractive apt-get install -y \
    php8.5-fpm php8.5-cli php8.5-bcmath php8.5-curl php8.5-gd php8.5-intl php8.5-mbstring php8.5-mysql \
    php8.5-soap php8.5-xml php8.5-zip php8.5-readline php8.5-igbinary php8.5-imagick php8.5-memcached \
    php8.5-msgpack php8.5-redis php8.5-pcov php8.5-pgsql php8.5-xmlrpc

for sapi in fpm cli; do
    sudo tee "/etc/php/8.5/$sapi/conf.d/99-serveur.ini" >/dev/null <<'EOF'
; Réglages repris de PHP 8.4 (10/10/2026, mise à niveau Ubuntu 26.04)
realpath_cache_size = 10M
realpath_cache_ttl = 7200
max_execution_time = 1800
memory_limit = 4G
post_max_size = 1G
upload_max_filesize = 1G
date.timezone = Europe/Paris
opcache.enable = 1
opcache.memory_consumption = 256
opcache.max_accelerated_files = 20000
opcache.revalidate_freq = 2
opcache.save_comments = 1
EOF
done

sudo systemctl enable --now php8.5-fpm
sudo systemctl restart php8.5-fpm
php -v | head -1
