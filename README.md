# Madame Aiguille

Boutique en ligne d'une créatrice d'accessoires textiles cousus main, en séries limitées de cinq à dix pièces.

**Magento Open Source 2.4.9** avec le thème **Hyvä 1.5.2**, thème enfant `MadameAiguille/default` et modules maison `MadameAiguille_Theme`, `MadameAiguille_Contact` et `MadameAiguille_Checkout`.

## Organisation du dépôt

| Chemin | Contenu |
|---|---|
| `shop/` | L'application Magento. Seuls `app/code`, `app/design` et `app/etc/config.php` sont versionnés |
| `shop/app/design/frontend/MadameAiguille/default/` | Le thème enfant : layouts, templates, sources Tailwind, assets |
| `shop/app/design/frontend/MadameAiguille/checkout/` | Le thème enfant Luma du tunnel : LESS, templates Knockout, logo et fontes |
| `shop/app/code/MadameAiguille/` | Les modules : ViewModels, blocs, contrôleurs, data patches, configuration |
| `docs/` | Cahier des charges, spécification fonctionnelle, plan de développement, documentation du thème, maquettes |
| `docs/prompts/` | Briefs de reprise, un par lot |

`vendor/`, `generated/`, `var/`, `pub/static/`, `pub/media/` et `app/etc/env.php` ne sont pas versionnés : ils se reconstruisent ou contiennent des secrets.

## Documentation

- **[`docs/documentation-theme.md`](docs/documentation-theme.md)** — où modifier quoi, dans le code comme dans le back-office. Le **§24 « Mémo Céline »** récapitule tout ce qui se règle sans toucher au code.
- **[`docs/commandes-et-deploiement.md`](docs/commandes-et-deploiement.md)** — commandes `bin/magento` maison, scripts, déploiement par versions et retour arrière : l'aide-mémoire de Pierre.
- **[`docs/plan-de-developpement.md`](docs/plan-de-developpement.md)** — découpage en lots, décisions prises, ce qui reste.
- **[`docs/cahier-des-charges-docs-developpement/`](docs/cahier-des-charges-docs-developpement/)** — cahier des charges, spécification fonctionnelle, architecture, charte graphique, guide de bonnes pratiques Hyvä, plan de tests.

## Fontes — à récupérer avant tout build

⚠️ **Les fichiers de fonte ne sont pas dans ce dépôt.** Britney et Sentient sont distribuées sous *ITF Free Font License*, qui interdit leur redistribution — y compris via un dépôt public. Seuls les textes de licence (`FFL-*.txt`) sont versionnés.

Pour rendre le thème fonctionnel, télécharger les deux familles sur **[fontshare.com](https://www.fontshare.com/)** et placer les fichiers WOFF2 dans :

```
shop/app/design/frontend/MadameAiguille/default/web/fonts/
```

Graisses utilisées : `Britney-Regular`, `Sentient-Light`, `Sentient-Regular`, `Sentient-Medium`, `Sentient-Italic`. Les fichiers sont servis tels quels — la licence interdit le subsetting, la conversion de format et la modification des métadonnées.

Le tunnel Luma utilise **Sentient-Regular**, **Sentient-Medium** et **Sentient-Italic** : copier également leurs WOFF2 originaux dans `shop/app/design/frontend/MadameAiguille/checkout/web/fonts/`. Ne jamais forcer leur ajout dans Git.

Les maquettes de `docs/maquettes-direction-artistique/` attendent les mêmes fichiers dans leur propre dossier `fonts/`.

## Chaîne de build CSS

Tailwind v4, sans `tailwind.config.js` : toute la configuration vit dans `tailwind-source.css` et `hyva.config.json`.

```bash
cd shop/app/design/frontend/MadameAiguille/default/web/tailwind
npm ci          # une fois (Node ≥ 20, cf. .nvmrc)
npm run watch   # développement
npm run build   # production
```

`web/css/styles.css` est généré et n'est pas versionné : il se régénère à chaque build. Le thème Luma `checkout` possède un build LESS distinct, par la commande Magento `setup:static-content:deploy -f --theme MadameAiguille/checkout fr_FR` ; détails et pièges de cache au §27 de la documentation du thème.

## Déploiement

Build dans GitHub Actions (*Actions › Déploiement › Run workflow*), publication par versions successives sur le VPS, provisionnement en scripts : voir [`deploy/serveur/README.md`](deploy/serveur/README.md) et le §29 de la documentation du thème. Les fontes sont téléchargées par le build depuis Fontshare et vérifiées (`deploy/fontes.sh`).

## Commandes Magento utiles

```bash
bin/magento cache:clean full_page block_html   # après un template ou un bloc CMS
bin/magento setup:upgrade --keep-generated     # nouveau module, nouveau data patch
bin/magento setup:di:compile                   # OBLIGATOIRE après un nouveau plugin
vendor/bin/phpunit -c dev/tests/unit/phpunit.xml.dist --no-extensions app/code/MadameAiguille/Theme/Test/Unit
```

## Avancement

| Lot | Contenu | État |
|---|---|---|
| 1 | Fondations : thème enfant, Tailwind, tokens, header, footer, styleguide | Livré |
| 2 | Catalogue : modèle de données, page catégorie, fiche produit | Livré |
| 6a | Checkout Luma fallback — installation | Livré |
| 3 | Accueil, pages CMS, formulaire de contact, états vides | Livré |
| 4 | Panier et mini-panier | Livré |
| 7 | Compte client, emails, statuts de commande | Livré |
| 5 | Livraison et paiements (Mollie, table rates) | Livré hors préproduction et validation Mollie |
| 6b | Checkout — habillage et confirmations | Livré ; recette et limites dans `docs/recettes/lot-6b.md` |
| 8a | Domaine, HTTPS, SMTP, serveur et déploiement | Livré : en ligne sur `madame-aiguille.fr`, non indexé ; recette de production au lot 8 |
| 8 | Recette de production, back-office, exploitation, ouverture des ventes | Prochain lot |

Le détail de chaque lot, les décisions prises et les points ouverts sont dans [`docs/plan-de-developpement.md`](docs/plan-de-developpement.md).

## Licences

Magento Open Source est distribué sous OSL-3.0, le thème Hyvä sous BSD-3-Clause. Les fontes Britney et Sentient restent la propriété d'Indian Type Foundry (ITF Free Font License, voir les fichiers `FFL-*.txt`). Les visuels, photos et contenus de la boutique appartiennent à leur autrice.
