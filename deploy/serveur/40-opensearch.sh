#!/usr/bin/env bash
# OpenSearch 3.x pour Magento 2.4.9 (dépôt de 10-depots.sh), même version majeure que le poste de développement.
#
# Nœud unique, écoute sur 127.0.0.1 seulement (9200/9300 fermés par le pare-feu), tas de 2 Go.
# Greffon de sécurité désactivé : aucun accès réseau extérieur, et Magento s'y connecte sans authentification.
# L'installateur exige un mot de passe administrateur initial : généré au vol, jamais affiché ni conservé.
# Les certificats de démonstration qu'il dépose (clés privées publiques) sont supprimés.
#
# Déjà appliqué le 09/10/2026. Usage : ssh <vps> 'bash -s' < deploy/serveur/40-opensearch.sh
set -euo pipefail

if ! dpkg -s opensearch >/dev/null 2>&1; then
    password="$(openssl rand -base64 18 | tr -d '/+=')Aa1!"
    sudo env OPENSEARCH_INITIAL_ADMIN_PASSWORD="$password" DEBIAN_FRONTEND=noninteractive apt-get install -y opensearch
    unset password
fi

sudo tee /etc/opensearch/opensearch.yml >/dev/null <<'EOF'
# Madame Aiguille — OpenSearch 3 pour Magento 2.4.9 (nœud unique, accès local uniquement)
cluster.name: madame-aiguille
node.name: vps-74c719db
path.data: /var/lib/opensearch
path.logs: /var/log/opensearch
network.host: 127.0.0.1
http.port: 9200
transport.port: 9300
discovery.type: single-node
# Écoute limitée à 127.0.0.1 et pare-feu fermé sur 9200 : pas de TLS ni d authentification internes
plugins.security.disabled: true
action.destructive_requires_name: true
EOF
printf -- '-Xms2g\n-Xmx2g\n' | sudo tee /etc/opensearch/jvm.options.d/heap.options >/dev/null

for demo in esnode.pem esnode-key.pem kirk.pem kirk-key.pem root-ca.pem securityadmin_demo.sh; do
    sudo rm -f "/etc/opensearch/$demo"
done

sudo systemctl daemon-reload
sudo systemctl enable opensearch >/dev/null
sudo systemctl restart opensearch
for _ in $(seq 1 30); do curl -s localhost:9200 >/dev/null && break; sleep 2; done
curl -s 'localhost:9200/_cluster/health?filter_path=status,number_of_nodes'; echo
