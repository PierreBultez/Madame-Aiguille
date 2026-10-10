#!/usr/bin/env bash
# Provisionne les WOFF2 originaux de Britney et Sentient dans les deux thèmes, depuis Fontshare.
#
# La licence ITF (FFL) interdit leur redistribution, y compris par un dépôt public : ils ne sont jamais versionnés.
# Ce script les télécharge à la source, vérifie leur empreinte SHA-256 (deploy/fontes.sha256, relevée sur les
# fichiers d'origine) et les copie tels quels : ni conversion, ni sous-ensemble, ni modification.
#
# Usage, depuis la racine du dépôt : deploy/fontes.sh
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
THEMES="$ROOT/shop/app/design/frontend/MadameAiguille"
WORK="$(mktemp -d)"
trap 'rm -rf "$WORK"' EXIT

for family in britney sentient; do
    curl -fsSL -o "$WORK/$family.zip" "https://api.fontshare.com/v2/fonts/download/$family"
    unzip -q -j "$WORK/$family.zip" '*/Fonts/WEB/fonts/*.woff2' -d "$WORK/fonts"
done

(cd "$WORK/fonts" && sha256sum --check --strict "$ROOT/deploy/fontes.sha256")

# Le thème Hyvä utilise les cinq graisses, le tunnel Luma Sentient Regular, Medium et Italic (README)
install -m 0644 "$WORK"/fonts/{Britney-Regular,Sentient-Light,Sentient-Regular,Sentient-Medium,Sentient-Italic}.woff2 \
    "$THEMES/default/web/fonts/"
install -m 0644 "$WORK"/fonts/{Sentient-Regular,Sentient-Medium,Sentient-Italic}.woff2 "$THEMES/checkout/web/fonts/"
echo "Fontes originales provisionnées dans les thèmes default et checkout."
