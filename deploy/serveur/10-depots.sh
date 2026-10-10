#!/usr/bin/env bash
# Dépôts APT externes du VPS (Ubuntu 26.04 « resolute »), au format deb822.
#
# - MariaDB 12.3 : dépôt officiel, même source que le poste de développement.
# - OpenSearch 3.x : dépôt officiel (désactivé par la mise à niveau 25.10 → 26.04).
# - RabbitMQ / Erlang : dépôt de l'équipe RabbitMQ. Pas encore de « resolute » chez eux (404 au 10/10/2026) :
#   on garde « noble » (24.04), comme avant la mise à niveau.
#
# Idempotent. Usage : ssh <vps> 'bash -s' < deploy/serveur/10-depots.sh
set -euo pipefail

sudo install -d -m 0755 /etc/apt/keyrings

# Clés publiées en ASCII : converties en binaire pour Signed-By
curl -fsSL https://mariadb.org/mariadb_release_signing_key.pgp \
    | sudo gpg --dearmor --batch --yes -o /etc/apt/keyrings/mariadb-keyring.gpg
[ -f /usr/share/keyrings/opensearch-release-keyring ] || curl -fsSL https://artifacts.opensearch.org/publickeys/opensearch-release.pgp \
    | sudo gpg --dearmor --batch --yes -o /usr/share/keyrings/opensearch-release-keyring
[ -f /usr/share/keyrings/com.rabbitmq.team.gpg ] || curl -fsSL https://keys.openpgp.org/vks/v1/by-fingerprint/0A9AF2115F4687BD29803A206B73A36E6026DFCA \
    | sudo gpg --dearmor --batch --yes -o /usr/share/keyrings/com.rabbitmq.team.gpg

sudo tee /etc/apt/sources.list.d/mariadb.sources >/dev/null <<'EOF'
Types: deb
URIs: https://deb.mariadb.org/12.3/ubuntu
Suites: resolute
Components: main
Signed-By: /etc/apt/keyrings/mariadb-keyring.gpg
EOF

sudo tee /etc/apt/sources.list.d/opensearch-3.x.sources >/dev/null <<'EOF'
Types: deb
URIs: https://artifacts.opensearch.org/releases/bundle/opensearch/3.x/apt
Suites: stable
Components: main
Signed-By: /usr/share/keyrings/opensearch-release-keyring
EOF

sudo tee /etc/apt/sources.list.d/rabbitmq.sources >/dev/null <<'EOF'
# Erlang/OTP moderne
Types: deb
URIs: https://deb1.rabbitmq.com/rabbitmq-erlang/ubuntu/noble https://deb2.rabbitmq.com/rabbitmq-erlang/ubuntu/noble
Suites: noble
Components: main
Architectures: amd64
Signed-By: /usr/share/keyrings/com.rabbitmq.team.gpg

# RabbitMQ moderne
Types: deb
URIs: https://deb1.rabbitmq.com/rabbitmq-server/ubuntu/noble https://deb2.rabbitmq.com/rabbitmq-server/ubuntu/noble
Suites: noble
Components: main
Architectures: amd64
Signed-By: /usr/share/keyrings/com.rabbitmq.team.gpg
EOF

# Fichiers laissés par la mise à niveau : remplacés par les .sources ci-dessus
sudo rm -f /etc/apt/sources.list.d/opensearch-3.x.list.disabled \
    /etc/apt/sources.list.d/rabbitmq.list \
    /etc/apt/sources.list.d/rabbitmq.list.disabled

sudo apt-get update -qq
apt-cache policy mariadb-server opensearch rabbitmq-server erlang-base | grep -E '^[a-z]|Installed|Candidate'
