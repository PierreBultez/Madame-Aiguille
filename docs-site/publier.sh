#!/usr/bin/env bash
# Construit le site de documentation depuis docs/*.md et le publie sur guide-magento.pierrebultez.com.
# Prérequis : deploy/serveur/96-guide-magento.sh joué une fois, et l'alias SSH du VPS (par défaut
# « madame-aiguille », à changer par PUBLICATION_SSH=<alias>). Usage : docs-site/publier.sh
set -euo pipefail

ICI="$(cd "$(dirname "$0")" && pwd)"
SSH_HOTE="${PUBLICATION_SSH:-madame-aiguille}"
DESTINATION=/var/www/guide-magento/

cd "$ICI"
npm ci --no-audit --no-fund
npm run build
rsync -az --delete --chmod=D2755,F644 src/.vitepress/dist/ "$SSH_HOTE:$DESTINATION"
echo "Publié : https://guide-magento.pierrebultez.com/"
