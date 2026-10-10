#!/usr/bin/env bash
# Valkey à la place de Redis, plus deux instances dédiées à Magento.
#
# - Instance principale `valkey-server` : remplace Redis pour les sites Laravel du VPS, sur le même port 6379 et
#   avec les mêmes réglages (127.0.0.1, 256 Mo, allkeys-lru, sans mot de passe) : aucun .env à modifier.
#   Les données Redis ne sont pas reprises : les visiteurs connectés de ces sites sont déconnectés une fois.
# - `valkey-server@magento-cache` (6380) : cache Magento, volatile, éviction LRU, sans persistance.
# - `valkey-server@magento-sessions` (6381) : sessions Magento, jamais évincées, persistées (AOF).
#
# Toutes les instances n'écoutent qu'en local. Usage :
#   ssh <vps> 'bash -s' < deploy/serveur/60-valkey.sh
set -euo pipefail

if dpkg -s redis-server >/dev/null 2>&1; then
    sudo systemctl disable --now redis-server
fi

sudo DEBIAN_FRONTEND=noninteractive apt-get install -y valkey-server valkey-tools

# Instance principale : réglages repris de Redis, ajoutés en fin de fichier (la dernière directive l'emporte)
MAIN=/etc/valkey/valkey.conf
if ! sudo grep -q '^# Madame Aiguille' "$MAIN"; then
    sudo tee -a "$MAIN" >/dev/null <<'EOF'

# Madame Aiguille — reprise des réglages de Redis pour les sites Laravel (10/10/2026)
bind 127.0.0.1 -::1
port 6379
maxmemory 256mb
maxmemory-policy allkeys-lru
EOF
fi
sudo systemctl enable valkey-server >/dev/null
sudo systemctl restart valkey-server

instance() {
    local name="$1" port="$2" body="$3"
    sudo install -d -o valkey -g valkey -m 0750 "/var/lib/valkey/$name"
    sudo tee "/etc/valkey/valkey-$name.conf" >/dev/null <<EOF
# Madame Aiguille — $name
bind 127.0.0.1 -::1
protected-mode yes
port $port
supervised systemd
daemonize no
pidfile /run/valkey-$name/valkey-server.pid
logfile /var/log/valkey/valkey-$name.log
dir /var/lib/valkey/$name
$body
EOF
    sudo chown valkey:valkey "/etc/valkey/valkey-$name.conf"
    sudo chmod 0640 "/etc/valkey/valkey-$name.conf"
    sudo systemctl enable "valkey-server@$name" >/dev/null
    sudo systemctl restart "valkey-server@$name"
}

instance magento-cache 6380 'save ""
appendonly no
maxmemory 1gb
maxmemory-policy allkeys-lru'

instance magento-sessions 6381 'save 900 1 300 10 60 10000
appendonly yes
appendfsync everysec
maxmemory 512mb
maxmemory-policy noeviction'

for port in 6379 6380 6381; do
    echo "Valkey $port : $(valkey-cli -p "$port" PING) — $(valkey-cli -p "$port" CONFIG GET maxmemory-policy | tail -1)"
done

if dpkg -s redis-server >/dev/null 2>&1; then
    sudo DEBIAN_FRONTEND=noninteractive apt-get purge -y redis-server redis-tools
    sudo rm -rf /etc/redis /var/lib/redis /var/log/redis
fi
echo "Redis : $(dpkg -l | grep -cE '^ii  redis') paquet(s) restant(s)"
