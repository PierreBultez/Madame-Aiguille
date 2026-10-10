# Documentation du thème Madame Aiguille

Document interne — v3.0 (10/10/2026), lots 6a, 3, 4, 7, 5, 6b et **8a** livrés ; boutique entièrement en français (§28), **en ligne sur `https://madame-aiguille.fr`**, non indexée, installation neuve (§29). Prochain lot : **8**, recette de production, exploitation et ouverture des ventes. **À compléter à chaque lot** (une section par écran livré).

Les tableaux « où modifier quoi » distinguent ce qui se règle **dans l'admin** (Céline, sans code) de ce qui se change **dans le code** (Pierre).

Ce document décrit ce qui a été construit, où se trouve chaque chose, et **où modifier quoi** — dans le code ou dans le back-office Magento. **Céline peut aller directement au §24**, qui récapitule tout ce qui se règle sans toucher au code. **Tout développement se termine par le §25**, le rituel de fin de lot. Il complète `guide-bonnes-pratiques-hyva.md` (conventions de code) et `charte-graphique.md` / l'artboard *Design System* (décisions visuelles).

## 1. Vue d'ensemble

| Élément | Emplacement | Rôle |
|---|---|---|
| Thème enfant | `shop/app/design/frontend/MadameAiguille/default/` | Tout le visuel : CSS, templates, layouts, fontes, images |
| Module | `shop/app/code/MadameAiguille/Theme/` | Tout le PHP : ViewModels, routes, configuration admin, data patches |
| Module du tunnel | `shop/app/code/MadameAiguille/Checkout/` | Lot 5 : point relais, retrait sur rendez-vous, paiement sur place, emballage cadeau, et leurs composants Knockout (§26) |
| Documentation | `docs/` | Cahier des charges, spécifications, charte, maquettes, ce document |

Parent : `Hyva/default` 1.5.2 (`vendor/hyva-themes/magento2-default-theme`). **On ne modifie jamais `vendor/`.** Un template du parent se surcharge en le copiant au même chemin relatif dans le thème enfant (`Magento_Theme/templates/html/header.phtml`, par exemple).

**Règle d'architecture visuelle** : toute surcharge de rendu frontend vit dans le thème enfant, même lorsqu'un module dédié porte la logique métier. Le module expose ses blocs, ViewModels, contrôleurs, validations et configurations ; le thème enfant surcharge ses layouts, `.phtml`, CSS, assets et templates JavaScript. Exemple : le traitement sécurisé de Contact reste dans `app/code/MadameAiguille/Contact`, mais son formulaire est rendu par `app/design/frontend/MadameAiguille/default/MadameAiguille_Contact/templates/form.phtml`. Le checkout Luma applique cette règle dans `MadameAiguille/checkout` (§27). Les composants fonctionnels du lot 5 restent fournis par `MadameAiguille_Checkout` ; leurs gabarits habillés sont surchargés dans ce thème Luma enfant, sans modifier leurs validations ni leurs calculs (§26).

Le thème est activé pour la vue *Default Store View* (`design/theme/theme_id = 5`, admin *Contenu › Design › Configuration*).

### Arborescence du thème

```
MadameAiguille/default/
├── theme.xml, registration.php, composer.json
├── etc/view.xml                         tailles d'images catalogue (ratio 4:5), options de galerie
├── i18n/fr_FR.csv                       traductions des libellés Hyvä rencontrés
├── Hyva_Theme/web/svg/lucide/          icônes ajoutées au jeu Lucide (pinterest.svg)
├── MadameAiguille_Theme/
│   ├── layout/madameaiguille_styleguide_index_index.xml
│   └── templates/styleguide.phtml       page de contrôle /styleguide
├── MadameAiguille_Contact/
│   ├── layout/contact_index_index.xml   composition visuelle de la page Contact
│   └── templates/form.phtml             surcharge du formulaire du module Contact
├── Magento_Catalog/
│   ├── layout/catalog_category_view.xml page catégorie (1 colonne, en-tête, tri, état vide)
│   ├── layout/catalog_list_item.xml     carte produit : ViewModel, sans liste d'envies
│   ├── layout/catalog_product_view.xml  fiche produit (blocs retirés, ViewModels, rassurance)
│   └── templates/
│       ├── category/header.phtml        titre orné + description de catégorie
│       ├── product/list.phtml           grille, séries terminées, pagination
│       ├── product/list/item.phtml      carte produit
│       ├── product/list/empty.phtml     états vides (catégorie, recherche)
│       ├── product/list/toolbar*.phtml  compteur + tri
│       ├── product/product-detail-page.phtml
│       ├── product/view/product-info.phtml   colonne d'achat
│       ├── product/view/{quantity,addtocart,gallery,details,breadcrumbs}.phtml
│       └── product/slider/product-slider.phtml  « Vous aimerez aussi »
├── Magento_CatalogSearch/
│   ├── layout/catalogsearch_result_index.xml
│   └── templates/result.phtml           page de résultats (en-tête, liste ou état vide)
├── Magento_LayeredNavigation/layout/    handles *_type_layered forcés en 1 colonne
├── Magento_Swatches/templates/product/  sélecteur de taille (renderer + swatch-item)
├── Magento_Theme/
│   ├── layout/default.xml               header, logo, footer (fusionné avec le parent)
│   ├── layout/default_head_blocks.xml   preload des fontes
│   ├── web/favicon.ico                  favicon multi-tailles issu du logo carré officiel
│   ├── web/images/logo-carre.jpg        source carrée officielle conservée dans le thème
│   └── templates/html/
│       ├── header.phtml                 header (bandeau, grille, panier)
│       ├── header/logo.phtml            logo responsive
│       ├── header/menu/desktop.phtml    navigation ≥ md
│       ├── header/menu/mobile.phtml     burger + tiroir < md
│       ├── breadcrumbs.phtml            fil d'Ariane (catégories, CMS)
│       ├── pager.phtml                  pagination 44 px
│       ├── footer.phtml                 pied de page
│       └── footer/{brand,shop,links,services,column,copyright}.phtml
└── web/
    ├── css/styles.css                   GÉNÉRÉ — jamais édité, jamais commité
    ├── fonts/                           .woff2 + licences FFL
    ├── images/logo-rectangulaire.jpg    logo officiel du header et du footer
    └── tailwind/
        ├── tailwind-source.css          point d'entrée : @source, @theme (tokens)
        ├── hyva.config.json             tokens de couleur (oklch) + inclusion du parent
        ├── src/fonts.css                @font-face
        ├── base/                        preflight Hyvä + brand.css (règles de base marque)
        ├── components/                  button, badge, card, forms, messages, layout, wrapper…
        ├── theme/                       styles de pages (copiés du parent)
        ├── utilities/                   fallback, icônes, ornaments.css (feston)
        └── generated/                   GÉNÉRÉ par npm run generate — ignoré par git
```

### Arborescence du module

```
MadameAiguille/Theme/
├── registration.php, composer.json
├── etc/module.xml, etc/config.xml (valeurs par défaut), etc/acl.xml
├── etc/adminhtml/system.xml             section admin « Madame Aiguille » (réseaux sociaux, catalogue)
├── etc/frontend/routes.xml              route /styleguide
├── etc/di.xml                           plugins de purge des caches produit
├── etc/crontab.xml                      cron nocturne du badge « Nouveauté »
├── Controller/Index/Index.php           page de contrôle (404 en production)
├── Cron/RefreshNoveltyBadges.php
├── Model/Cache/FlushProductCacheBySkus.php
├── Plugin/Inventory/FlushCacheAfter{Reservations,SourceItemsSave}.php
├── ViewModel/
│   ├── SocialLinks.php                  liens réseaux sociaux (footer)
│   ├── Catalog/Sorting.php              options du sélecteur de tri
│   ├── Product/LimitedSeries.php        TOUTES les règles séries limitées / stock / badges
│   └── Product/Characteristics.php      lignes du tableau Caractéristiques
├── Test/Unit/ViewModel/Product/LimitedSeriesTest.php
└── Setup/Patch/Data/
    ├── CreateStaticPages.php            pages CMS légales
    ├── CreateHeaderAnnouncementBlock.php bloc bandeau
    ├── CreateCatalogAttributes.php      taille, serie_limitee, taille_serie, weight requis
    ├── CreateCreationAttributeSet.php   attribute set « Création »
    ├── CreateProductDetailAttributes.php composition, dimensions, entretien
    ├── CreateProductReassuranceBlock.php bloc CMS product_reassurance
    └── MakeCreatedAtSortable.php        tri « Nouveautés »
```

## 2. Chaîne de build CSS (Tailwind v4)

```bash
cd shop/app/design/frontend/MadameAiguille/default/web/tailwind
npm ci                 # une fois (Node ≥ 20, cf. .nvmrc)
npm run watch          # développement : rebuild à chaque modification
npm run build          # production : minifié
```

`npm run generate` (lancé par les deux) produit `generated/hyva-source.css` (scan des templates du parent et des modules Hyvä listés dans `app/etc/hyva-themes.json`) et `generated/hyva-tokens.css` (tokens de `hyva.config.json`).

Après un build, en mode developer, la feuille est servie directement (`pub/static` est un lien symbolique / matérialisation automatique). En production : `bin/magento setup:static-content:deploy fr_FR`.

**Pièges connus**

- Il n'y a **pas** de `tailwind.config.js` : tout est dans `tailwind-source.css` (`@theme`, `@source`) et `hyva.config.json`.
- Tailwind v4 CLI **ne rebase pas** les `url()` des fichiers importés : dans `src/fonts.css`, les chemins sont écrits relatifs au CSS compilé (`../fonts/`).
- Une séquence `*/` dans un commentaire CSS (par ex. `gray-*/slate-*`) ferme le commentaire et casse le build.
- Les classes utilisées **uniquement** dans le CMS (pages, blocs) ne sont pas vues par le scanner : les déclarer dans `@source inline(...)` de `tailwind-source.css`, sinon elles disparaissent du CSS.
- `@utility container` est défini dans `components/wrapper.css` (marges 20 / 80 px) — ne pas le redéfinir ailleurs.
- **La feuille compilée est servie sous une URL versionnée figée** (`/static/version…/css/styles.css`). Après un build, le navigateur peut continuer à servir l'ancienne version : une règle pourtant présente dans `web/css/styles.css` semble alors « ne pas prendre ». Recharger sans cache (Ctrl+Maj+R) ; en cas de doute, comparer la taille du fichier servi et celle du fichier compilé.

## 3. Tokens de design — où changer quoi

### Couleurs

`web/tailwind/hyva.config.json` → `tokens.values.color`, en **oklch** (hex de contrôle dans `notice`). Génère `--color-<nom>` → classes `bg-<nom>`, `text-<nom>`, `border-<nom>`.

| Token | Hex | Usage |
|---|---|---|
| `brand` | #8A615C | boutons principaux, titres, liens, prix |
| `brand-dark` | #6E4D49 | survol ; texte courant sur fond blush (`.on-blush`) |
| `brand-active` | #5F4139 | bouton enfoncé |
| `brand-rose` | #C3867A | **décoratif seulement** — jamais de texte (3,0:1) |
| `brand-nude` | #E9C3AD | filets, bordures, ornements — jamais de texte |
| `brand-blush` | #F4E7E7 | fonds de section, cartes, footer |
| `brand-ivory` | #FAF0EB | fond de page |
| `brand-paper` | #FFFCFA | cartes, header |
| `brand-ink` | #5F4A41 | corps de texte |
| `brand-muted` | #77635A | texte secondaire, placeholders (4,99:1) |
| `brand-line` | #F2E0D7 | filets de cartes |
| `field-border` | #D8C3B8 | bordure des champs |
| `success` / `error` / `info` (+ `-dark`, `-bg`, `-border`) | | alertes, badges, validation |
| `disabled-bg` / `disabled-fg` / `disabled-border` / `disabled-muted` | | état désactivé unique |
| `soldout-bg` / `soldout-fg` / `soldout-border` | | badge « Épuisé » |

Dans `tailwind-source.css` (`@theme`), les tokens Hyvä sont mappés dessus (`primary → brand`, `bg → brand-ivory`, `surface → brand-paper`, `fg → brand-ink`…) et les neutres Tailwind (`gray-*`, `slate-*`, `white`, `black`, `red/green/blue/yellow`) sont **remappés** sur la palette chaude : un template hérité du parent qui utilise `border-gray-300` obtient `field-border`. Changer une couleur = changer une ligne dans `hyva.config.json`.

### Typographie

`tailwind-source.css` → `@theme` :

| Classe | Taille · interlignage | Usage |
|---|---|---|
| `text-caption` | 13 px · 1,5 | mentions, aides de saisie — **plancher** |
| `text-label` | 15 px · 1,3 | labels, boutons S |
| `text-base` | 16 px · 1,6 | corps — **plancher** |
| `text-lead` | 18 px · 1,55 | accroches (avec `italic`) |
| `text-subtitle` / `-lg` | 18 / 20 px | H3, nom en vignette, prix |
| `text-title` / `-lg` | 26 / 32 px | H1 de page, nom en fiche (Sentient Medium) |
| `text-display` | 30 px | **plancher Britney** |
| `text-display-lg` / `-xl` / `-2xl` | 32 / 40 / 64 px | H2, hero |

Familles : `font-display` (Britney — titres uniquement, ≥ 30 px, jamais `italic`) et `font-body` (Sentient, défaut du `body`). Graisses : `font-light` 300, `font-normal` 400, `font-medium` 500, `italic`. Interlettrage des intitulés en capitales : `tracking-label` (0,14 em).

Fontes : `web/fonts/*.woff2` servies telles quelles (licence ITF), déclarées dans `web/tailwind/src/fonts.css`, préchargées dans `Magento_Theme/layout/default_head_blocks.xml`. Ajouter une graisse = un `@font-face` + le fichier, rien d'autre.

### Rayons, ombres, grille

- `rounded-xs` 2 px (badges) · `rounded-sm` 4 px (champs, boutons) · `rounded-md` 6 px (cartes) · `rounded-full`. Tout au-delà est plafonné à 6 px.
- `shadow-soft` / `shadow-card` / `shadow-raised` / `shadow-drawer`, teintées `brand-ink` ; les `shadow-sm…2xl` de Tailwind y sont remappées.
- Conteneur : `container` = marges 20 px (mobile) / 80 px (≥ lg), plafond 1440 px. Points de rupture Tailwind par défaut (`sm` 640, `md` 768, `lg` 1024, `xl` 1280, `2xl` 1440).
- Espacements : échelle Tailwind (4 px). Rythme des sections : 64 px mobile / 96 px desktop (`py-16 md:py-24`).

## 4. Composants CSS

Définis dans `web/tailwind/components/` ; démonstration sur `/styleguide`.

| Classe | Fichier | Notes |
|---|---|---|
| `btn` (= secondaire), `btn-primary`, `btn-link`, `btn-size-sm` (44 px), `btn-size-lg` (56 px) | `button.css` | états survol / actif / désactivé / `aria-busy`. API `--btn-*` du parent conservée |
| `form-input`, `form-select`, `form-textarea`, `form-checkbox`, `form-radio`, `.field`, `.field-error .messages`, `.field-note` | `forms.css` + tokens `--form-*` dans `@theme` | 48 px, erreur via `aria-invalid="true"` |
| `message` + `success` / `error` / `notice` | `messages.css` | alertes Magento (messages système inclus) |
| `card`, `card-interactive` | `card.css` | |
| `badge` + `badge-new` / `badge-limited` / `badge-scarce` / `badge-soldout` | `badge.css` | |
| `scallop scallop-from-<couleur> scallop-to-<couleur>` | `utilities/ornaments.css` | feston entre deux sections |
| `header-grid`, `footer-grid` | `layout.css` | grilles du header / footer |
| `full-bleed` | `layout.css` | bande pleine largeur depuis l'intérieur du conteneur (barre de tri, sections de la fiche) ; `.page-wrapper` rogne le débordement |
| `on-blush` | `base/brand.css` | texte courant sur fond blush |
| `swatch-option[data-swatch-type="text"]` | `swatches.css` | sélecteur de taille en boutons 120 × 56 px : choisi en aplat brand, épuisé grisé et barré |
| `product-gallery`, `gallery-thumb` | `slider.css` | miniatures 4:5 du pager de la galerie |
| `pdp-price`, `pdp-price-compact` | `theme/page-catalog.css` | prix de la fiche (26 / 32 px) et de la barre collante mobile ; `.product-item` règle le prix des cartes |
| `franco-bar` (+ `-message`, `-track`, `-fill`, `-amounts`, `franco-bar-reached`) | `theme/page-cart.css` | barre de livraison offerte, page panier et mini-panier ; l'état `reached` passe en vert |
| `cart-lines`, `cart-line*`, `qty-stepper` | `theme/page-cart.css` | tableau du panier ≥ 768 px, cartes empilées en dessous, sélecteur de quantité |
| `cart-summary-panel`, `cart-total-row`, `cart-summary-block` | `theme/page-cart.css` | récapitulatif, totaux et blocs repliables (code promo, estimation) |
| `cart-drawer*` | `theme/page-cart.css` | mini-panier en tiroir ; `.cart-drawer[open]` force la colonne et anime le glissement |

Les composants du catalogue (carte produit, pagination, sélecteurs de quantité et de taille, états vides) sont écrits en classes utilitaires directement dans leurs templates ; leur référence visuelle est sur `/styleguide`, sections « Lot 2 · catalogue ».

Règles de base (`base/brand.css`) : liens sans classe soulignés nude, `font-display` jamais italique, anneau de focus ivoire sur fond `bg-brand`, chiffres tabulaires sur `.price`.

### Gabarit partagé de titre de section — lot 3, étape 2

Template : `MadameAiguille_Theme/templates/section/heading.phtml` dans le thème enfant. Il est utilisé par `Magento_Catalog/templates/category/header.phtml`, `Magento_Catalog/templates/product/slider/product-slider.phtml` et les trois exemples réels du styleguide (`/styleguide#sg-section-heading`). Les prochains blocs d'accueil réutiliseront ce même rendu.

| Quoi | Où | Comment |
|---|---|---|
| Titre et description de catégorie | Admin › *Catalogue › Catégories › Contenu* | Le nom reste le H1 Sentient 26 / 32 px. La description HTML CMS conserve son rendu natif sous le titre |
| Titre du slider produit | Layout / données du bloc slider (`title`, `heading_tag`, `heading_css_classes`) | Les paramètres existants sont conservés ; ajout des options `subtitle`, `link_label`, `link_url` |
| Ornements et disposition communs | `MadameAiguille_Theme/templates/section/heading.phtml` | Filets décoratifs de 48 px sous 640 px, 96 px au-delà ; transparents sur papier, ivoire et blush |
| Typographie de section | même template, valeur par défaut de `heading_class` | Britney 30 px, 40 px dès 768 px ; accroche Sentient italique 18 px, texte et lien `brand-dark` compatibles avec le fond blush |
| Lien facultatif | données du bloc appelant | Sous l'accroche, cible d'au moins 44 px de haut, focus natif du thème conservé ; masqué si URL ou libellé absent |

**API de présentation** : le bloc appelant fournit un tableau `section_heading` (`title`, `tag`, `id`, `heading_class`, `subtitle`, `link_label`, `link_url`), puis appelle `fetchView($block->getTemplateFile('MadameAiguille_Theme::section/heading.phtml'))`, comme pour les colonnes du footer. Il est aussi possible de déclarer directement ce template avec le tableau en arguments de layout. Le niveau de titre est limité à `h1`…`h6`, avec `h2` par défaut. Tous les textes et attributs sont échappés ; `title`, `subtitle` et `link_label` attendent du texte brut traduit par l'appelant. Aucun HTML CMS brut ne transite par ce gabarit ; il reste rendu par le bloc CMS ou le renderer natif de catégorie. Le composant ne porte aucune règle catalogue/stock.

Le scanner Tailwind couvre déjà tous les `.phtml` du thème : ces classes sont compilées automatiquement. Après changement : `npm run build`, puis `bin/magento cache:clean full_page block_html`. En développement, si le navigateur conserve l'ancienne feuille CSS malgré le rebuild, effectuer un rechargement sans cache (Ctrl+Maj+R).

**Recette technique** : syntaxe PHP des quatre templates et build Tailwind validés ; captures Chrome headless à 390 et 1440 px sur le styleguide, la catégorie Trousses de toilette et la fiche Sac Aurora Verveine. Contrôle des titres longs, du fond blush et des ornements mobiles. Recette Pierre de cette étape en attente.

## 5. Header

Template : `Magento_Theme/templates/html/header.phtml`. Layout : `Magento_Theme/layout/default.xml` (bloc `header-content`).

| Quoi | Où | Comment |
|---|---|---|
| **Bandeau d'annonce** | Admin › *Contenu › Blocs › `header_announcement`* | Modifier le texte ; vider ou désactiver le bloc pour masquer le bandeau. Vider le cache `full_page` ensuite (`bin/magento cache:clean full_page`) |
| **Logo** | `web/images/logo-rectangulaire.jpg`, copie fidèle de `docs/logos/logo-rectangulaire.jpg` ; ou admin › *Contenu › Design › Configuration › Header › Logo* (prioritaire s'il est renseigné) | Tailles : 240 px mobile · 288 px md · 356 px lg (classes `w-60 md:w-72 lg:w-89` dans `header/logo.phtml`). Ratio intrinsèque 1864 × 345 et dimensions HTML 356 × 66. Texte alternatif : config `design/header/logo_alt` |
| **Favicon** | `Magento_Theme/web/favicon.ico`, généré depuis `docs/logos/logo-carre.jpg` ; source conservée sous `Magento_Theme/web/images/logo-carre.jpg` | contient les tailles 16, 32, 48, 64, 128 et 256 px. Un favicon chargé dans *Contenu › Design › Configuration › HTML Head › Favicon Icon* est prioritaire sur celui du thème |
| **Navigation** | Admin › *Catalogue › Catégories* | Catégories actives et « Inclure dans le menu » sous *Default Category*, 2 niveaux affichés (`getNavigation(3)` — niveau absolu). Aucun lien en dur : le logo est le lien vers l'accueil, À propos / Contact sont dans le footer |
| Compare | `default.xml` → `show_compare` | désactivé |
| Recherche | `header/search-form.phtml` du parent (desktop) ; formulaire simple dans `header/menu/mobile.phtml` (mobile) | raccourci ⌘/Ctrl+K |
| Menu compte | `Magento_Customer::header/customer-menu.phtml` du parent | liens ajoutables via `Magento_Customer/layout/default.xml` (`SortableItems`) |
| Mini-panier | `Magento_Theme::html/cart/cart-drawer.phtml` du parent — à restyler au lot 4 | affiché si `checkout/sidebar/display` = oui |

Points de rupture : burger < 768 px, navigation horizontale ≥ 768 px sur une seconde ligne sous le logo.

## 6. Footer

Template : `Magento_Theme/templates/html/footer.phtml` ; colonnes déclarées dans `Magento_Theme/layout/default.xml` (bloc `footer-content`).

| Quoi | Où | Comment |
|---|---|---|
| Accroche sous le logo | `default.xml` → bloc `footer.brand`, argument `tagline` | texte traduisible |
| **Réseaux sociaux** | Admin › *Stores › Configuration › Général › Madame Aiguille › Réseaux sociaux* | Instagram, Facebook, TikTok (Pinterest retiré au lot 5) — vide = masqué. Ajouter un réseau : `ViewModel/SocialLinks.php` (constante `NETWORKS`) + `system.xml` + icône Lucide (ou SVG dans `Hyva_Theme/web/svg/lucide/`, comme `tiktok.svg`) |
| Colonne **Boutique** | automatique : catégories de niveau 1 | `footer/shop.phtml` |
| Colonne **Informations** | `default.xml` → bloc `footer.info`, argument `links` (`label` + `path`) | les `path` sont des URL relatives : identifiant de page CMS, `contact`, etc. |
| Colonne **Paiement & livraison** | `default.xml` → bloc `footer.services`, arguments `payment_methods` / `shipping_methods` | Carte bancaire, Sur place au retrait · Mondial Relay, Retrait à l'atelier (alignés au lot 5) |
| **Copyright** | Admin › *Contenu › Design › Configuration › Footer › Copyright* (`design/footer/copyright`) | porte la mention « TVA non applicable, art. 293 B du CGI. » depuis le lot 5 |
| Gabarit de colonne (titre + liste, accordéon mobile) | `footer/column.phtml` | réutilisable via `fetchView` |

## 7. Pages et blocs CMS

Créés par data patch (`Setup/Patch/Data/`), **une seule fois** ; ensuite ils vivent en base et s'éditent dans l'admin (*Contenu › Pages / Blocs*). Rejouer `setup:upgrade` ne les écrase jamais.

| Identifiant | Type | Rôle |
|---|---|---|
| `a-propos` | page | L'histoire de Madame Aiguille |
| `livraison-retours` | page | Livraison et retours — **contenu générique en place** (voir ci-dessous) |
| `cgv` | page | Conditions générales de vente — **contenu générique en place** |
| `mentions-legales` | page | Mentions légales — **contenu générique en place** |
| `confidentialite` | page | Politique de confidentialité — **contenu générique en place** |
| `nos-tissus` | page | galerie de motifs référencés pour préparer une demande par contact |
| `header_announcement` | bloc | bandeau du header |
| `product_reassurance` | bloc | quatre arguments sous le bouton d'achat de la fiche produit : expédition sous 4 à 5 jours ouvrés, paiement, Mondial Relay offert dès 60 €, emballage cadeau (alignés au lot 5) |

### Pages juridiques — contenus génériques

`Setup/Patch/Data/FillLegalPages` remplit les quatre pages juridiques (CGV, mentions légales, livraison et retours, confidentialité) à partir des décisions du call Céline : identité de l'entreprise, franchise de TVA, zone France / Belgique / Luxembourg, point relais Mondial Relay, franco à 60 €, expédition sous 4-5 jours, retrait sur rendez-vous payé sur place, rétractation de 14 jours.

**Ce sont des brouillons, pas des documents validés.** Chaque page s'ouvre sur un bandeau `.cms-draft` qui le dit, et les valeurs encore inconnues sont laissées **entre crochets** : adresse e-mail de contact, coordonnées de l'hébergeur, médiateur de la consommation, prestataire d'envoi d'e-mails, date de mise en ligne. Un `grep` sur `[` dans ces pages liste ce qui reste à combler.

Le patch suit le même principe que celui du lot 3 pour *À propos* : **il ne remplace que le placeholder exact** posé par `CreateStaticPages`. Dès que Céline a touché une page, le patch ne la regarde plus — y compris si on le rejoue. Pour reproposer un contenu générique après coup, il faut passer par l'admin, pas par un nouveau patch.

Côté styles, `theme/page-cms.css` couvre désormais les listes (`ul`, `ol`), les listes de définitions (`dl`/`dt`/`dd`, en deux colonnes au-delà de 640 px) et le bandeau `.cms-draft` — ces balises sont partout dans un texte juridique et n'étaient pas stylées avant.

### Accueil — lot 3, étape 3

L'accueil suit cet ordre : hero, Nouveautés, Incontournables, nouvelles de l'atelier, histoire, arguments, galerie Instagram, newsletter. Les blocs créés par `CreateHomeBlocks` ne sont jamais réécrits après leur première installation : Céline conserve donc toutes ses modifications.

| Zone | Modification dans l'admin | Structure dans le code |
|---|---|---|
| Hero | *Contenu › Blocs* › `home_hero` : titre, texte, image et lien de catégorie | `Magento_Theme/layout/cms_index_index.xml`, styles `.home-hero` : centré et plafonné à 1440 px (`--breakpoint-2xl`), image plafonnée à 240 px sur mobile |
| Nouveautés | Dates *Définir le produit comme nouveau à partir de / jusqu'au* sur la fiche ; titre et accroche dans `home_new` | widget Magento `NewWidget`, règles de dates natives ; produits épuisés exclus par `ViewModel/Home/Products.php` |
| Incontournables | Fiche produit, groupe *Accueil* › *Incontournable sur l'accueil* ; titre et accroche dans `home_featured` | attribut EAV `home_featured`, quatre produits disponibles au maximum, ordre de création décroissant |
| Marchés, congés, annonces | bloc `home_actualities` | bloc CMS indépendant ; le texte initial est volontairement générique et ne contient ni date ni promesse non validée |
| Histoire | bloc `home_story` | visuel initial `web/images/home/atelier.png` et lien vers `a-propos` |
| Arguments | titre du bloc `home_why`, puis ses quatre éléments | grille 2 colonnes mobile / 4 desktop |
| Instagram | bloc `home_instagram` pour les quatre visuels ; URL dans *Stores › Configuration › Général › Madame Aiguille › Réseaux sociaux* | images initiales `web/images/home/ig-1.png` à `ig-4.png` ; bouton absent si l'URL Instagram est vide |
| Newsletter | bloc `home_newsletter` pour l'accroche | formulaire natif Magento retemplété dans `Magento_Newsletter/templates/subscribe.phtml`, reCAPTCHA natif conservé, confirmation par email activée dans `etc/config.xml` |

Le titre affiché des sections structurées est le **titre du bloc CMS**. Désactiver un bloc le masque. Une sélection Nouveautés ou Incontournables sans produit disponible est masquée avec son titre. Les images sont éditables dans le contenu CMS avec le sélecteur de médias ; les fichiers du thème servent de valeurs initiales.

Les cartes utilisent le renderer partagé `product_list_item`. Le tag `madameaiguille_home_products` invalide l'accueil après une sauvegarde produit, une variation de stock MSI ou le cron des dates de nouveauté, y compris lorsque la sélection était auparavant vide.

Pour les données de recette uniquement, `php docs/jeux-de-donnees/accueil.php` coche quatre SKU existants comme Incontournables. Ce script ne crée et ne supprime aucun produit.

Classes Tailwind utilisables dans le CMS : celles listées dans `@source inline(...)` de `tailwind-source.css` (couleurs `bg-/text-/border-brand*`, échelle `text-*`, espacements courants, grilles, `btn`, `badge`, `scallop`, `card`, `prose`, et celles du bloc de rassurance). Pour en ajouter une : la déclarer là, puis `npm run build`.

### Gabarit des pages CMS — lot 3, étape 4

Toutes les pages CMS hors accueil utilisent le gabarit `Magento_Cms/layout/cms_page_view.xml` : titre H1 orné, contenu centré, largeur de lecture d'environ 65 caractères et footer repoussé en bas de l'écran. Le contenu reste celui du moteur CMS natif, avec son filtrage des directives `{{view}}` et `{{store}}` et ses tags de cache.

| Quoi | Modification dans l'admin | Structure dans le code |
|---|---|---|
| Titre visible et balise title | *Contenu › Pages* › page concernée › *Content Heading* et *Page Title* | `ViewModel/Cms/Page.php`, `templates/cms/page.phtml`, titre partagé `section/heading.phtml` |
| Texte, listes, tableaux et liens | éditeur de la page | `.cms-content` dans `web/tailwind/theme/page-cms.css` ; prose limitée à 65 caractères |
| Image large | ajouter un élément avec la classe `cms-media` | image à 260 px mobile / 420 px desktop, rognage `cover` |
| Introduction | paragraphe de classe `cms-lead` | texte 18 px italique Sentient |
| Groupe de boutons | conteneur `cms-actions`, liens `btn btn-primary` ou `btn btn-secondary` | empilé sur mobile, horizontal à partir de `sm` |
| Encart | conteneur `cms-callout` | fond blush et texte `brand-dark` |
| Galerie de tissus | page `nos-tissus`, cartes `fabric-card` dans `fabric-grid` | deux colonnes à toutes les largeurs, images carrées et référence sous le visuel |

Le patch `CreateFabricPageAndPrepareAbout` crée `nos-tissus` une seule fois. Il remplace le placeholder d'À propos uniquement si son contenu correspond encore exactement au texte initial « À rédiger » ; toute modification faite par Céline est préservée. Le texte livré reste générique et signale que l'histoire définitive attend validation. Les contenus Livraison, CGV, Mentions légales et Confidentialité restent « À rédiger » jusqu'à réception des informations réelles.

Pour actualiser la galerie : *Contenu › Pages › Nos tissus*, remplacer chaque image, son texte alternatif, la référence `T01`… et la disponibilité. Le lien *Nos tissus* est ajouté à la colonne Informations du footer. Les liens vers `/contact` sont actifs et peuvent transmettre le SKU d'un produit avec `?product=<sku>`.

## 8. Catalogue — modèle de données

Tout est créé par data patch du module (`Setup/Patch/Data/`) : rejouer `setup:upgrade` ne crée jamais de doublon.

### Attribute set « Création »

**Toute création se saisit avec l'attribute set « Création »** (Admin › *Catalogue › Produits › Ajouter un produit › choisir « Création »*). Il reprend le set Default et ajoute deux groupes :

| Groupe | Attribut (code) | Type | Rôle à l'affichage |
|---|---|---|---|
| Série limitée | Série limitée (`serie_limitee`) | oui / non | Affiche « Série limitée » sur la vignette et la fiche |
| Série limitée | Nombre de pièces de la série (`taille_serie`) | entier | Complète en « Série limitée — 8 pièces » ; ligne « Série : 8 exemplaires » dans Caractéristiques |
| Série limitée | Taille (`taille`) | liste Petit / Grand, swatch texte | Seul axe de variante ; ne sert qu'aux modèles déclinés en deux tailles (produit configurable) |
| Caractéristiques | Composition, Dimensions, Conseils d'entretien | textes libres | Tableau « Caractéristiques » de la fiche. Une ligne vide n'est pas affichée |
| Product Details | Poids (`weight`) | **obligatoire**, en **kilogrammes** | Frais de port au poids (lot 5) ; affiché en grammes dans Caractéristiques (« 140 g ») |
| Product Details | Définir le produit comme nouveau à partir de / jusqu'au (`news_from_date` / `news_to_date`) | dates | Badge « Nouveauté » pendant la période ; le bloc Nouveautés de l'accueil (lot 3) s'en servira aussi |

Règles d'usage pour Céline :

- **Photos** : ratio **4:5** (portrait), 2 000 px de haut si possible, fond constant ; la première photo est l'image principale, trois vues minimum (ensemble, détail, situation). Les tailles d'affichage sont fixées dans `etc/view.xml` du thème (vignette 480 × 600, fiche 880 × 1100, miniatures 96 × 120) : une photo hors ratio est **rognée**, pas déformée.
- **Modèle à deux tailles** : créer un produit **configurable** sur l'attribut Taille (assistant « Créer des configurations ») ; chaque taille est un produit simple enfant avec son propre stock et son propre poids. La fiche affiche « À partir de » tant qu'aucune taille n'est choisie, et « 140 g (Petit) · 190 g (Grand) » dans Caractéristiques.
- **Stock** : la quantité saisie est le nombre de pièces restantes. À 0, la création passe « Épuisé » automatiquement (elle reste visible, voir ci-dessous). Ne pas cocher « Gérer le stock : non » : les badges et le plafond de quantité en dépendent.
- **Description courte** : une phrase (accroche en italique sous le nom de la fiche, ligne secondaire de la vignette). **Description** : le texte long, mis en forme.

### Règles d'affichage (ViewModel `LimitedSeries`)

Un seul badge par produit, par priorité : **Épuisé** > **Plus que N exemplaires** > **Nouveauté** > **Série limitée — N pièces**. Ces règles vivent dans `ViewModel/Product/LimitedSeries.php`, jamais dans un template.

| Quoi | Où | Comment |
|---|---|---|
| **Seuil de rareté** (« Plus que N exemplaires ») | Admin › *Stores › Configuration › Général › Madame Aiguille › Catalogue › Seuil de rareté* | Défaut 3 ; 0 désactive le badge. Indépendant de « Série limitée » : c'est un fait de stock |
| **Mention sous le prix** (TVA, frais de port…) | même écran, *Mention sous le prix* | Vide = rien n'est affiché. À renseigner quand le statut fiscal sera connu (lot 5) |
| Produits épuisés **visibles** (décision du 05/09/2026) | valeur par défaut du module (`config.xml` : `cataloginventory/options/show_out_of_stock = 1`) | Regroupés en fin de liste sous « Séries terminées », fiche consultable et indexable |
| Poids en kilogrammes | valeur par défaut du module (`general/locale/weight_unit = kgs`) | |
| Libellés des badges, phrase « Sur les N de la série », « Série terminée » | `LimitedSeries.php` (`__()`), traduisibles dans `i18n/fr_FR.csv` | |

### Fraîcheur des badges (caches)

Magento ne vide ses caches (HTML des vignettes, cache pleine page) que quand un produit change de **statut** de stock. Le module ajoute la purge des tags produit (enfants et parents configurables) :

- après toute réservation MSI — commande, annulation, remboursement, expédition (`Plugin/Inventory/FlushCacheAfterReservations`) ;
- après tout enregistrement de stock — saisie admin, import, déduction (`Plugin/Inventory/FlushCacheAfterSourceItemsSave`) ;
- chaque nuit à 00:05 pour les produits dont la période « nouveau » commence ou finit (`Cron/RefreshNoveltyBadges`, cron `madameaiguille_refresh_novelty_badges`). Le cron Magento doit donc tourner (à vérifier au lot 8).

Conséquence assumée : la page d'une catégorie est régénérée à chaque commande contenant un de ses produits.

## 9. Page catégorie

Templates dans `Magento_Catalog/templates/`, layout `Magento_Catalog/layout/catalog_category_view.xml`. Gabarit une colonne, **sans filtres à facettes** (v1) : les handles `catalog_category_view_type_layered*` sont surchargés dans `Magento_LayeredNavigation/layout/` et le bloc `catalog.leftnav` est retiré.

| Quoi | Où | Comment |
|---|---|---|
| **Titre et description** de la catégorie | Admin › *Catalogue › Catégories* › Contenu | Le titre est centré entre deux filets ornés ; la description (facultative) s'affiche dessous, largeur de lecture limitée. Template `category/header.phtml` |
| **Ordre des catégories** dans le menu et le footer | Admin › *Catalogue › Catégories* (glisser-déposer) | Deux niveaux affichés |
| **Tri par défaut** | Module : `catalog/frontend/default_sort_by = created_at` (Nouveautés) ; direction décroissante forcée dans le layout ; surchargeable par catégorie (*Paramètres d'affichage › Tri par défaut*) | Options proposées : Nouveautés, Prix croissant, Prix décroissant (+ Pertinence sur la recherche). Liste dans `ViewModel/Catalog/Sorting.php` ; `created_at` rendu triable par le patch `MakeCreatedAtSortable` |
| **Produits par page** | Module : `catalog/frontend/grid_per_page = 24` (une seule valeur, pas de limiteur) | Les épuisés sont regroupés **par page** ; 24 évite la pagination sur un catalogue de quelques dizaines de références |
| Carte produit | `product/list/item.phtml` | image 4:5, badge (abrégé sous 768 px), nom, ligne secondaire 13 px (mention de série, sinon description courte, sinon « Deux tailles disponibles »), prix, bouton « Voir le produit » ≥ md ; carte entière cliquable ; épuisée grisée avec bouton inactif « Série terminée ». Pas d'ajout au panier, de comparateur ni de liste d'envies en liste |
| Intertitre « Séries terminées » + encart « Une série terminée vous plaît ? » | `product/list.phtml` | l'encart mène à `/contact` (page du lot 3) |
| Compteur « 4 créations — dont 1 série terminée » et sélecteur de tri | `product/list/toolbar.phtml`, `toolbar/amount.phtml`, `toolbar/sorter.phtml` | barre pleine largeur (`full-bleed`) |
| Pagination | `Magento_Theme/templates/html/pager.phtml` | cibles 44 px, `aria-current`, flèches désactivées jamais masquées |
| Fil d'Ariane | `Magento_Theme/templates/html/breadcrumbs.phtml` | « Accueil / Catégorie » ; page courante non cliquable |
| **Catégorie sans produit** | automatique | « La série est en cours de couture » + bouton vers la boutique (`product/list/empty.phtml`, mode `category`) |

Recherche (`/catalogsearch/result/?q=…`) : même gabarit et même liste (`Magento_CatalogSearch/templates/result.phtml`, layout `catalogsearch_result_index.xml`), en-tête « Résultats pour « terme » », tri par pertinence par défaut. Sans résultat : « Aucune création trouvée », rappel du terme, pastilles vers les catégories de premier niveau, bouton vers la boutique (mode `search`). Attention : OpenSearch est tolérant, « trousse bleue » renvoie les trousses.

« Voir toute la boutique » pointe vers l'accueil : il n'existe pas de page boutique globale (décision à prendre avec Céline, voir le plan).

## 10. Fiche produit

Layout `Magento_Catalog/layout/catalog_product_view.xml` ; templates dans `Magento_Catalog/templates/product/`. Retirés du natif : avis, liste d'envies, comparateur, prix dégressifs, statut de stock natif, boutons de paiement express, « récemment vus », montée en gamme.

| Quoi | Où | Comment |
|---|---|---|
| Nom, accroche, description, caractéristiques, photos, prix, stock, dates de nouveauté | Admin › *Catalogue › Produits* (set « Création », §8) | tout le contenu de la fiche vient du produit |
| **Bloc de rassurance** (expédition, paiement, transporteurs, emballage) | Admin › *Contenu › Blocs › `product_reassurance`* | quatre entrées : icône SVG, titre, texte. À aligner sur les modes réellement activés (lot 5) |
| **Mention sous le prix** | Admin › *Configuration › Madame Aiguille › Catalogue* | voir §8 |
| Encart de rareté / de série (« Plus que 2 exemplaires · Sur les 10 de la série », « 4 exemplaires disponibles sur les 8 ») | automatique (`LimitedSeries`) | textes dans `product/view/product-info.phtml` |
| **Sélecteur de taille** | automatique sur un configurable | swatch texte natif Hyvä restylé (`Magento_Swatches/templates/product/…`, `components/swatches.css`) : option épuisée grisée, barrée, annotée « Épuisé » ; le bouton d'achat reste inactif tant qu'aucune taille n'est choisie |
| **Quantité plafonnée au stock** | automatique | `product/view/quantity.phtml` : − / +, plafond = stock restant (celui de la taille choisie sur un configurable), « Stock maximum atteint » |
| Encart « Une question sur le tissu ou le motif ? » | textes dans `product-info.phtml` | lien vers `/contact?product=<sku>` : le module Contact valide le SKU et préremplit le produit concerné s'il est actif et visible |
| Fiche **épuisée** | automatique | photo grisée, badge, bouton inactif « Série terminée », encart « Ce modèle vous plaît ? » vers le contact, suggestions retitrées « Disponible en ce moment » |
| Galerie | `product/view/gallery.phtml` (copie du parent, JS intact) + `etc/view.xml` | 4:5, badge sur l'image, bouton « Agrandir » (lightbox natif), miniatures sous l'image à toutes les largeurs, pas de flèches sur la vue principale |
| Description / Caractéristiques | `product/view/details.phtml` + `ViewModel/Product/Characteristics.php` | deux colonnes ≥ md, accordéons `<details>` en dessous ; lignes Composition, Dimensions, Poids, Série, Entretien |
| « Vous aimerez aussi » | Admin › produit › *Produits liés* (onglet « Produits liés, ventes incitatives… », section **Produits liés**) | slider natif restylé (`product/slider/product-slider.phtml`), quatre produits maximum, cartes du listing |
| Barre d'achat collante (mobile) | `product-info.phtml` | apparaît quand la galerie sort de l'écran ; même formulaire |
| Fil d'Ariane | `product/view/breadcrumbs.phtml` | rendu côté client par Hyvä : la catégorie affichée dépend de la page d'où l'on vient |

Écarts assumés par rapport à la maquette : pas de sous-libellé « 18 × 12 cm » dans les boutons de taille (dimensions dans Caractéristiques) ; pas de champ « Être prévenue » ciblé sur la fiche épuisée (la newsletter activée au lot 3 est généraliste) ; WebP non généré (reporté, voir plan).

## 11. Recette du catalogue

- Toujours vérifier à **1440 et 390 px**.
- Un produit sans poids ne peut plus être enregistré (attribut requis) ; contrôle global à passer avant mise en production (plan de tests §4).
- Après une commande de test (lot 4+), la vignette et la fiche doivent afficher le nouveau stock **sans vider de cache** (§8, fraîcheur).
- Pour tester un état vide : désactiver temporairement des produits, ou créer puis supprimer une catégorie **dans l'admin**.

## 12. Jeux de données de test

`docs/jeux-de-donnees/` : `categories.php` (arborescence des maquettes, sans suppression) puis `produits-test.php` (treize produits couvrant tous les états, photos des maquettes copiées sous `pub/media/madameaiguille/photos-test/`). Détail et règles dans le README du dossier. Ces scripts ne sont jamais exécutés en production.

## 13. Page de contrôle `/styleguide`

Route `styleguide` (module, `etc/frontend/routes.xml`), contrôleur `Controller/Index/Index.php` — **404 en mode production**. Layout `MadameAiguille_Theme/layout/madameaiguille_styleguide_index_index.xml`, template `MadameAiguille_Theme/templates/styleguide.phtml`. Tenir la page à jour à chaque nouveau composant : c'est la référence visuelle de recette. Sections lot 2 : carte produit (cinq états), fil d'Ariane, pagination, sélecteur de quantité, sélecteur de taille, états vides. Section lot 4 : barre de franco dans ses trois états (calculée par le vrai ViewModel), ligne de panier normale / rare / devenue incommandable, récapitulatif et pied de mini-panier. Section lot 7 : tableau des six statuts avec leur état Magento, une carte de commande par statut, les deux frises de suivi (livraison et retrait) et la navigation du compte — tout y est produit par `ViewModel\Styleguide\OrderStates`, qui fait passer des commandes d'exemple **non enregistrées** par le vrai `Order\Progress`. Les photos des cartes viennent de `pub/media/madameaiguille/photos-test/` (jeu de données, dev uniquement).

## 14. Checkout — Luma fallback (lot 6a)

Installé le 10/09/2026 sur `lot-6a-checkout`, depuis `lot-2-catalogue` (`6877562`) : **`hyva-themes/magento2-luma-checkout` 1.1.7**, dépendance **`hyva-themes/magento2-theme-fallback` 1.0.4**, licences OSL-3.0. Deux installations, aucune mise à jour ni suppression d'autre paquet. La contrainte Composer est `^1.1`, les versions exactes sont verrouillées dans `shop/composer.lock`. Modules activés dans `shop/app/etc/config.php` : `Hyva_LumaCheckout` et `Hyva_ThemeFallback`.

Commandes exécutées depuis `shop/` :

```bash
composer require hyva-themes/magento2-luma-checkout
bin/magento setup:upgrade --keep-generated
bin/magento cache:flush
```

L'accès au Packagist Hyvä utilise la configuration Composer globale non versionnée. Aucun identifiant n'est recopié dans le projet. Aucun moyen de paiement n'a été installé ou configuré.

### Fonctionnement et configuration

**Correction du plan initial : le fallback change le thème de la page entière**, via `Hyva\ThemeFallback\Model\ThemeSwitch::switchToFallback()`. Le checkout utilise donc le layout Luma simplifié (logo Luma, lien de connexion, copyright), **pas le header/footer du thème enfant Hyvä**. Le module Luma Checkout fournit les réglages ; le module Theme Fallback réalise la bascule. Aucun habillage n'est livré au lot 6a.

| Quoi | Où | Valeur / fonctionnement |
|---|---|---|
| Activer le fallback | Admin › *Stores › Configuration › Hyva Themes › Theme Fallback › General Settings › Enable* | `hyva_theme_fallback/general/enable = 1` |
| Thème du checkout | même écran, *Theme full path* | `hyva_theme_fallback/general/theme_full_path = frontend/MadameAiguille/checkout` depuis le lot 6b |
| Routes concernées | même écran, *Apply fallback to requests containing* | `hyva_theme_fallback/general/list_part_of_url` : conserver les valeurs système décrites ci-dessous |
| Commande invité | Admin › *Stores › Configuration › Sales › Checkout › Checkout Options › Allow Guest Checkout* | `checkout/options/guest_checkout = 1`, valeur effective vérifiée pour la vue `default` |
| Valeurs structurelles | `vendor/hyva-themes/magento2-luma-checkout/src/etc/config.xml` (lecture seule) | Les valeurs natives suffisent ; aucune surcharge en base ajoutée. Une future valeur propre au projet ira dans `MadameAiguille_Theme/etc/config.xml` |

Les routes système de la version 1.1.7 sont `/checkout/index`, `paypal/express/review`, `paypal/express/saveShippingMethod`, `paypal/transparent/redirect`, `paypal/transparent/response` et `customer/ajax/login`. Les routes PayPal sont fournies par le paquet : leur présence **n'active pas un paiement**. Le matching porte notamment sur la route Magento résolue : `/checkout/` correspond à `checkout/index/index`. Ne pas élargir la règle à tout `checkout`, sinon le panier et les pages de résultat basculeraient aussi vers Luma. Après modification : `cache:flush`.

### Surcharges du lot 6b

Le lot 6b a créé `shop/app/design/frontend/MadameAiguille/checkout/`, **second thème enfant de `Magento/luma`**, et versionné son chemin dans `MadameAiguille_Theme/etc/config.xml`. Les fichiers du thème Hyvä `MadameAiguille/default` ne sont pas hérités par Luma. Les pages de succès et d'échec restent Hyvä ; elles sont les seules pages de commande habillées dans `default`. Détail des fichiers effectivement livrés : §27.

| Besoin | Emplacement dans le thème Luma enfant |
|---|---|
| Déclaration | `theme.xml` avec parent `Magento/luma`, `registration.php` |
| Structure, arguments `jsLayout`, logo du tunnel | `Magento_Checkout/layout/checkout_index_index.xml` ; fusion ciblée avec le layout natif |
| Templates Knockout | `Magento_Checkout/web/template/` en conservant le chemin relatif du template natif : `shipping.html`, `payment.html`, `summary/...` |
| Champs UI partagés, uniquement si nécessaire | `Magento_Ui/web/templates/...` (attention au pluriel `templates`) |
| Variables et styles LESS | `web/css/source/_theme.less`, `web/css/source/_extend.less` ; extension ciblée possible dans `Magento_Checkout/web/css/source/_extend.less` |
| Fontes et traductions | `web/fonts/` avec les WOFF2 fournis tels quels et licences ; `i18n/fr_FR.csv` propre au thème Luma |

Références en lecture seule : `vendor/magento/module-checkout/view/frontend/web/template/`, `vendor/magento/theme-frontend-luma/Magento_Checkout/web/css/source/`, et les README de `vendor/hyva-themes/magento2-{luma-checkout,theme-fallback}/`. Les templates UI génériques viennent de `vendor/magento/module-ui/view/base/web/templates/`. Luma compile du **LESS**, pas le Tailwind du thème Hyvä. Les styles générés vivent dans `pub/static/frontend/<Vendor>/<theme>/<locale>/css/` et ne se modifient pas directement. En production, leur génération passe par `setup:static-content:deploy`.

### Composants ajoutés au lot 5

Sous la liste des modes de livraison (région native `shippingAdditional`) : carte Mondial Relay, rendez-vous de retrait, case « Emballage cadeau » ; dans le récapitulatif, la ligne d'emballage. Livrés fonctionnellement par `MadameAiguille_Checkout` (§26), puis habillés au 6b par les surcharges `MadameAiguille_Checkout/web/template/{relay-point,pickup-slot,gift-wrap}.html` et `summary/gift-wrap.html` du thème checkout (§27).

### Recette technique du 10/09/2026

- Ajout depuis `/sac-aurora-verveine.html` : une unité de `MA-SAC-VER`, puis accès invité à `/checkout/#shipping` ; formulaire d'adresse et tarif natif Flat Rate existant affichés. Aucune commande créée.
- Contrôle visuel du checkout à **1440 et 390 px**, via le navigateur Chromium intégré à Codex (utilisé à la place de la commande Chrome headless du brief). Le branding Luma et les libellés partiellement anglais sont attendus avant le lot 6b. Le pays par défaut États-Unis et le tarif existant sont à reprendre au lot 5.
- Comparaison des scripts du DOM : accueil `/`, catégorie `/petits-sacs.html` et panier `/checkout/cart/` chargent Alpine du thème Madame Aiguille, **aucun script RequireJS/Knockout** ; le checkout charge `requirejs/require.js` et `knockoutjs/knockout.js` sous `frontend/Magento/luma/fr_FR/`. Aucune erreur JavaScript relevée au chargement du checkout.
- Régression du module : **10 tests, 28 assertions**. La commande habituelle signale l'absence de `allure/allure.config.php` ; relance avec `--no-extensions` réussie, sans changement du code de test.
- **Pierre a validé le lot 6a après avoir passé une commande**, puis autorisé le lot 3. Le moyen de paiement et les détails de cette commande n'ont pas été relevés par l'agent ; la recette des prestataires, de la connexion pendant le tunnel et des transporteurs reste dans les lots correspondants.

## 15. Commandes utiles

```bash
bin/magento cache:flush                      # après un nouveau layout, une route, une section system.xml
bin/magento cache:clean full_page block_html # après modification d'un template ou d'un bloc CMS
bin/magento setup:upgrade --keep-generated   # nouveau module, nouveau data patch
bin/magento setup:di:compile                 # OBLIGATOIRE après un nouveau plugin : --keep-generated fige la liste des plugins
bin/magento config:set <chemin> <valeur>     # ex. design/footer/copyright "…"
bin/magento indexer:reindex                  # après création de catégories/produits par script
bin/magento cron:run --group=default         # exécuter les crons (dont le badge Nouveauté) à la main
vendor/bin/phpunit --no-extensions -c dev/tests/unit/phpunit.xml.dist app/code/MadameAiguille   # tests des modules
bin/magento madameaiguille:shipping:import-rates [--dry-run]   # grille de frais de port au poids (§26)
bin/magento madameaiguille:catalog:check-weight                # produits activés sans poids : à passer avant chaque mise en production
bin/magento madameaiguille:env:check [--serveur] [--noindex]    # configuration effective de l'environnement (§29)
```

Sur le serveur, toute commande Magento tourne sous l'utilisateur du pool : `sudo -u madame-aiguille php /var/www/madame-aiguille/current/bin/magento …` (§29).

## 16. Conventions rappelées

- Escaping systématique (`$escaper->escapeHtml/Url/HtmlAttr/Js`), `__()` sur tous les textes, zéro logique métier dans les `.phtml` (→ ViewModel déclaré en layout XML).
- Aucune valeur hexadécimale ni classe arbitraire de couleur dans un template : uniquement des tokens.
- Alpine pour l'interface, jamais pour recalculer une donnée serveur.
- Un fichier de layout par handle. Surcharger un template entier seulement si le layout ne suffit pas.
- Toute surcharge visuelle frontend appartient au thème enfant : layouts, `.phtml`, Tailwind/CSS, assets et templates JavaScript. `app/code` porte la logique et les valeurs par défaut, jamais le rendu personnalisé livré à la boutique.
- Commits atomiques en français, un par étape cohérente.
- Toute règle liée au stock ou aux séries limitées passe par `ViewModel\Product\LimitedSeries` — jamais de `getQty()` ni de comparaison de seuil dans un template.
- Toute règle liée à un statut de commande passe par `ViewModel\Order\Progress` — jamais de `switch` ni de comparaison de code de statut dans un template.
- Pour tester un état vide ou une suppression : l'admin, pas un script.

## 17. Journal des livraisons

| Lot | Contenu | Commits |
|---|---|---|
| 1 — Fondations | dépôt, thème enfant, chaîne Tailwind, fontes, tokens, composants, styleguide, module, header, footer | `62dca83` → `7af01df` |
| 2 — Catalogue | attributs et set « Création », jeu de données, ViewModel LimitedSeries, view.xml 4:5, page catégorie, fraîcheur des badges, fiche produit, états vides, styleguide et documentation | `92500b5` → `ccabeab` + documentation |
| 6a — Checkout | installation Luma fallback 1.1.7 + Theme Fallback 1.0.4, contrôle invité et isolation des scripts, documentation des surcharges 6b ; validé par Pierre après une commande | `7f46bb1` |
| 3 — Accueil et CMS | titres partagés, accueil éditable, newsletter, pages CMS et Nos tissus | `bdc1169` → `0522185` |
| 3 — Contact et états vides | module Contact, pièce jointe privée, 404 interactive, panier vide, styleguide et recette transversale | `dfb6b45` → `6cbe5cc` |
| 3 — Identité visuelle | logos officiels header/footer, favicon carré multi-tailles, rendu Contact replacé dans le thème enfant | `9c48987` |
| 4 — Panier et mini-panier | ViewModels Cart (franco, stock, récapitulatif, options), plugin customer-data, page panier, récapitulatif et estimateur, mini-panier en tiroir, traductions des messages de stock, styleguide et documentation | `3316965` → `eb54441` |
| 7 — Statuts de commande | six statuts par data patch idempotent, `Model\Order\StatusConfig`, ViewModel `Order\Progress` (phrase d'avancement, badge, frise datée) et ses tests | `d889482` |
| 7 — Compte client | navigation restylée avec l'identité de la cliente, tableau de bord, commandes en cartes, détail avec frise, formulaires, retrait de l'assistance distante, dictionnaire `fr_FR.csv` | `b4c1f86` |
| 7 — Emails | enveloppe commune header/footer aux couleurs de la marque, styles LESS email, notifications « paiement reçu » et « prête pour retrait » (observateur + envoi configurable), accusé de réception du contact réaligné | `635efa5` |
| 7 — Styleguide et documentation | états du compte sur `/styleguide` via `ViewModel\Styleguide\OrderStates`, sections §21 et §22, mémo Céline complété | `8f33ac5` → `8f2c4c4` |
| 5 — Préparation (09/10/2026) | contenus génériques des quatre pages juridiques par data patch non destructif, styles CMS des listes, définitions et bandeau de brouillon | `5a23cb8` |
| 5 — Spike et socle (09/10/2026) | spike Mondial Relay ; zone FR/BE/LU/MC, origine Saint-Épain, TVA FR, chèque coupé, Mollie restreint, message cadeau | `8982ecb` → `718f8bc` |
| 5 — Livraison | grille au poids versionnée et commande d'import, franco à 60 € depuis le seul seuil du panier, contrôle des poids | `0171883` → `871c67d` |
| 5 — Tunnel | module `MadameAiguille_Checkout` : point relais Mondial Relay, retrait payé sur place, statuts câblés sur Mollie et l'expédition, rendez-vous de retrait avec réservation, emballage cadeau | `94d5aca` → `cede240` |
| 5 — Affichage et recette | rassurance, pied de page, bandeau, mention de TVA (PDF, emails, prix), TikTok, lignes longues | `acf530c`, `f1201b7`, `c236760` + documentation |
| 6b — Socle (09/10/2026) | thème Luma enfant, fontes et licences, logo, tokens LESS, fallback, captures avant / socle | `3416d43` |
| 6b — Tunnel et consentements | étapes et récapitulatif, composants du lot 5, clavier du relais, traductions, accord CGV natif, newsletter native facultative et tests | `2a1db4f` |
| 6b — Confirmations | succès retrait / relais, échec, inscription native après commande, vrais ViewModels dans le styleguide | `670dfdf` |
| 6b — Newsletter | respect du réglage d’inscription invitée, rattachement au compte des clientes connectées, tests ciblés | `342bc19` |
| 6b — Récapitulatif final | mention des montants estimatifs, espacement, dernières clés de traduction | `2f82598` |
| 6b — Correctif de recette | chargement Leaflet unique, récapitulatif natif du paiement relais rétabli, contrôle console et captures | `f80ceaf` |
| 6b — Recette et documentation | captures finales, administration, limites, mémo Céline et plan v2.8 | `92a96f6` |
| 6b — Passage de relais | prompt de reprise 8a écrit après fusion et publication, avec état vérifiable et pièges du 6b | `docs/prompts/prompt-lot8a.md` (commit de passage de relais) |
| Langue française (09/10/2026) | paquet `community-engineering/language-fr_fr`, clés Hyvä manquantes, lignes d'adresse du tunnel, sujets d'e-mails, titre de livraison de l'admin, §28 | `e6c8e3d` → documentation |
| 8a — Contrôle d'environnement (09/10/2026) | commande `madameaiguille:env:check`, lecture de la configuration effective, profils local / serveur / non indexé, tests | `276f162` |
| 8a — Provisionnement (10/10/2026) | scripts `deploy/serveur/` 10 à 60 : dépôts, MariaDB 12.3, PHP 8.5, OpenSearch 3, Varnish 7, Valkey à la place de Redis | `fd1e19b` |
| 8a — Installation neuve | patches d'identité de la boutique et des moyens Mollie provisoires, sites / boutiques / thèmes figés dans `config.php` | `74bcfb9` |
| 8a — Build et bascule | workflow GitHub *Déploiement*, fontes Fontshare vérifiées, `deploy/bascule.sh`, hébergement (70), installation (80), double authentification | `04fd144` |
| 8a — Mise en ligne | installation du 10/10/2026 sur `madame-aiguille.fr`, SMTP Brevo, clés Mollie, recette écrans, `docs/recettes/lot-8a.md` | `090e1ba` + documentation |
| 8a — Accès à l'administration | IP autorisées ou mot de passe HTTP après l'alerte Chrome « Site dangereux » | `dbb251f` |
| 8a — Clôture | workflow manuel seul, actions sur Node 24, documentation, plan v3.0, prompt du lot 8 | commit de clôture |
| Correctif accueil (09/10/2026) | hero centré et plafonné à 1440 px dans le thème ; contrôles à 1440, 390 et 2560 px, CSS servi identique au build ; recette `docs/recettes/correction-hero.md` | `8316318` |
| 7 — Correction (11/09/2026) | identifiants des deux gabarits d'email alignés sur le chemin de configuration : sans cela, la page *Emails de vente* de l'administration ne s'ouvrait plus du tout | `62d7647` |

## 18. Formulaire de contact

La page `/contact` est fournie par le module `MadameAiguille_Contact`. Sa logique reste dans `app/code/MadameAiguille/Contact` et sa surcharge visuelle dans `app/design/frontend/MadameAiguille/default/MadameAiguille_Contact`. Les encarts de la colonne de droite sont des blocs CMS modifiables depuis **Contenu > Éléments > Blocs** :

- `contact_help` pour les informations pratiques ;
- `contact_locations` pour les marchés, congés et annonces.

Le formulaire accepte une photo JPG ou PNG de 5 Mo maximum. Le type MIME est contrôlé sur le serveur et le fichier reçoit un nom aléatoire dans `var/madameaiguille/contact`, hors du répertoire public. Une tâche cron purge chaque jour les fichiers arrivés à échéance. La durée, fixée à 30 jours par défaut, se règle dans **Boutiques > Configuration > Général > Madame Aiguille > Formulaire de contact**.

Le lien depuis une fiche produit utilise `/contact?product=SKU`. Lorsque le produit est actif et visible, son nom, son image et sa référence sont présentés au-dessus du message.

Les emails reprennent l’expéditeur et le destinataire du module Contact natif de Magento. Un premier email avec la pièce jointe est adressé à la boutique, puis un accusé de réception est envoyé au visiteur. La protection reCAPTCHA se configure avec le mécanisme Magento/Hyvä habituel.

Les textes génériques des blocs CMS doivent être remplacés par les contenus validés par Céline avant la mise en production.

## 19. États vides : 404 et panier

| Écran | Où modifier le rendu | Comportement |
|---|---|---|
| 404 | `Magento_Cms/templates/default/no-route.phtml` et `theme/page-empty.css` | Conserve le statut HTTP 404, passe en une colonne et propose l’accueil ou le contact. L’interaction « Tirer doucement sur le fil » fonctionne au clic et au clavier ; les animations sont coupées avec `prefers-reduced-motion`. |
| Panier vide | `Magento_Checkout/templates/php-cart/noItems.phtml` et `Magento_Checkout/layout/checkout_cart_index.xml` | Retour vers l’accueil et deux nouveautés disponibles au maximum, obtenues avec la même collection et la même carte produit que l’accueil. Aucun produit épuisé n’est proposé. |
| Démonstrations | `/styleguide`, section « États vides » | Les aperçus appellent les vrais templates 404 et panier. Le formulaire Contact dispose aussi de son aperçu complet dans la section dédiée. |

La 404 n’utilise pas le contenu de la page CMS `no-route` installée par Magento : le handle `cms_noroute_index` retire explicitement son gabarit afin qu’un ancien texte anglais ou une mise en page à deux colonnes ne réapparaisse pas. Le titre et les textes de cet état relèvent donc du code du thème.

Les recommandations du panier sont automatiques. Pour modifier les produits proposés, renseigner les dates **Définir le produit comme nouveau à partir de / jusqu’au** dans la fiche produit ; les règles et la purge de cache sont identiques au bloc Nouveautés de l’accueil.

## 20. Panier et mini-panier

Le panier vide relève du lot 3 (§19). Cette section traite le panier contenant des articles, le mini-panier et l'estimation des frais de port.

### Où modifier quoi

| Quoi | Où | Comment |
|---|---|---|
| **Seuil de livraison offerte** | Admin › *Stores › Configuration › Général › Madame Aiguille › Panier › Seuil de livraison offerte* | Défaut **60 €** depuis le lot 5 (49 € auparavant). **0 masque la barre et supprime le franco.** C'est aussi ce seuil qui rend le point relais gratuit au checkout (§26) : barre et tarif ne peuvent plus diverger |
| **Champ code promo** | même écran, *Afficher le champ code promo* | Masqué par défaut : un champ ouvert donne à celles qui n'ont pas de code le sentiment de payer trop cher. À activer le jour où une règle de panier existe. Un coupon déjà appliqué reste toujours affiché et retirable, même réglage désactivé |
| **Rassurance sous le bouton de commande** | Admin › *Contenu › Blocs › `cart_reassurance`* | Deux lignes : paiement et délai d'expédition. Textes éditables, à aligner sur les prestataires réellement activés (lot 5) |
| **Mention sous le total** | Admin › *Configuration › Madame Aiguille › Catalogue › Mention sous le prix* | Même réglage que la fiche produit. Vide = rien n'est affiché ; aucune mention fiscale n'est écrite dans le code |
| Nombre d'articles affichés dans le tiroir | Admin › *Stores › Configuration › Sales › Checkout › Shopping Cart Sidebar* | Valeurs natives Magento conservées |
| Textes des messages de stock et des totaux | `i18n/fr_FR.csv` du thème | Les paquets de langue Magento 2.4.9 ne contiennent plus de traductions : sans ces entrées, la boutique affiche « Shopping Cart », « SousTotal » et « The requested qty is not available » |

### Templates et ViewModels

| Fichier | Rôle |
|---|---|
| `Magento_Checkout/layout/checkout_cart_index.xml` | Injection des ViewModels, bloc de rassurance, état vide du lot 3 |
| `Magento_Checkout/templates/php-cart/wrapper.phtml` | Deux colonnes ≥ 1024 px, titre « Mon panier — N articles », barre de franco |
| `…/php-cart/form.phtml` | Intitulés de colonnes, liste des lignes, actions, composant Alpine du sélecteur de quantité |
| `…/php-cart/item/default.phtml` | Ligne produit : image 4:5, variante, rareté, quantité plafonnée, sous-total |
| `…/php-cart/item/renderer/actions/{edit,remove}.phtml` | Modifier / Retirer, cibles de 44 px |
| `…/php-cart/totals.phtml` | Classes des totaux ; segments et ordre restent natifs |
| `…/php-cart/{methods,onepage-link}.phtml` | Bouton « Passer commande » et rassurance |
| `…/php-cart/shipping.phtml` | Estimation de livraison — script du parent conservé tel quel |
| `MadameAiguille_Theme/templates/cart/coupon.phtml` | Code promo, masqué selon la configuration |
| `Magento_Theme/templates/html/cart/cart-drawer.phtml` | Mini-panier en tiroir |
| `ViewModel/Cart/FreeShipping.php` | Seuil, montant restant, progression, libellés |
| `ViewModel/Cart/Stock.php` | Plafond de quantité d'une ligne, disponibilité, mentions de rareté |
| `ViewModel/Cart/Summary.php` | Nombre d'articles et sous-total pour l'en-tête |
| `ViewModel/Cart/Options.php` | Visibilité du code promo |
| `Plugin/Checkout/CustomerData/AddCartData.php` | Ajoute à la section privée `cart` le plafond de stock de chaque ligne et l'état du franco |
| `web/tailwind/theme/page-cart.css` | Tous les styles du panier et du tiroir |

**Quantité plafonnée au stock.** Le renderer natif borne la quantité sur `max_sale_qty` (10 000 par défaut) : une cliente pouvait saisir six exemplaires d'une série où il en restait trois et ne l'apprendre qu'en validant. `Cart\Stock` ramène le plafond au stock vendable, via `LimitedSeries` — seule autorité du projet sur le stock. Sur un configurable, c'est le stock de l'enfant réellement commandé, pas la somme des tailles. Alpine ne fait que borner l'interface : toute quantité reste validée par Magento.

**Franco de port.** Le montant restant est calculé côté serveur, à partir du quote sur la page panier et de la section privée dans le tiroir — jamais depuis un prix lu dans le DOM. La barre annonce un montant, jamais un pourcentage : « 12,00 € » est actionnable, « 75 % » ne l'est pas. Une fois le seuil franchi, elle passe en succès et se tait.

**Mini-panier.** Tiroir de 380 px sur desktop, plein écran sous 768 px. Au-delà de quatre articles la liste défile, le pied reste visible. Le compteur affiche le nombre d'exemplaires, comme le badge du header et le titre de la page panier. Deux écarts nécessaires avec le gabarit du parent, commentés dans le template : le glissement est animé en CSS et non par les attributs `x-transition` (sur un `<dialog>` piloté par `x-htmldialog`, ils retardent l'ouverture de plusieurs secondes et empêchent la fermeture) ; et l'état ouvert force l'affichage en colonne, `x-show` écrivant un `display` inline qui casserait la liste défilante et le pied fixe.

### Estimation des frais de port

Le calcul est **entièrement natif** : appels REST `estimate-shipping-methods` et `totals-information`, mémorisation de l'adresse dans customer-data. Seul le gabarit change.

**Seules les méthodes réellement retournées par Magento sont affichées**, groupées par transporteur. Depuis le lot 5 : Mondial Relay (grille au poids, 0 € au-delà du franco) et, en France, le retrait à l'atelier à 0 €. Aucun nom de transporteur ni aucun tarif n'est écrit en dur. Quand aucune méthode n'est retournée, un message le dit au lieu de laisser la zone vide.

### Recette du 10/09/2026

- Panier vide, une création simple, un configurable (variante et stock de l'enfant), plusieurs lignes, quantité maximale, suppression du dernier article, rafraîchissement de page.
- Mini-panier à 1440 et 390 px : ouverture, fermeture au bouton, à la touche Échap et au clic extérieur, retour du focus au bouton panier, liste défilante à six lignes, quantité jusqu'au plafond, suppression, compteur synchronisé avec la page panier.
- Estimateur avec et sans code postal : France 75011 renvoie la méthode active et met à jour le total ; destination non desservie affiche le message dédié.
- **Concurrence** : stock ramené à 1 sur une ligne qui en contenait 3 → bandeau « Une création de votre panier vient d'être épuisée », message sur la ligne, photo grisée, plafond atteint, et **retour au panier au lieu du tunnel de commande**. Stock restauré ensuite.
- **Produit désactivé pendant que le panier est ouvert** : Magento retire la ligne **sans aucun message**. Comportement natif, conservé faute de pouvoir l'améliorer sans ajouter de logique métier — à trancher avec Pierre (voir §23).
- Accès au checkout, et absence de RequireJS/Knockout sur le panier comme sur le tiroir.
- Tests unitaires du module : **24 tests, 64 assertions**.

## 21. Compte client et commandes (lot 7)

Les pages du compte restent en **Hyvä** — seul le checkout bascule sur le thème Luma du fallback. Tout ce que Magento fournit nativement est conservé : formulaires, validation, pagination, carnet d'adresses, sections privées. Le lot n'ajoute que des gabarits de rendu et des ViewModels.

### Où se trouve quoi

| Écran | Layout du thème | Gabarit |
|---|---|---|
| Toutes les pages du compte | `Magento_Customer/layout/customer_account.xml` | ajoute la classe `madameaiguille-account`, injecte le ViewModel d'identité, retire les entrées inutiles (liste d'envies, avis, produits téléchargeables, cartes bancaires) et remonte le lien de déconnexion |
| Navigation latérale | idem | `Magento_Customer/templates/account/navigation.phtml` |
| Tableau de bord | `Magento_Sales/layout/customer_account_index.xml` | `Magento_Sales/templates/order/recent.phtml` — le bloc des dernières commandes est remonté au-dessus des informations |
| Mes commandes | `Magento_Sales/layout/sales_order_history.xml` | `Magento_Sales/templates/order/history.phtml` |
| Détail d'une commande | `Magento_Sales/layout/sales_order_view.xml` | `Magento_Sales/templates/order/view.phtml` |
| Création de compte | — | `Magento_Customer/templates/newcustomer.phtml` |
| Réinitialisation du mot de passe | — | `Magento_Customer/templates/form/resetforgottenpassword.phtml` |
| Assistance distante | `Magento_LoginAsCustomerAssistance/layout/customer_account_{create,edit}.xml` | le bloc natif d'opt-in est retiré : il n'entre pas dans le parcours convenu. **Le retirer par un `.phtml` vide ne suffit pas** — son layout est chargé après celui du compte, il faut viser le nom du bloc |

Styles : `web/tailwind/theme/account-nav.css` (colonne de 280 px, accordéon sous 768 px, entrée courante en `<strong>`) et `web/tailwind/theme/page-customer.css` (cartes de commande, badges de statut, frise, états vides, formulaires).

### ViewModels

| ViewModel | Rôle |
|---|---|
| `ViewModel\Customer\AccountSummary` | Nom et adresse email de la cliente connectée, affichés en tête de la navigation |
| `ViewModel\Order\Progress` | Phrase d'avancement, variante de badge, étape courante et frise datée d'une commande |
| `ViewModel\Styleguide\OrderStates` | Uniquement pour `/styleguide` : construit des commandes d'exemple **non enregistrées** et les fait passer par `Order\Progress` |

**Aucun `switch` sur un code de statut dans un `.phtml`.** Le gabarit reçoit une phrase et un nom de variante ; toute la correspondance vit dans `Order\Progress`. Une carte de commande n'affiche donc jamais un statut sec : elle dit où en est la commande (« Je couds votre commande avec soin avant son expédition. »).

### Les six statuts

Créés par le data patch `Setup/Patch/Data/CreateOrderStatuses`, à partir de la table unique `Model/Order/StatusConfig`. Le patch écrit en `insertOnDuplicate` : rejouer `setup:upgrade` ne crée jamais de doublon, il rafraîchit le libellé.

| Libellé (cliente et back-office) | Code | État Magento |
|---|---|---|
| En attente de paiement | `madameaiguille_pending_payment` | `pending_payment` |
| Paiement reçu | `madameaiguille_payment_received` | `processing` |
| En préparation | `madameaiguille_preparing` | `processing` |
| Expédiée | `madameaiguille_shipped` | `complete` |
| Prête pour retrait | `madameaiguille_ready_for_pickup` | `processing` |
| Livrée | `madameaiguille_delivered` | `complete` |

La frise du détail de commande a cinq étapes. Elle suit le parcours livraison par défaut et remplace « Expédiée » par « Prête pour retrait » dès qu'un passage par ce statut figure dans l'historique. Les dates viennent de l'historique natif des statuts, jamais d'un champ ajouté. Les statuts natifs des commandes antérieures (`pending`, `processing`, `complete`) sont ramenés au statut équivalent pour que leur suivi reste cohérent.

### Textes français

`i18n/fr_FR.csv` du thème, 220 lignes. Les paquets de langue Magento 2.4.9 sont **vides** : sans ce dictionnaire, le compte affiche « Sign In », « Order # » ou « My Account ». Les clés sont les chaînes réellement émises par Magento — les recopier exactement, ponctuation comprise, en les relevant sur la page plutôt qu'en les devinant.

### Recette

À rejouer à **1440 et 390 px** : création de compte, connexion, déconnexion, mot de passe oublié et réinitialisation, tableau de bord, liste de commandes vide puis remplie, détail d'une commande, ajout et modification d'adresse, changement de mot de passe. Vérifier qu'aucune page du compte ne charge RequireJS ni Knockout. Les états visuels sans donnée réelle sont consultables sur `/styleguide`, section « Compte client et commandes ».

## 22. Emails transactionnels (lot 7)

**Décision** : on garde les gabarits transactionnels **natifs de Magento**, habillés par une enveloppe commune. Céline continue donc de les éditer dans *Marketing › Communications › Modèles d'e-mail*, et une montée de version de Magento ne réécrit pas des gabarits maison.

### L'enveloppe commune

`Magento_Email/email/header.html` et `Magento_Email/email/footer.html` dans le thème enfant : logo officiel, salutation de clôture, mention « Fait main en France en très petites séries », liens Contact et Mon compte. Tous les emails du site en héritent, y compris ceux que Magento envoie sans qu'on les ait touchés.

Les styles email sont en **LESS** (Magento ne compile pas Tailwind pour l'email) :

| Fichier | Rôle |
|---|---|
| `web/css/source/_email-variables.less` | Palette de marque en hexadécimal, synchronisée à la main avec `hyva.config.json` — les clients email ne comprennent pas les variables CSS |
| `web/css/source/_email-extend.less` | Habillage : en-tête, cartouche de commande, bouton, note, pied |
| `web/css/source/_typography.less` | `@font-face` des fontes de marque, avec repli Georgia / serif |
| `web/css/email.less`, `email-inline.less`, `email-fonts.less` | Points d'entrée compilés par Magento |

### Les deux notifications métier

Elles n'existent pas dans Magento : « paiement reçu » et « prête pour retrait » sont propres au parcours de l'atelier.

- Gabarits : `MadameAiguille_Theme/email/payment_received.html` et `ready_for_pickup.html`, déclarés dans `etc/email_templates.xml` sous les identifiants `sales_email_madameaiguille_payment_received_template` et `sales_email_madameaiguille_ready_for_pickup_template`.

  > **L'identifiant d'un gabarit n'est pas libre.** Dès qu'un champ de configuration utilise le modèle source `Config\Source\Email\Template`, Magento reconstruit l'identifiant à partir du **chemin du champ**, slashs remplacés par des underscores : `sales_email/madameaiguille_payment_received/template` → `sales_email_madameaiguille_payment_received_template`. Si `email_templates.xml` déclare un autre nom, la page *Emails de vente* ne s'ouvre plus du tout — `UnexpectedTemplateIdValueException: Email template is not defined`, et pas seulement sur notre groupe : la section entière tombe. Même convention que `sales_email_order_template` chez Magento.
- Déclenchement : `Observer/SendOrderStatusEmail` sur `sales_order_save_after`, **uniquement si le statut a réellement changé**, puis `Model/Order/Email/StatusEmailSender`. Aucune règle d'envoi dans un gabarit.
- Réglages : *Boutiques › Configuration › Ventes › Emails de vente*, groupes « Paiement reçu » et « Prête pour retrait » (activation, expéditeur, gabarit). Les deux sont activés par défaut sur le gabarit du module.
- L'email de retrait reprend le **commentaire de statut visible par la cliente** saisi dans la commande : c'est là que Céline écrira l'adresse, la date et l'heure de la remise en main propre.

L'accusé de réception du formulaire de contact (`MadameAiguille_Contact/email/acknowledgement.html`) a été repris pour utiliser la même enveloppe.

### Ce qui n'est pas bouclé

- ~~**Email de virement**~~ : sans objet depuis le call du 11/09/2026 (carte bancaire en ligne, paiement sur place au retrait). Aucun gabarit n'avait été créé.
- ~~**Modalités de retrait**~~ : depuis le lot 5, l'email « Prête pour retrait » reçoit aussi `pickup_slot`, `pickup_location_name`, `pickup_location_address` et `pickup_directions_url` (§26). Le commentaire visible reste repris en complément.
- **Mention de TVA** : en pied de tous les emails depuis le lot 5, via `{{config path="madameaiguille/legal/vat_mention"}}`.
- **Délivrabilité et SMTP** (SPF, DKIM, DMARC) : dépendent du domaine, **lot 8**. Tant que le SMTP de production n'est pas en place, un email peut partir sans arriver.
- **Recette de bout en bout** d'une commande réellement payée par Mollie : statuts câblés au lot 5, paiement de test à finir par Pierre (§23).

## 23. Après le lot 8a : ce qui reste à faire

Les lots 3, 4, 7, 5, 6b et **8a (infrastructure)** sont livrés. La boutique tourne sur `https://madame-aiguille.fr`, installation neuve, mode production, non indexée, administration réservée (§29). Les éléments suivants demandent du contenu réel, une action de Pierre ou de Céline, ou appartiennent au lot 8. La **recette fonctionnelle de production** (paiement, emails, newsletter, reCAPTCHA, pièces de vente) attend un catalogue réel et ouvre le lot 8.

| Sujet | Action attendue | Responsable / échéance |
|---|---|---|
| **Clés reCAPTCHA v2 invisible** | Créer les clés pour `madame-aiguille.fr` dans la console Google, les saisir dans *Sécurité › Google reCAPTCHA Storefront* ; **ensuite seulement** activer le type sur création de compte, contact, newsletter et mot de passe oublié (sans clés, ces formulaires refuseraient tout envoi). Recetter refus serveur et envoi normal | Pierre (clés), lot 8 (activation, recette) |
| **Catalogue réel** | Catégories et 2 à 3 premières créations saisies avec Céline dans l'administration (set « Création », poids obligatoire, photos 4:5), puis `madameaiguille:catalog:check-weight` | Pierre et Céline, avant la demande de validation Mollie |
| **Recette de production** | Sur le vrai site et un catalogue réel : paiement Mollie test relais + cadeau + CGV jusqu'au webhook et « Paiement reçu » ; annulation / échec et **restauration native du panier** ; retrait payé sur place ; **facture et avoir avec emballage** sur une commande dédiée (avoir déclenché par Pierre) ; emails **reçus** (confirmation, paiement reçu, prêt pour retrait, expédition, création de compte, contact avec JPG / PNG) ; newsletter invitée et connectée avec clic de confirmation, aucune inscription sans la case ; écrans du tunnel, des confirmations et du compte à 1440 / 390 px. Commandes de test annulées dans l'administration | Pierre et agent, début du lot 8 |
| **Validation du compte Mollie** | Demander la vérification du site dans le tableau de bord Mollie une fois catalogue et pages juridiques réels ; puis **un paiement réel** par Pierre (mode live, petit montant, remboursé) ; décider du mode (test ou live) jusqu'à l'ouverture | Pierre, après le catalogue |
| **Alerte Chrome « Site dangereux »** | Le formulaire de connexion de l'administration a été signalé à tort comme hameçonnage le 10/10/2026. Administration réservée aux IP connues ou au mot de passe HTTP, signalement envoyé à Google, propriété Search Console créée (aucun problème de sécurité affiché). Vérifier la levée de l'alerte ; surveiller *Problèmes de sécurité* | Pierre, sous quelques jours |
| **IP de Céline** | L'ajouter à la liste de l'administration (`deploy/serveur/90-acces-admin.sh <ip-pierre> <ip-céline>`) ; en attendant, identifiant et mot de passe HTTP | Pierre, quand elle sera connue |
| **Sauvegardes** | Aucune sauvegarde automatique de la base et des médias, hormis le dump pris par chaque bascule qui modifie la base. Planifier dump quotidien + médias, rétention, copie hors du VPS, **test de restauration** | Lot 8, **avant la première vraie commande** |
| **Code enseigne Mondial Relay** | Ouvrir ou transférer le compte **Offre Start**, puis saisir le code dans *Madame Aiguille › Mondial Relay*. Tant que `BDTEST` est en place, la carte affiche « compte de démonstration » | Céline |
| **Tarifs Mondial Relay** | Remplacer les tarifs provisoires de `Theme/data/tablerates-mondial-relay.csv` (FR, MC, BE, LU) par la politique de prix de Céline, puis rejouer l'import ; peser l'emballage type | Céline (tarifs), Pierre (CSV, déploiement) |
| **Lieu de retrait réel** | Adresse, coordonnées et, si souhaité, image de plan dans *Madame Aiguille › Retrait à l'atelier*. Un lieu générique « Saint-Épain (37800) » est en place | Céline |
| **Moyens de paiement Mollie** | Apple Pay, Google Pay, Bancontact, iDEAL, Wero et **Klarna** actifs à côté de la carte (choix provisoire de Pierre, reproduit sur le serveur par `KeepProvisionalMollieMethods`), **à valider avec Céline**. Écart avec le call (CB uniquement) et avec la CGV : trancher, puis aligner la CGV ou l'admin | Pierre / Céline, avant la validation Mollie |
| **Contenus juridiques** | Brouillons en place sur le serveur. Compléter : hébergeur (OVH), médiateur de la consommation, date de mise en ligne, délai de réponse ; citer **Brevo** (emails), **Google reCAPTCHA**, **Mondial Relay** et **OpenStreetMap** dans la confidentialité ; ajouter l'emballage cadeau à la CGV ; retirer les encadrés « Brouillon » | Céline avec conseil, avant la validation Mollie |
| **Contenus génériques** | Remplacer `home_story`, `home_actualities`, `a-propos`, `nos-tissus`, `contact_help`, `contact_locations` ; choisir la destination du CTA « Voir toutes les nouveautés » | Céline, avant la validation Mollie |
| **Téléphone de la boutique** | *Général › Informations sur le magasin* (non versionné, dépôt public) | Pierre / Céline |
| **Langue de l'administration** | Passer chaque compte en *Français (France)* dans *Paramètres du compte* (réglage par utilisateur, §28) | Pierre ; Céline à la création de son compte |
| **Compte et rôle de Céline** | Rôle ACL restreint et compte administrateur à son nom, double authentification ; guide du back-office | Lot 8 |
| **Ouverture des ventes** | Retirer `X-Robots-Tag` (`deploy/serveur/nginx/madame-aiguille.conf`, puis nginx rechargé) **et** passer *robots* à `INDEX,FOLLOW` ; `env:check --serveur` sans `--noindex` ; sitemap | Lot 8 |
| **Domaine secondaire** | `madameaiguille.fr` ne résout pas : le réserver et le rediriger en 301 | Pierre, lot 8 |
| **Délivrabilité** | DKIM Brevo et DMARC `p=none` en place ; SPF limité à OVH (alignement DMARC par DKIM). Vérifier la réception Gmail / Outlook / webmail français, durcir DMARC après observation | Lot 8 |
| **Hygiène du serveur** | Supprimer le compte RabbitMQ `guest` ; envisager de désactiver Magento Analytics et l'export Commerce (intégration « Magento Analytics user » créée par le cron, journaux d'export) ; `<title>` vide de l'accueil (aussi en local) ; espace avant le point dans « sans créer de compte . » (connexion) | Lot 8 |
| **Session / connexion dans le tunnel** | Fusion du panier à la connexion et expiration réelle de session conservées natives, non rejouées | Recette transversale du lot 8 |
| **Dépendances distantes du widget** | Leaflet est chargé par RequireJS à la même URL non versionnée que celle du widget ; une évolution distante peut nécessiter une nouvelle recette | Pierre, à chaque mise à jour / lot 8 |
| **Widget Mondial Relay** | Les onglets Horaires / Photo de l'infobulle de la carte restent bloqués par la CSP (identifiants variables) ; la liste et la sélection fonctionnent | Limite assumée |
| **Colis de plus de 5 kg** | `tablerate` n'a pas de borne haute : un colis lourd prend le tarif du dernier palier | Limite assumée, improbable |
| **Réseau TikTok** | Saisir l'URL dans *Madame Aiguille › Réseaux sociaux* ; tant qu'elle est vide, l'icône est masquée | Céline |
| **Seuil de TVA intra-UE** | Aucune TVA facturée, Céline étant en franchise de base. **À revoir au-delà de 10 000 € de ventes à distance intra-UE sur l'année** (guichet OSS) ; la mention se change dans *Mentions légales* | Céline avec conseil |
| Alerte ciblée « Me prévenir » | Décider si une alerte de retour d'une création précise est utile. L'inscription newsletter actuelle n'est pas une alerte de stock | Décision produit ultérieure |
| **Produit désactivé pendant qu'il est au panier** | Magento retire la ligne sans message. Décider si une information explicite est souhaitée — elle demanderait un observateur dédié | Décision Pierre |
| **Code promo du panier** | Activer le champ le jour où une règle de panier existe | Céline |
| **Textes des emails transactionnels** | Relire et personnaliser les gabarits natifs (confirmation, expédition, bienvenue, réinitialisation) dans *Marketing › Modèles d'e-mail* | Céline, avant ouverture |
| Production | Contrôler la purge des pièces jointes et des badges Nouveauté sur le serveur ; WebP et performances (Lighthouse) | Lot 8 |
| Logo et favicon | Vérifier le favicon à 16 / 32 px | Lot 8 |

Soldé au lot 8a : domaine, DNS, HTTPS (certificat `madame-aiguille.fr` + `www`), serveur aligné (Ubuntu 26.04, PHP 8.5.4, MariaDB 12.3.3, OpenSearch 3.9, Valkey 9.0.4, Varnish 7.7), SMTP Brevo opérationnel, webhook Mollie actif et joignable, clés Mollie saisies, double authentification, langue reproductible (paquet dans `composer.lock`), fontes provisionnées par le build, déploiement versionné et retour arrière, contrôle d'environnement. Détail : §29 et `docs/recettes/lot-8a.md`. Le prompt du lot suivant est `docs/prompts/prompt-lot8.md`.

## 24. Mémo Céline — tout ce qui se règle depuis le back-office

Récapitulatif de ce qui se modifie sans toucher au code, écran par écran. Chaque ligne renvoie à la section détaillée. **Après avoir modifié un bloc ou une page CMS, vider le cache** : *Système › Gestion du cache › Actualiser le cache invalidé*.

### Contenus éditables — *Contenu › Éléments › Blocs et Pages*

| Identifiant du bloc / page | Ce qu'il pilote | Détail |
|---|---|---|
| `header_announcement` | Bandeau en haut de toutes les pages. Le vider ou le désactiver masque le bandeau | §5 |
| `home_hero`, `home_story`, `home_actualities` | Accueil : image et accroche, histoire de l'atelier, marchés et congés | §7 |
| `product_reassurance` | Quatre arguments sous le bouton d'achat de la fiche produit | §10 |
| `cart_reassurance` | Deux lignes sous « Passer commande » dans le panier | §20 |
| `contact_help`, `contact_locations` | Encarts de la page Contact | §18 |
| Pages `a-propos`, `nos-tissus` | Pages éditoriales | §7 |
| Pages `cgv`, `mentions-legales`, `livraison-retours`, `confidentialite` | **Pages juridiques, pré-remplies d'un brouillon.** Chacune commence par un encadré « Brouillon à faire relire » : le retirer une fois le texte validé. Les éléments **entre crochets** sont à compléter (e-mail de contact, hébergeur, médiateur de la consommation, date) | §7 |

### Réglages — *Boutiques › Configuration › Général › Madame Aiguille*

| Réglage | Effet | Détail |
|---|---|---|
| Réseaux sociaux | Liens affichés dans le pied de page : Instagram, Facebook, TikTok. Un champ vide masque le réseau | §6 |
| Mentions légales › Mention de TVA | Imprimée sur les factures PDF et en pied de tous les e-mails. « TVA non applicable, art. 293 B du CGI. » tant que la franchise s'applique | §26 |
| Mondial Relay › Code enseigne | Code du compte pro (Offre Start). « BDTEST » = démonstration, la carte l'affiche | §26 |
| Retrait à l'atelier | Nom, adresse et indications du lieu ; latitude / longitude (lien « Itinéraire ») ; image de plan facultative ; **disponibilités de chaque semaine** (jour, de, à — plusieurs plages possibles) ; durée d'un créneau ; clientes par créneau ; délai minimum avant un retrait ; nombre de jours proposés ; **jours sans retrait** (une date `AAAA-MM-JJ` par ligne, ou une période `AAAA-MM-JJ/AAAA-MM-JJ`) | §26 |
| Emballage cadeau | Proposer ou non l'option, son **prix**, son libellé et le texte affiché sous la case | §26 |
| Catalogue › Seuil de rareté | À partir de combien d'exemplaires restants la mention « Plus que N exemplaires » s'affiche. `0` désactive | §8 |
| Catalogue › Mention sous le prix | Petite ligne sous le prix en fiche produit **et** sous le total du panier. Vide = rien | §8, §20 |
| Panier › Seuil de livraison offerte | Montant à partir duquel le point relais est offert (**60 €**), et montant de la barre du panier. `0` supprime le franco et la barre. Penser au bandeau et au bloc `product_reassurance`, qui citent « 60 € » en toutes lettres | §20, §26 |
| Panier › Afficher le champ code promo | Masqué tant qu'aucun code n'existe | §20 |
| Formulaire de contact › Conservation des pièces jointes | Durée avant suppression automatique des photos reçues (30 jours par défaut) | §18 |

### Livraison — *Boutiques › Configuration › Ventes › Méthodes de livraison*

| Réglage | Effet | Détail |
|---|---|---|
| Retrait à l'atelier (Madame Aiguille) | Activer ou couper le retrait ; libellés ; **pays où il est proposé** (France par défaut) | §26 |
| Mondial Relay (*Table Rates*) | Activation et libellés. **Les tarifs ne se saisissent pas ici** : envoyer la grille à Pierre, qui met à jour le fichier versionné et le réimporte | §26 |

### Tunnel, CGV et newsletter — lot 6b

| Quoi | Où / effet | Détail |
|---|---|---|
| Texte des CGV | *Contenu › Pages › cgv*. Le lien du tunnel ouvre cette page ; la case d’acceptation ne remplace pas sa relecture juridique | §7, §27 |
| Case obligatoire des CGV | *Magasins › Paramètres › Conditions générales de ventes*. Accord « Conditions générales de vente », actif, application **manuelle**, toutes les vues. Modifier son texte ici ; conserver son activation et le mode manuel | §27 |
| Activation des accords | *Magasins › Configuration › Ventes › Commander › Options de commande › Activer les conditions générales* = Oui | §27 |
| Téléphone obligatoire | *Magasins › Configuration › Clients › Configuration client › Options de nom et d’adresse › Afficher le téléphone* = Obligatoire | §27 |
| Newsletter | *Magasins › Configuration › Clients › Newsletter* : activation, inscriptions invitées, email de confirmation et expéditeur. Garder **la confirmation** activée. La case du tunnel reste facultative et décochée | §27 |
| Abonnés | *Marketing › Communications › Abonnés à la newsletter*. Ne pas confondre inscription non confirmée et abonnement actif ; aucune inscription n’est faite si la case est laissée vide | §27 |
| Lieu de la confirmation de retrait | Les mêmes réglages *Madame Aiguille › Retrait à l’atelier* alimentent le tunnel, les emails et la page de succès | §26, §27 |
| Protection de création de compte | *Magasins › Configuration › Sécurité › Google reCAPTCHA Storefront*. Pierre configure les clés et le domaine ; le formulaire natif est conservé | §27 |

### Se connecter à l'administration et réglages du site en ligne — lot 8a

| Quoi | Où / effet | Détail |
|---|---|---|
| Ouvrir l'administration | Adresse privée transmise par Pierre (à garder dans un gestionnaire de mots de passe). Depuis une IP enregistrée, la page s'ouvre ; ailleurs (smartphone, déplacement), le navigateur demande d'abord un identifiant et un mot de passe communs, transmis par Pierre, puis Magento demande votre compte | §29, `deploy/serveur/README.md` |
| Double authentification | À la première connexion, Magento envoie un lien par email pour associer **Google Authenticator** ; ensuite, un code à six chiffres à chaque connexion. Téléphone perdu : demander à Pierre de réinitialiser | §29 |
| Envoi des emails | *Magasins › Configuration › Avancé › Système › Paramètres d'envoi des e-mails* : SMTP Brevo, **ne pas modifier** sans Pierre (un mauvais réglage coupe tous les emails de commande) | §29 |
| Clés Mollie et moyens de paiement | *Magasins › Configuration › Ventes › Moyens de paiement › Mollie* : mode test / live, clés, activation de chaque moyen ; bouton d'autotest du webhook | §26, §29 |
| Clés reCAPTCHA | *Magasins › Configuration › Sécurité › Google reCAPTCHA Storefront* : les clés d'abord, le type par formulaire ensuite | §23, §27 |
| Téléphone et adresse de la boutique | *Magasins › Configuration › Général › Général › Informations sur le magasin* | §29 |
| Ouverture des ventes | Indexation par les moteurs et retrait de l'en-tête serveur : Pierre, au lot 8 | §23 |

### Emails — *Boutiques › Configuration › Ventes › Emails de vente*

| Réglage | Effet | Détail |
|---|---|---|
| Paiement reçu › Activé / Expéditeur / Gabarit | Notification envoyée automatiquement quand une commande passe au statut « Paiement reçu » | §22 |
| Prête pour retrait › Activé / Expéditeur / Gabarit | Notification envoyée quand une commande passe au statut « Prête pour retrait ». Elle contient automatiquement **le rendez-vous choisi, le lieu et le lien d'itinéraire** ; un commentaire visible saisi dans la commande s'y ajoute | §22, §26 |
| Confirmation, expédition, avoir… | Réglages natifs de Magento, inchangés | §22 |

Les **textes** des emails se modifient dans *Marketing › Communications › Modèles d'e-mail* : dupliquer le gabarit voulu, le modifier, puis le sélectionner dans le réglage correspondant. L'en-tête au logo et le pied de page de la marque s'appliquent automatiquement — inutile de les recopier dans chaque gabarit.

### Commandes — *Ventes › Commandes*

| Quoi | Où | Détail |
|---|---|---|
| Faire avancer une commande | Ouvrir la commande, puis **Commentaires sur l'historique** : choisir le statut et enregistrer | §21 |
| Les six statuts | *En attente de paiement*, *Paiement reçu*, *En préparation*, *Expédiée*, *Prête pour retrait*, *Livrée*. La cliente voit le même libellé, accompagné d'une phrase qui explique où en est sa commande | §21 |
| Prévenir la cliente | Cocher **Visible par le client** avant d'enregistrer un commentaire. Pour un retrait, y écrire les modalités : elles partent dans l'email | §21, §22 |
| Renommer un statut | *Ventes › Statuts de commande* : le libellé change côté cliente **et** côté back-office. Ne pas supprimer un statut ni changer son code | §21 |
| **Expédier en point relais** | L'*Adresse de livraison* de la commande est celle du point (« NOM — Point relais FR-087807 »). Créer l'étiquette sur l'espace pro Mondial Relay avec cette adresse, puis cliquer **Expédier** dans la commande (numéro de suivi facultatif) : la commande passe en « Expédiée » et l'email d'expédition part | §26 |
| **Préparer un retrait** | Le rendez-vous figure dans *Méthode de livraison* (« Retrait à l'atelier — jeudi 15 octobre 2026 à 9 h 00 »). Quand la commande est prête : statut **Prête pour retrait**, l'email part avec le rendez-vous et le lieu | §26 |
| **Remettre un retrait** | Au rendez-vous, encaisser (TPE ou espèces), puis **Facturer** et **Expédier** la commande : elle passe en « Livrée » | §26 |
| **Rendez-vous non honoré** | Annuler la commande (*Annuler* en haut de la commande) : le stock des créations et le créneau sont libérés automatiquement. Prévenir la cliente si besoin par un commentaire visible | §26 |
| Emballage cadeau | Ligne « Emballage cadeau » dans les totaux de la commande ; le message à écrire sur la carte est dans *Message cadeau* du détail | §26 |

### Catalogue — *Catalogue › Produits* et *Catalogue › Catégories*

| Quoi | Où | Détail |
|---|---|---|
| Créer une création | *Produits › Ajouter un produit* → choisir l'attribute set **« Création »** | §8 |
| Photos | Ratio **4:5** (portrait), trois vues minimum. Une photo hors ratio est rognée, pas déformée | §8 |
| Poids | **Obligatoire**, en kilogrammes — il servira au calcul des frais de port | §8 |
| Stock | Le nombre saisi est le nombre de pièces restantes. À 0, la création passe « Épuisé » et reste visible en fin de liste | §8 |
| Série limitée | Cocher *Série limitée* et renseigner le nombre de pièces | §8 |
| Modèle en deux tailles | Produit **configurable** sur l'attribut Taille ; chaque taille a son propre stock et son propre poids | §8 |
| Badge « Nouveauté » | Dates *Définir le produit comme nouveau à partir de / jusqu'au*. Ces dates alimentent aussi le bloc Nouveautés de l'accueil et les suggestions du panier vide | §8, §19 |
| Suggestions « Vous aimerez aussi » | Onglet *Produits liés*, section **Produits liés** | §10 |
| Ordre du menu et des catégories | *Catégories*, par glisser-déposer ; case « Inclure dans le menu » | §5 |

### Langue

| Quoi | Où | Détail |
|---|---|---|
| Mettre l'administration en français | En haut à droite : *nom du compte › Paramètres du compte › Langue de l'interface* → *Français (France)*, puis enregistrer avec son mot de passe | §28 |
| Corriger un texte du site | Demander à Pierre : les traductions vivent dans les dictionnaires du thème | §28 |

### Ce qu'il ne faut pas faire

- **Ne pas désactiver la double authentification** ni partager l'identifiant et le mot de passe HTTP en dehors de Pierre et Céline.
- **Ne pas passer Mollie en mode live** ni modifier les webhooks sans Pierre : une commande payée resterait « En attente de paiement ».

- **Ne pas décocher « Gérer le stock »** sur une création : les badges et le plafond de quantité du panier en dépendent.
- **Ne pas supprimer une catégorie contenant des produits** sans les avoir déplacés d'abord.
- **Ne pas annoncer un délai ou un tarif** dans un bloc CMS tant qu'il n'est pas confirmé : les textes actuels sont des exemples à remplacer.
- **Ne pas réactiver** les transporteurs *Flat Rate* et *Free Shipping* : le franco est porté par le point relais, une méthode « Livraison gratuite » séparée n'aurait pas de point relais.
- **Ne pas importer une grille de tarifs depuis l'administration** : elle serait écrasée au prochain import du fichier versionné. Passer par Pierre.

## 25. Rituel de fin de lot

**Un lot n'est pas fini quand le code marche : il est fini quand il est recetté, documenté, fusionné et que le lot suivant est prêt à démarrer.** Cette liste est à dérouler intégralement, sans qu'on ait à la redemander. Si un point ne peut pas être tenu, on le dit et on le consigne comme limite — on ne le saute pas en silence.

### 1. Recette

- Contrôle visuel à **1440 et 390 px** sur chaque écran livré.
- Contrôle **dans l'administration** de chaque écran touché par le lot : une section de configuration qui ne s'ouvre plus ne se voit ni dans les tests, ni en façade (cf. §22, l'identifiant de gabarit d'email).
- Tests unitaires du module et `phpcs --standard=Magento2` sur les fichiers touchés.
- Vérifier que la feuille servie au navigateur est bien la dernière compilée (§2, taille du fichier).

### 2. Documentation

- Une **section par écran ou par mécanisme livré** dans ce document, avec les chemins réels.
- **§24 Mémo Céline** : tout ce que le lot rend réglable sans code — un lot qui ajoute un réglage et ne l'y inscrit pas est un réglage qu'elle ne trouvera jamais.
- **§23** : ce qui n'a pas pu être bouclé, avec son responsable et son échéance.
- **§17 Journal des livraisons** : une ligne par étape, avec les commits.
- **Pièges rencontrés** : consignés là où on les cherchera, pas dans un commit.
- `plan-de-developpement.md` : note de version, état après le lot, incertitudes levées, titre du lot marqué livré.
- `/styleguide` : les états visuels du lot, produits par les **vrais** ViewModels quand c'est possible.

### 3. Fusion et publication

- Vérifier `git status` propre et l'arbre à jour.
- Fusionner dans `main` — l'historique du dépôt est **linéaire**, on fusionne en avance rapide.
- Pousser `main` et la branche du lot sur GitHub.

### 4. Passer la main

- Écrire le **prompt de reprise du lot suivant** dans `docs/prompts/`, sur le modèle de `prompt-lot5.md` : état de départ vérifiable, ce qui est déjà tranché, règles non négociables, périmètre, ce qui ne pourra pas être bouclé, décisions à demander à Pierre, pièges connus du projet.
- Mettre à jour le prompt avec les pièges découverts pendant le lot qu'on vient de finir.

## 26. Livraison et paiements (lot 5)

Lot livré le 09/10/2026 sur `codex/lot-5-livraison-paiements`, **hors 8a** (mise en ligne anticipée), différé par Pierre faute d'accès au domaine et au serveur. Périmètre arrêté par le call Céline du 11/09/2026 et les arbitrages de Pierre du 09/10/2026 (`docs/brief-call-celine-2026-09-11.md`). Spike Mondial Relay : `docs/spike-mondial-relay.md`.

### Où se trouve quoi

La logique du tunnel vit dans `MadameAiguille_Checkout`. Au lot 5, ses composants Knockout (`view/frontend/web/`) étaient livrés bruts par le module, en attendant le thème Luma enfant. Le lot 6b a créé `MadameAiguille/checkout` et y surcharge les gabarits au même chemin relatif (§27). Les originaux et leur logique métier restent dans le module ; Luma n’hérite toujours pas du thème Hyvä.

| Mécanisme | Fichiers |
|---|---|
| Socle de vente (zone, origine, TVA, chèque coupé, message cadeau) | `Theme/Setup/Patch/Data/ConfigureSalesFoundations.php` |
| Grille au poids Mondial Relay | `Theme/data/tablerates-mondial-relay.csv`, `Theme/Model/Shipping/{TableRateImporter,GridRules}.php`, `Theme/Console/Command/ImportTableRates.php`, `Theme/Setup/Patch/Data/ConfigureRelayShipping.php` |
| Franco | `Theme/Plugin/Shipping/FreeRelayAboveThreshold.php`, `Theme/Setup/Patch/Data/DisableNativeFreeShipping.php` |
| Contrôle des poids | `Theme/Model/Catalog/MissingWeightFinder.php`, `Theme/Console/Command/CheckWeight.php` |
| Point relais | `Checkout/Model/RelayPoint/*`, `Checkout/Plugin/Checkout/AssignRelayPoint.php`, `Checkout/Observer/ApplyRelayPoint.php`, `view/frontend/web/js/{view,model}/relay-point.js`, `template/relay-point.html`, `etc/csp_whitelist.xml` |
| Retrait à l'atelier et paiement sur place | `Checkout/Model/Carrier/Pickup.php`, `Checkout/Observer/RestrictPaymentToDelivery.php`, `Checkout/Setup/Patch/Data/EnablePayOnSite.php` |
| Rendez-vous de retrait | `Checkout/Model/Pickup/*`, `Checkout/Plugin/Checkout/AssignPickupSlot.php`, `Checkout/Observer/{ReservePickupSlot,AttachPickupBooking,ReleasePickupBooking}.php`, `Checkout/Block/Adminhtml/Form/Field/*`, `etc/webapi.xml`, `js/view/pickup-slot.js`, `template/pickup-slot.html` |
| Emballage cadeau | `Checkout/Model/GiftWrap/*`, `Checkout/Model/Total/{Quote,Invoice,Creditmemo}/GiftWrap.php`, `etc/sales.xml`, `etc/pdf.xml`, `Checkout/Block/Sales/GiftWrapTotal.php` (+ layouts `sales_*`), `Checkout/Model/Mollie/GiftWrapLine.php`, `js/view/gift-wrap.js`, `js/view/summary/gift-wrap.js` |
| Statuts sur le réel | `Checkout/Setup/Patch/Data/MapMollieStatuses.php`, `Checkout/Plugin/Sales/StatusOnCompletion.php` |
| Report sur la commande | `Checkout/Plugin/Sales/CopyDeliveryChoicesToOrder.php` (adresse), `Checkout/Observer/CopyGiftWrapToOrder.php` (montants) |
| Validation de l'étape Livraison, envoi au serveur, récapitulatif | `js/mixin/{shipping,payload-extender,shipping-information}-mixin.js` |
| Textes alignés, mention de TVA, TikTok | `Theme/Setup/Patch/Data/{AlignDisplayWithDelivery,AlignHeaderAnnouncement}.php`, `Theme/Plugin/Sales/VatMentionOnInvoicePdf.php`, `Magento_Email/email/footer.html`, `Magento_Theme/layout/default.xml` |

### Configuration arrêtée

Posée par data patches, une seule fois : un réglage modifié ensuite dans l'admin n'est jamais réécrit.

| Chemin | Valeur |
|---|---|
| `general/country/allow` | `FR,BE,LU,MC` — la Suisse est écartée |
| `shipping/origin/*` | 35 Grande Rue, 37800 Saint-Épain, Indre-et-Loire, FR |
| `tax/defaults/country` | `FR` ; aucune règle de taxe, prix TTC = prix encaissés |
| `payment/checkmo/active` | `0` |
| `payment/mollie_methods_*/active` | le patch ne laissait que la carte bancaire ; **Pierre a ensuite réactivé** Apple Pay, Google Pay, Bancontact, iDEAL, Wero et Klarna depuis l'admin et demandé de garder cette configuration (09/10/2026). Écart avec le call consigné au §23 |
| `payment/mollie_general/order_status_*` | `madameaiguille_pending_payment` / `madameaiguille_payment_received` |
| `payment/cashondelivery/*` | actif, renommé « Paiement sur place » |
| `carriers/tablerate/*` | actif, « Mondial Relay — Livraison en point relais », condition `package_weight` |
| `carriers/flatrate/active`, `carriers/freeshipping/active` | `0` |
| `carriers/madameaiguille_pickup/*` | actif, France seulement |
| `sales/gift_options/allow_order` | `1` — le message cadeau natif se saisit dans le panier |
| `madameaiguille/cart/free_shipping_threshold` | `60` (défaut du module) |

Les clés Mollie (`payment/mollie_general/*`) sont saisies dans l'admin par Pierre ; aucune n'est dans le dépôt. `use_webhooks = disabled` en local : **à réactiver en préproduction**.

### Grille au poids

Le CSV a le format de l'export natif de l'admin (`Country, Region/State, Zip/Postal Code, Weight (and above), Shipping Price`), paliers 0 · 0,25 · 0,5 · 1 · 2 · 5 kg pour FR, MC, BE et LU. **Tarifs provisoires** en attendant les tarifs pro Mondial Relay de Céline.

```bash
bin/magento madameaiguille:shipping:import-rates --dry-run   # valide sans écrire
bin/magento madameaiguille:shipping:import-rates             # remplace la grille du site « base »
```

La lecture reprend l'import natif ; en plus, chaque pays doit être un pays de vente et commencer à 0 kg. Tout se fait dans **une transaction** : un CSV invalide laisse la grille en place. Rejouable en préproduction et en production.

**Limite native** : `tablerate` n'a pas de borne haute. Un colis de plus de 5 kg prend le tarif du dernier palier.

### Franco

Une seule valeur : *Madame Aiguille › Panier › Seuil de livraison offerte*. La barre du panier et le tarif lisent la même valeur ; au-delà du seuil, le point relais passe à 0 € (sous-total avant remise, comme la barre). Le carrier natif `freeshipping`, qui affichait le franco comme une méthode à part sans point relais, est coupé.

### Point relais

Widget officiel Mondial Relay, chargé **à la demande** au premier choix du point relais : aucune requête vers Mondial Relay, unpkg ou OpenStreetMap avant. Seul le **code enseigne** est nécessaire (*Madame Aiguille › Mondial Relay*), `BDTEST` en attendant l'Offre Start ; il est complété à huit caractères par des espaces.

Pendant le tunnel, **l'adresse du panier reste celle de la cliente** : le point validé est mémorisé à côté (`quote_address.madameaiguille_relay_point_id` et `madameaiguille_relay_point`, JSON). À la validation, l'adresse du point devient l'adresse de livraison **de la commande** : société « NOM — Point relais FR-087807 », rue, code postal, ville, pays ; nom et téléphone de la cliente conservés pour l'étiquette. L'identifiant est gardé dans `sales_order_address.madameaiguille_relay_point_id`.

Contrôles serveur : point obligatoire, identifiant `XX-NNNNNN` cohérent avec le pays, pays de vente, champs non vides. Monaco cherche dans le réseau français.

### Retrait à l'atelier et rendez-vous

Transporteur `madameaiguille_pickup` à 0 €, France seulement (réglage natif *Pays*). Paiement **sur place** uniquement — et les paiements en ligne ne sont proposés qu'avec le point relais (`Observer\RestrictPaymentToDelivery`, la méthode `free` reste toujours possible).

La cliente choisit un jour puis une heure parmi les créneaux libres, lus par `GET /V1/madameaiguille/pickup-slots`. Calcul : plages hebdomadaires, durée d'un créneau, délai de prévenance, horizon, jours sans retrait (`Model\Pickup\SlotCalendar`, testé). Le créneau est revérifié au passage de l'étape Livraison, puis **réservé à la validation de la commande** : une ligne par place dans `madameaiguille_pickup_booking`, clé unique `(slot, seat)`. Deux clientes qui valident le même créneau au même instant : la seconde reçoit « Ce créneau vient d'être réservé. Revenez à l'étape Livraison pour en choisir un autre. » La place est libérée si la commande échoue, ou à l'**annulation** de la commande.

Restitution : le rendez-vous s'ajoute à la description de livraison (« Retrait à l'atelier - … — jeudi 15 octobre 2026 à 9 h 00 »), donc à l'admin, aux emails, à la facture et au compte. L'email « Prête pour retrait » reçoit le rendez-vous, le lieu et le lien d'itinéraire (`Model\Pickup\EmailVariables`, branché sur l'expéditeur du lot 7 par `Theme\Model\Order\Email\TemplateVariablesProviderInterface`). Le rendez-vous est formaté dans la **langue de la boutique**, pas celle de l'utilisateur de l'admin.

Lieu générique en place : « Atelier Madame Aiguille, Saint-Épain (37800) ». Pas de carte interactive : une image de plan facultative et un lien d'itinéraire Google Maps, pour un lieu unique et sans question RGPD.

### Emballage cadeau

Option de l'étape Livraison, 2 € par défaut (*Madame Aiguille › Emballage cadeau* : activation, prix, libellé, texte). Le *Gift Wrapping* natif est réservé à Adobe Commerce : la ligne est un total maison, porté par le panier (`quote.madameaiguille_gift_wrap`, montants sur `quote_address`), la commande, **la première facture** et **l'avoir qui solde la commande** (un retour partiel ne rembourse pas l'emballage). Affichée dans le récapitulatif du tunnel, le compte, les emails, l'admin et le PDF. Mollie reçoit une ligne de surcharge dédiée, pour que la somme des lignes égale le total.

Le **message cadeau** natif (la carte écrite à la main) se saisit dans le panier et s'affiche dans le détail de commande depuis le lot 7.

### Statuts

| Évènement | Statut |
|---|---|
| Commande Mollie créée | En attente de paiement |
| Paiement Mollie capté | Paiement reçu → email du lot 7 |
| Commande de retrait créée | `pending`, affichée « En attente de paiement » |
| Expédition d'un colis | Expédiée (+ email d'expédition natif) |
| « Expédition » d'un retrait (remise en main propre) | Livrée |

Un statut posé à la main par Céline n'est jamais réécrit. L'email de virement esquissé au lot 7 n'avait pas de gabarit : rien à retirer ; les mentions « virement » des blocs, du pied de page et du styleguide sont supprimées.

### Mentions et affichage

« TVA non applicable, art. 293 B du CGI. » : réglage *Madame Aiguille › Mentions légales*, imprimé en pied de chaque page des **factures PDF**, en pied de **tous les emails** (`{{config}}` autorisé par `di.xml`), sous le prix et dans le copyright. Rassurance, pied de page et bandeau ne promettent plus que Mondial Relay, le retrait, la carte bancaire ou le paiement sur place, 4 à 5 jours ouvrés et le franco à 60 €. **TikTok** remplace Pinterest.

### Pièges rencontrés

- **CSP bloquante du tunnel** (Magento ≥ 2.4.7) : le widget Mondial Relay injecte un script inline (`MondialRelayLanguage`) et des `onclick` inline. Le script et trois gestionnaires statiques sont autorisés par empreinte ; le composant définit aussi la variable lui-même ; les `onclick` de la liste sont retirés en phase de capture et l'action est rejouée par `FocusOnMap`. Les onglets Horaires / Photo de l'infobulle restent bloqués (identifiants variables).
- **`populateWithArray()` ignore les clés sans setter** : un fieldset ne suffit pas pour reporter un champ maison du panier vers la commande. Adresse : plugin `afterConvert` sur `ToOrderAddress`. Commande : `QuoteManagement` fusionne en plus par `mergeDataObjects()` → observateur sur `sales_model_service_quote_submit_before`.
- **Titre d'un segment de total** : l'API ne le rend que si c'est une `Phrase` ; une chaîne donne un titre vide.
- **Ordre des totaux du tunnel** : fixé par *Ventes › Ordre des totaux du tunnel* (`sales/totals_sort`) ; un `sortOrder` maison doit s'y intercaler (livraison 30, taxe 40).
- **Clé de traduction avec deux-points** dans un gabarit Knockout (`translate="'Votre rendez-vous :'"`) : le préprocesseur produit une liaison invalide et Knockout abandonne tout le sous-arbre, sans erreur visible.
- **Fichiers statiques figés en développement** : `Cache-Control: immutable`, un an. Ni Ctrl+Maj+R ni le vidage du cache ne suffisent pour un JS chargé par RequireJS : supprimer `pub/static/deployed_version.txt` (régénéré à la requête suivante). Le dictionnaire `pub/static/frontend/Magento/luma/fr_FR/js-translation.json` se régénère de même, puis vider `mage-translation-storage` dans le `localStorage` du navigateur. La page du tunnel elle-même peut aussi rester en cache : recharger par une URL différente.
- **Nouvel argument `di.xml` (commande CLI, plugin)** : `setup:di:compile`. Une compilation lancée pendant que php-fpm sert des pages peut échouer sur « directory not empty » : relancer.
- **Message natif trompeur** : un produit sans stock vendable donne « Some of the products are disabled » au passage de commande par l'API.

### Recette du 09/10/2026

- Spike : widget avec `BDTEST`, points en FR (37800), BE (1000), LU (1611, 2449, 4011, 9010), sélection et callback.
- Tarifs par l'API : FR 5,50 € et BE / LU 6,50 € pour un sac de 0,25 à 0,5 kg ; Suisse sans tarif ; 0 € au-delà de 60 € ; plus de méthode « Livraison gratuite » séparée.
- Import : à blanc, réel, rejoué ; CSV invalide refusé, grille intacte.
- Point relais dans le navigateur : 7 points autour de Saint-Épain, sélection au clic sans erreur CSP, blocage de l'étape sans point, refus serveur sans point ou hors zone, adresse du panier inchangée, adresse de commande = point (`FR-087807`).
- Retrait : FR seulement, 0 €, « Paiement sur place » seul ; point relais : paiements en ligne seuls.
- Rendez-vous : 65 créneaux sur 3 semaines au 09/10 (premier : jeudi 15 à 9 h, prévenance 48 h) ; **collision** de deux paniers sur le même créneau → le second refusé ; créneau retiré de l'API puis rendu à l'annulation ; commande passée dans le navigateur, description et réservation conformes.
- Statuts : commande Mollie en « En attente de paiement » ; retrait facturé puis expédié → « Livrée ».
- Emballage : segment « Emballage cadeau 2,00 € » placé après la livraison ; commande à 34 € dont 2 € ; facture à 34 €, reste dû 0 ; ligne présente dans l'email et le PDF.
- Mentions : PDF de facture, directive `{{config}}` des emails en mode strict, sous le prix, copyright.
- Visuel à 1440 et 390 px : fiche produit (rassurance, mention), panier (barre à 60 €, rassurance, bandeau corrigé), tunnel à 375 px sans débordement.
- Administration : **pas de session disponible pour l'agent** ; structure des sections *Madame Aiguille* (Mondial Relay, Retrait, Emballage, Mentions légales) et *Méthodes de livraison* chargée sans erreur, grille des plages rendue avec ses valeurs par défaut. Contrôle visuel à faire par Pierre (§23).
- Tests unitaires : **85 tests, 228 assertions**. `phpcs --standard=Magento2` : 0 erreur ; avertissements de docblocks absents, comme le reste du projet.
- `/styleguide` : **aucun état ajouté**. Les composants du lot vivent dans le tunnel Luma, que la page Hyvä ne sait pas rendre ; leur démonstration se fait dans le tunnel lui-même. Les textes alignés (rassurance, mention sous le prix) apparaissent dans les vrais blocs de la fiche et du panier.
- Paiement Mollie de bout en bout **non bouclé par l'agent** : les champs carte de la page de test Mollie sont des iframes qui refusent la saisie simulée. La commande `000000005` attend son paiement de test.


## 27. Habillage du tunnel et confirmations (lot 6b)

Livré le 09/10/2026 sur `lot-6b-habillage-tunnel`, depuis `main` (`8ee229c`). Pierre a demandé de reprendre aussi la mise en page du récapitulatif des maquettes, confirmé téléphone et CGV obligatoires, newsletter facultative décochée. Les méthodes Mollie sont provisoires à valider avec Céline. Pas de nouveau champ « Message pour Céline » en l’absence d’arbitrage : le message cadeau natif du panier reste disponible.

### Thème, identité et en-tête / pied

Tous les chemins suivants partent de `shop/app/design/frontend/MadameAiguille/checkout/` :

| Mécanisme / écran | Fichiers réels |
|---|---|
| Déclaration Luma enfant | `registration.php`, `theme.xml`, `composer.json` (parent `Magento/luma`) |
| Palette et fontes | `web/css/source/_tokens.less`, `_theme.less`, `_typography.less` ; WOFF2 originaux dans `web/fonts/`, ignorés par Git, licences versionnées |
| Règles du tunnel | `web/css/source/_extend.less`, `_checkout.less` ; pas de Tailwind |
| Logo et favicon | `web/images/logo-rectangulaire.jpg`, `Magento_Theme/web/favicon.ico` |
| Structure et récapitulatif | `Magento_Checkout/layout/checkout_index_index.xml`, `Magento_Checkout/web/template/summary.html` |
| Retour au panier et pied minimal | `Magento_Checkout/templates/header/back.phtml`, `footer.phtml` |
| Étapes / récapitulatif mobile | `Magento_Checkout/web/template/progress-bar.html`, `estimation.html` |
| Relais, rendez-vous et cadeau | `MadameAiguille_Checkout/web/template/relay-point.html`, `pickup-slot.html`, `gift-wrap.html`, `summary/gift-wrap.html` |
| Traductions propres au tunnel | `i18n/fr_FR.csv` |

`MadameAiguille_Theme/etc/config.xml` porte la valeur par défaut du fallback : `frontend/MadameAiguille/checkout`. Le layout natif, les champs UI et les renderers de paiement restent en place. Les actions et champs ont une hauteur de 48 px, les cibles des étapes et de la carte au moins 44 px ; focus visible, messages contrastés et palette centralisée. Le widget garde ses logos tiers et son avertissement `BDTEST` visible.

**Synchronisation manuelle des tokens** : reporter toute évolution de `default/web/tailwind/hyva.config.json` dans `_tokens.less`, comme pour les variables d’email. Ne pas changer une couleur en dur dans une règle. Conserver les variables structurelles de Luma, dont `@total-columns: 24` : les remplacer implicitement par les valeurs Blank fait déborder la colonne native.

### Livraison, paiement et récapitulatif

`requirejs-config.js` active des mixins de présentation du thème : `web/js/summary/full-mode.js` montre les totaux natifs dès la livraison, `expanded-items.js` garde les articles ouverts, `view/payment-title.js` traduit notamment le titre Klarna, `view/agreements-link.js` fournit l’URL de la page CGV. Aucun calcul de tarif n’est déplacé dans le thème. Les montants pendant la livraison sont **estimatifs**, une mention le précise ; le calcul final, cadeau et port compris, arrive avec l’enregistrement natif de l’adresse au passage au paiement. Le tiroir de récapitulatif mobile reste celui de Magento.

`web/js/view/relay-accessibility.js` donne aux résultats natifs du widget un rôle de bouton et un accès clavier : Entrée / Espace déclenchent le même clic, pris en charge par l’adaptateur CSP du lot 5. Il ne remplace ni la recherche ni la validation du point. La feuille tierce imposant Montserrat avec `!important`, le thème impose Sentient sur le widget avec la même priorité. Aucune règle ne masque le mode démonstration. Les onglets Horaires / Photo restent la limite CSP du lot 5.

**Correctif de recette RequireJS** : le widget recharge Leaflet dans son `init`, même si `window.L` existe. Sa déduplication compare seulement le `src` exact. Le chargement versionné `leaflet@1.9.4` du lot 5 était donc suivi de `leaflet/dist/leaflet.js`, enregistré comme module AMD anonyme ; le rendu natif des coordonnées au paiement échouait. `Checkout/view/frontend/requirejs-config.js` utilise désormais la même URL que le widget, chargée une seule fois par RequireJS. Cela rétablit le récapitulatif sans modifier sélection, validation ou calculs. Conséquence assumée : Leaflet suit l’URL distante de l’éditeur, comme le widget ; recetter ces dépendances lors des mises à jour.

### CGV obligatoires et newsletter facultative

Le data patch `Checkout/Setup/Patch/Data/EnableCheckoutAgreement.php` crée un **accord Magento natif actif et manuel**, toutes vues, et active `checkout/options/enable_agreements`. Il ne réécrit pas un accord déjà créé sous le même nom. Le texte complet reste dans la page CMS `cgv`, accessible sous la case ; son brouillon doit être validé avant ouverture. La validation cliente et `AgreementsValidatorInterface` refusent une commande sans l’identifiant de l’accord. Le bouton traduit indique « Commander avec obligation de paiement ».

La newsletter est rendue par `checkout/web/js/view/newsletter.js` et `checkout/Magento_Checkout/web/template/newsletter.html`, dans la région native `beforeMethods`. Sa valeur initiale est toujours `false`. Le hook Magento de placement de commande ajoute uniquement le booléen `madameaiguille_newsletter` dans `paymentMethod.additional_data`.

La logique est dans `MadameAiguille_Checkout` : `Model/Newsletter/ConfigProvider.php` respecte l’activation de la newsletter et l’autorisation des inscriptions invitées ; `Plugin/Checkout/CaptureNewsletterConsent.php` normalise et conserve le consentement dans les informations de paiement ; `Observer/SubscribeNewsletter.php`, sur `sales_model_service_quote_submit_success`, appelle le `SubscriptionManagerInterface` natif avec l’email et le store de la commande invitée, ou `subscribeCustomer` pour rattacher l’abonnement au compte connecté. Le refus des inscriptions invitées est aussi vérifié côté serveur. Une case vide ne désinscrit jamais un abonnement existant. Une erreur newsletter est journalisée sans faire échouer une commande déjà créée. Le service natif applique la confirmation email, activée en local. Réception et clic de confirmation restent à recetter avec SMTP au 8a.

### Succès, échec et création de compte — thème Hyvä

Les surcharges restent dans `default/Magento_Checkout/` : layouts `checkout_onepage_success.xml` / `checkout_onepage_failure.xml`, `templates/success.phtml`, `onepage/failure.phtml`, `registration.phtml` et `confirmation/delivery.phtml`. Titres de page et contenus sont français, avec les blocs et URL natives. L’échec invite à vérifier et réessayer, sans affirmer qu’aucun débit n’a eu lieu. La restauration du panier demeure à la charge du retour Mollie natif ; le thème ne supprime ni ne recrée de panier.

`Checkout/ViewModel/Confirmation.php` lit uniquement la **dernière commande de la session**, jamais un identifiant fourni dans l’URL. Pour le retrait, il réutilise `Model/Pickup/EmailVariables` (rendez-vous français, lieu, itinéraire). Pour le relais, il prend l’adresse de livraison de la commande, déjà transformée au lot 5. Les `.phtml` se limitent à l’affichage échappé. Une adresse sans données métier ne produit pas de faux détail de livraison.

Le lien « Créer mon compte » après commande conserve `getCreateAccountUrl()` et la délégation Magento vers le formulaire Hyvä natif, prérempli. Ce formulaire possède le mécanisme reCAPTCHA natif ; **il n’est pas activé en local**, faute de configuration du type et de clés validées pour le domaine final. Aucun CAPTCHA factice n’a été ajouté. Activation et contrôle serveur : 8a.

### Styleguide, recette et limites

`/styleguide#sg-checkout` montre les deux vraies cartes de livraison via `ViewModel/ConfirmationExamples` : commandes non persistées, même ViewModel et même gabarit que le succès réel. L’état d’échec est également présenté. Le tunnel Knockout se recettera toujours dans sa vraie page Luma avec un panier.

Recette détaillée et captures : `docs/recettes/lot-6b.md`, dossier `docs/recettes/lot-6b/`. Livraison et paiement contrôlés à **1440 / 390 px**, relais sélectionné au clavier et au clic, retrait avec cadeau, erreurs de champs/créneau/CGV, totaux 20 € en retrait et 24,90 € en relais. Deux commandes locales invitées `000000021` et `000000022`, toutes deux **annulées dans l’admin** ; aucune réservation de créneau restante. Succès réel retrait aux deux tailles, gabarit d’échec natif aux deux tailles. Succès relais alimenté par le vrai ViewModel dans le styleguide et tests, sans paiement en ligne complet. Compte et panier restent Hyvä, sans RequireJS / Knockout.

Administration ouverte dans la session fournie par Pierre : Theme Fallback, accord et options CGV, newsletter et abonnés, reCAPTCHA Storefront, sections Madame Aiguille, transporteurs, moyens de paiement, retrait créé puis annulé, facture existante. La grille d’avoirs est vide ; facture / avoir avec emballage restent en recette 8a. Paiement complet, annulation Mollie avec restauration, emails reçus, reCAPTCHA actif et connexion / session expirée sont consignés au §23, sans être déclarés validés.

Validation : **106 tests unitaires, 282 assertions**, aucune erreur PHPCS Magento2 sur les 18 fichiers PHP/PHTML touchés (avertissements de docblocks et de longueur, dont le styleguide existant). `setup:upgrade --keep-generated`, `setup:di:compile`, déploiement statique du thème et build Hyvä réussis. CSS Hyvä servi et compilé **identiques : 205 564 octets**. CSS Luma générés et servis également identiques : mobile **748 490 octets**, ordinateur **158 330 octets**.

### Builds, cache et provisionnement

- **Deux builds séparés** : Tailwind pour `default`, `setup:static-content:deploy -f --theme MadameAiguille/checkout fr_FR` pour Luma. Annoncer les commandes Magento avant exécution. Pierre a autorisé leur génération de fichiers dans `pub/static` ; cela n’autorise aucune édition manuelle de ces fichiers.
- Un déploiement statique réussi peut conserver un ancien CSS. Pour repartir proprement, utiliser le service Magento `DeployStaticFile::deleteFile("frontend/MadameAiguille/checkout")` et le filesystem Magento pour son cache `var/view_preprocessed/pub/static/frontend/MadameAiguille/checkout`, puis régénérer. Contrôler la taille ou le contenu effectivement servi et le numéro de version des URLs.
- Après modification de layout/traduction : nettoyer `layout block_html translate`, puis une URL de recette différente si le HTML reste ancien. Le dictionnaire propre au thème évite de dépendre du paquet français pour ses clés ; les chaînes composées doivent utiliser la clé source exacte (ex. `%1: Line %2`).
- Les WOFF2 Sentient ne sont **pas** distribués dans Git. Provisionner les originaux Fontshare dans les deux thèmes avant compilation (README). La branche séparée `traduction-fr` n’a pas été intégrée implicitement.
- Dans la recette navigateur, une simple affectation de champ peut ne pas déclencher les événements Knockout : saisir au clavier et quitter le champ avant validation. Les tests utilisent des données fictives et annulent les commandes dans l’administration.

## 28. Langue française

Fait le 09/10/2026 sur la branche `traduction-fr`, à la demande de Pierre, après le lot 6b.

### D'où viennent les traductions

Le paquet officiel `magento/language-fr_fr` (100.4.1) installé avec Magento est **vide** : il ne contient que `language.xml` et `registration.php`. La boutique s'appuie donc sur ces dictionnaires :

| Source | Contenu | Où modifier |
|---|---|---|
| `community-engineering/language-fr_fr` (0.0.64, OSL-3.0 / AFL-3.0) | Paquet du programme de traduction communautaire de Magento, généré depuis Crowdin : **11 168 chaînes** de tous les modules, vitrine et administration | Jamais dans `vendor/` : surcharger la clé dans un dictionnaire ci-dessous |
| `app/design/frontend/MadameAiguille/default/i18n/fr_FR.csv` | Vitrine Hyvä : textes des lots précédents, plus ~160 clés propres à Hyvä absentes du paquet (libellés d'accessibilité de l'en-tête, messages de validation des formulaires, pagination, filtres, recherche, compte), et les sujets d'e-mails à apostrophe | Ici pour tout texte de la vitrine **et pour les chaînes rendues côté serveur dans le tunnel** (voir ci-dessous) |
| `app/design/frontend/MadameAiguille/checkout/i18n/fr_FR.csv` | Tunnel Luma : chaînes **JavaScript et Knockout** (`js-translation.json` du thème) | Ici pour les gabarits Knockout du tunnel |
| `app/code/MadameAiguille/*/i18n/fr_FR.csv` | Chaînes des modules maison, valables aussi dans l'administration | Avec le module concerné |

Ordre de priorité : module < paquet de langue < thème. Une clé du thème l'emporte toujours sur le paquet ; une clé de module ne l'emporte **pas** sur le paquet (en admin, « Gift wrapping » sort donc « Emballage-cadeau », traduction du paquet).

### Pièges

- **Chaînes PHP du tunnel** (libellés construits côté serveur, comme `"%1: Line %2"` des lignes d'adresse dans `AttributeMerger`) : la traduction est chargée avec le thème Hyvä **avant** que le fallback ne bascule la page vers le thème `checkout`. Elles se traduisent donc dans le dictionnaire du thème `default`, pas dans celui du tunnel.
- **Sujets d'e-mails** : `{{trans}}` échappe le texte ; une traduction avec apostrophe droite donne `&#039;` dans l'objet du message. Les sujets concernés sont retraduits avec l'apostrophe typographique `’`, comme le reste du site. Le corps des e-mails n'est pas touché par ce défaut.
- **Langue de l'administration** : elle se règle **par utilisateur** (*Paramètres du compte › Langue de l'interface*). Le compte `admin` local est en `en_US`.
- Les dictionnaires JS se régénèrent en supprimant `pub/static/frontend/*/*/fr_FR/js-translation.json` et `pub/static/deployed_version.txt`, puis `mage-translation-storage` dans le `localStorage` (§26).

### Recette du 09/10/2026

- Vitrine : texte visible et attributs `aria-label`, `placeholder`, `title` relevés sur 13 pages (accueil, catégorie, fiche, recherche avec et sans résultat, contact, connexion, création de compte, mot de passe oublié, 404, panier, pages CMS) : tout est en français, y compris l'en-tête (« Ouvrir le menu », « Ouvrir ou fermer le mini-panier »).
- Relevé statique : plus aucune clé anglaise sans traduction dans les gabarits de `MadameAiguille/default`, `hyva-themes/magento2-default-theme`, `magento2-theme-module`, `magento2-email-module`, `magento2-mollie-theme-bundle` et `mollie/magento2-hyva-compatibility`.
- Tunnel réel, étapes Livraison (retrait) et Paiement : tout en français, « Adresse : ligne 1 / 2 » compris.
- E-mails rendus sans envoi, commande de test `000000018` : confirmation (cliente et invitée), facture, expédition, mise à jour, avoir, bienvenue, mot de passe oublié et modifié, changement d'e-mail, newsletter — corps et sujets en français.
- Confirmations et compte : états réels du `/styleguide` en français.
- Administration (par script, faute de session) : ventes, commandes, facture, expédition, avoir, produits, contenu, configuration, nos réglages ; « Shipping & Handling Information » ajouté au dictionnaire de `MadameAiguille_Checkout` (« Informations de livraison »).

### Limites

- Le paquet communautaire évolue : une mise à jour peut changer quelques formulations ; nos clés de thème restent prioritaires.
- Les écrans d'administration n'ont pas été vus par l'agent ; contrôle visuel par Pierre après passage de son compte en français.

## 29. Serveur, déploiement et contrôle d'environnement (lot 8a)

Mis en place les 09 et 10/10/2026 sur `lot-8a-preproduction`. La boutique s'installe **directement sur le domaine principal** `madame-aiguille.fr`, non indexée tant que les ventes ne sont pas ouvertes, sur le VPS OVH qui héberge d'autres sites de Pierre. Journal détaillé : `docs/recettes/lot-8a.md`.

### Pile du serveur

Ubuntu 26.04 LTS, alignée sur le poste de développement : PHP 8.5.4, MariaDB 12.3.3, OpenSearch 3.9, Valkey 9.0.4, RabbitMQ 4.3, nginx 1.28. Varnish 7.7, version de référence de Magento 2.4.9 (le poste a 9.1). Tout est provisionné par les scripts versionnés de `deploy/serveur/`, rejouables et sans secret ; leur mode d'emploi et leur état sont dans `deploy/serveur/README.md`.

| Service | Rôle | Écoute |
|---|---|---|
| nginx 443 | TLS (certificat unique `madame-aiguille.fr` + `www`), `www` et HTTP en 301, `X-Robots-Tag: noindex, nofollow` avant l'ouverture | public |
| Varnish | cache de pages, VCL généré par Magento | 127.0.0.1:6081 |
| nginx 8080 | backend de Varnish, `nginx.conf.sample` de la version en ligne | 127.0.0.1 |
| PHP-FPM, pool `madame-aiguille` | utilisateur système dédié sans connexion, socket lu par nginx | socket |
| Valkey | 6379 : sites Laravel (ex-Redis) ; 6380 : cache Magento (LRU) ; 6381 : sessions (persistées) | 127.0.0.1 |
| OpenSearch | recherche, nœud unique, 2 Go | 127.0.0.1:9200 |
| MariaDB, RabbitMQ | base `madame_aiguille`, vhost `/madame-aiguille` | 127.0.0.1 |

**Isolation** : PHP-FPM, le cron et toutes les commandes Magento tournent sous `madame-aiguille`. Le code appartient à Pierre et n'est que lisible par ce groupe ; `env.php` (0600) et `var/` (0700) ne sont lisibles que par lui. Une faille d'un autre site (`www-data`) n'atteint pas les secrets de la boutique, et une faille de Magento n'atteint pas le compte de Pierre, qui a sudo.

### Construire et publier

`.github/workflows/deploiement.yml`, construit **sans base de données** : `config.php` porte les sites, boutiques et thèmes (`app:config:dump scopes themes`, accord de Pierre). Conséquence : la structure *Magasins › Tous les magasins* ne se modifie plus dans l'administration, sans gêne pour une boutique à vue unique.

1. Composer (`COMPOSER_AUTH` : accès Hyvä et Magento), fontes originales téléchargées chez Fontshare et vérifiées par SHA-256 (`deploy/fontes.sh`, `deploy/fontes.sha256` : jamais versionnées, licence ITF), Tailwind, compilation DI, statiques `fr_FR` (vitrine et tunnel) et `fr_FR` + `en_US` (administration).
2. Sur demande (*Actions › Déploiement › Run workflow*, case « Publier ») : rsync en liens physiques vers `releases/<date>-<commit>/`, puis `deploy/bascule.sh`.
3. La bascule branche `env.php`, `var/` et `pub/media/` partagés ; **seulement si la base ou la configuration importée doivent évoluer**, maintenance, dump de la base dans `shared/sauvegardes/` et `setup:upgrade --keep-generated` ; puis lien `current` atomique, rechargement de PHP-FPM (OPcache figé entre deux versions) et nginx, caches vidés, `env:check`. Cinq versions conservées.

Retour arrière : `ssh <vps> 'bash -s -- <version>' < deploy/bascule.sh`, plus la restauration du dump si la version récente avait modifié la base. Le workflow ne se lance qu'à la main ; sans la case « Publier », il construit seulement, ce qui valide une branche sans rien mettre en ligne. Actions GitHub en versions Node 24 (`checkout@v7`, `cache@v6`, `setup-node@v7`).

Secrets du dépôt GitHub (jamais dans le code) : `COMPOSER_AUTH`, `SSH_PRIVATE_KEY` (clé personnelle de Pierre, à sa demande), `SSH_KNOWN_HOSTS` (clés d'hôte vérifiées), `DEPLOY_HOST`, `DEPLOY_PORT`, `DEPLOY_USER`.

### Installation neuve

`deploy/serveur/80-installer-magento.sh <version>`, une seule fois : base et compte RabbitMQ dédiés, mots de passe et chemin d'administration **générés sur le serveur** et confinés à `env.php`, `setup:install` (Valkey, OpenSearch, RabbitMQ, Varnish), mode production, robots `NOINDEX,NOFOLLOW`, webhooks Mollie actifs en mode test, double authentification par Google Authenticator, indexeurs « planifiés », grille Mondial Relay importée, VCL, cron, mise en ligne. Aucun compte administrateur n'est créé par le script : Pierre le crée lui-même.

Deux patches reproduisent la boutique locale sans jamais réécrire une valeur saisie : `Theme/Setup/Patch/Data/ConfigureStoreIdentity.php` (langue, fuseau, devise, informations de la boutique, expéditeurs `contact@madame-aiguille.fr`, thème Hyvä de la vue, logo, copyright avec mention de TVA, réseaux, URLs propres, télémétrie Adobe coupée) et `Checkout/Setup/Patch/Data/KeepProvisionalMollieMethods.php` (les sept moyens réactivés par Pierre, provisoires). Le téléphone de la boutique n'est pas versionné (dépôt public) : il se saisit dans l'administration.

### Contrôle d'environnement

```bash
bin/magento madameaiguille:env:check                      # poste local
bin/magento madameaiguille:env:check --serveur --noindex  # serveur avant l'ouverture des ventes
```

Lit la configuration **effective** (ScopeConfig, `env.php`, modules), sans afficher de secret : mode, URLs HTTPS, indexation, caches et sessions, OpenSearch, cron, double authentification, SMTP et expéditeurs, clé du mode Mollie et webhooks, reCAPTCHA des quatre formulaires, code enseigne, franco, poids, fontes des deux thèmes. Sort en erreur si un réglage rend la boutique inutilisable. Fichiers : `Theme/Console/Command/CheckEnvironment.php`, `Theme/Model/Environment/*`, tests `Theme/Test/Unit/Model/Environment/`. Le contrôleur est injecté par proxy : toutes les commandes sont construites à chaque `bin/magento`, `setup:install` compris.

### Pièges rencontrés

- **Mollie 3.1.3 ne traite un paiement que par le webhook** : le retour navigateur affiche le succès sans facturer ni changer le statut. En local (webhook coupé), une commande payée reste « En attente de paiement ».
- **La mise à niveau Ubuntu 25.10 → 26.04 retire PHP 8.4 sans installer 8.5** : les sites PHP tombent en 502 jusqu'à l'installation de PHP 8.5 et la bascule des sockets nginx. Elle désactive aussi les dépôts externes (`*.list.disabled`), remis au format deb822.
- **Elasticsearch 9 dimensionne son tas sur la moitié de la RAM** par défaut ; Magento 2.4.9 embarque un client Elasticsearch 8 : OpenSearch 3 retenu.
- **OpenSearch 3 à l'installation** exige un mot de passe administrateur et dépose des certificats de démonstration aux clés publiques : mot de passe aléatoire non conservé, sécurité désactivée en écoute locale, certificats supprimés.
- **`setup:install` n'a pas `--keep-generated`** : il peut vider le code généré par le build ; le script d'installation le détecte et le reconstruit pour cette seule fois.
- **Magento vérifie que `app/etc` est inscriptible** avant d'écrire `env.php` : la bascule l'ouvre au groupe pour chaque version.
- Le garde-fou de l'agent refuse les modifications des ressources partagées du VPS (nginx et PHP des autres sites, certificats) : ces étapes sont livrées en scripts relus et lancés par Pierre.

### Installation du 10/10/2026 et réglages faits à la main

- Version en ligne `20261010-073014-090e1ba` (build de `090e1ba`), installée par le script 80 ; compte administrateur créé par Pierre (`admin:user:create`), double authentification associée.
- **SMTP** : réglages non secrets posés par `config:set` (Brevo, 587, LOGIN, TLS, identifiant) ; le mot de passe, champ non « sensible » pour Magento, a été saisi par Pierre en ligne de commande, saisie masquée (`read -rsp` puis `config:set`, qui le chiffre). L'administration était inaccessible avant : la double authentification attend un email.
- **Mollie** : clés et profil saisis par Pierre dans l'administration ; mode test, webhooks actifs, point d'entrée `POST /mollie/checkout/webhook/` joignable (200).
- Restent : clés reCAPTCHA, catalogue réel, téléphone de la boutique (§23).

### Accès à l'administration et alerte Chrome

Le 10/10/2026, Chrome a affiché « Site dangereux » sur l'administration : sa protection en temps réel a pris pour de l'hameçonnage le formulaire de connexion standard de Magento (titre « Admin Magento », logo Adobe, champ mot de passe) servi sur un domaine neuf à la vitrine encore vide. Audit en lecture seule : aucune injection dans la configuration ni le CMS, aucun PHP dans les médias, un seul administrateur, aucun fichier modifié hors `setup:install` ; l'intégration « Magento Analytics user » vient du cron natif. Le rapport de transparence Safe Browsing n'avait aucune donnée sur le domaine.

Réponse : `deploy/serveur/90-acces-admin.sh` génère `/etc/nginx/snippets/madame-aiguille-admin.conf` (hors dépôt, contient le chemin de l'administration) inclus par la configuration versionnée : `satisfy any` — une IP de la liste passe, toute autre doit fournir l'identifiant et le mot de passe HTTP (hachage SHA-512 dans `/etc/nginx/madame-aiguille-admin.htpasswd`, choisi par Pierre). Vitrine, tunnel et webhook restent publics ; le robot de Google reçoit un 401 et ne voit plus le formulaire. Signalement envoyé à Google Safe Browsing ; propriété Search Console créée (aucun problème de sécurité). Ajouter l'IP de Céline en relançant le script avec les deux adresses.

### Recette du 10/10/2026

Écrans de production capturés à 1440 et 390 px (`docs/recettes/lot-8a/`) : accueil, contact, CGV (brouillon visible), panier vide, connexion, 404 ; dimensions lues dans le DOM (390 : aucun défilement horizontal ; 1440 : 1425 utiles hors barre de défilement), quatre fontes chargées, aucune ressource en erreur. CSS servies identiques aux construites. Redirections HTTP et `www` en 301, `X-Robots-Tag` et robots `NOINDEX,NOFOLLOW`, Varnish MISS puis HIT et purge par Magento, cron, 14 indexeurs planifiés, `env:check --serveur --noindex` à 0 erreur, administration à 401 hors liste. Les cinq autres sites du VPS à 200 après chaque étape.

Non recetté faute de catalogue réel et de clés reCAPTCHA : paiement et retour Mollie, emails reçus, newsletter, reCAPTCHA, pièces de vente avec emballage, tunnel et compte à 1440 / 390 sur le serveur, sections d'administration (inaccessibles à l'agent : liste d'IP et double authentification). Reporté au début du lot 8 (§23).
