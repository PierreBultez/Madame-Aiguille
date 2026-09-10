# Documentation du thème Madame Aiguille

Document interne — v2.2 (10/09/2026), lots 6a, 3, 4 et 7 livrés ; identité visuelle officielle intégrée. Les lots 5 et 6b restent en attente du compte marchand Mollie et du brief livraison. **À compléter à chaque lot** (une section par écran livré).

Les tableaux « où modifier quoi » distinguent ce qui se règle **dans l'admin** (Céline, sans code) de ce qui se change **dans le code** (Pierre).

Ce document décrit ce qui a été construit, où se trouve chaque chose, et **où modifier quoi** — dans le code ou dans le back-office Magento. **Céline peut aller directement au §24**, qui récapitule tout ce qui se règle sans toucher au code. Il complète `guide-bonnes-pratiques-hyva.md` (conventions de code) et `charte-graphique.md` / l'artboard *Design System* (décisions visuelles).

## 1. Vue d'ensemble

| Élément | Emplacement | Rôle |
|---|---|---|
| Thème enfant | `shop/app/design/frontend/MadameAiguille/default/` | Tout le visuel : CSS, templates, layouts, fontes, images |
| Module | `shop/app/code/MadameAiguille/Theme/` | Tout le PHP : ViewModels, routes, configuration admin, data patches |
| Documentation | `docs/` | Cahier des charges, spécifications, charte, maquettes, ce document |

Parent : `Hyva/default` 1.5.2 (`vendor/hyva-themes/magento2-default-theme`). **On ne modifie jamais `vendor/`.** Un template du parent se surcharge en le copiant au même chemin relatif dans le thème enfant (`Magento_Theme/templates/html/header.phtml`, par exemple).

**Règle d'architecture visuelle** : toute surcharge de rendu frontend vit dans le thème enfant, même lorsqu'un module dédié porte la logique métier. Le module expose ses blocs, ViewModels, contrôleurs, validations et configurations ; le thème enfant surcharge ses layouts, `.phtml`, CSS, assets et templates JavaScript. Exemple : le traitement sécurisé de Contact reste dans `app/code/MadameAiguille/Contact`, mais son formulaire est rendu par `app/design/frontend/MadameAiguille/default/MadameAiguille_Contact/templates/form.phtml`. Le checkout Luma du fallback suivra la même règle dans son propre thème enfant Luma au lot 6b.

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
| **Réseaux sociaux** | Admin › *Stores › Configuration › Général › Madame Aiguille › Réseaux sociaux* | Instagram, Facebook, Pinterest — vide = masqué. Ajouter un réseau : `ViewModel/SocialLinks.php` (constante `NETWORKS`) + `system.xml` + icône Lucide (ou SVG dans `Hyva_Theme/web/svg/lucide/`) |
| Colonne **Boutique** | automatique : catégories de niveau 1 | `footer/shop.phtml` |
| Colonne **Informations** | `default.xml` → bloc `footer.info`, argument `links` (`label` + `path`) | les `path` sont des URL relatives : identifiant de page CMS, `contact`, etc. |
| Colonne **Paiement & livraison** | `default.xml` → bloc `footer.services`, arguments `payment_methods` / `shipping_methods` | mentions en mots, à aligner sur les modes réellement activés (lot 5) |
| **Copyright** | Admin › *Contenu › Design › Configuration › Footer › Copyright* (`design/footer/copyright`) | mention TVA à ajouter quand le statut fiscal sera connu |
| Gabarit de colonne (titre + liste, accordéon mobile) | `footer/column.phtml` | réutilisable via `fetchView` |

## 7. Pages et blocs CMS

Créés par data patch (`Setup/Patch/Data/`), **une seule fois** ; ensuite ils vivent en base et s'éditent dans l'admin (*Contenu › Pages / Blocs*). Rejouer `setup:upgrade` ne les écrase jamais.

| Identifiant | Type | Rôle |
|---|---|---|
| `a-propos` | page | L'histoire de Madame Aiguille |
| `livraison-retours` | page | Livraison et retours |
| `cgv` | page | Conditions générales de vente |
| `mentions-legales` | page | Mentions légales |
| `confidentialite` | page | Politique de confidentialité |
| `nos-tissus` | page | galerie de motifs référencés pour préparer une demande par contact |
| `header_announcement` | bloc | bandeau du header |
| `product_reassurance` | bloc | quatre arguments sous le bouton d'achat de la fiche produit (expédition, paiement, transporteurs, emballage). À aligner sur les modes réels au lot 5 |

### Accueil — lot 3, étape 3

L'accueil suit cet ordre : hero, Nouveautés, Incontournables, nouvelles de l'atelier, histoire, arguments, galerie Instagram, newsletter. Les blocs créés par `CreateHomeBlocks` ne sont jamais réécrits après leur première installation : Céline conserve donc toutes ses modifications.

| Zone | Modification dans l'admin | Structure dans le code |
|---|---|---|
| Hero | *Contenu › Blocs* › `home_hero` : titre, texte, image et lien de catégorie | `Magento_Theme/layout/cms_index_index.xml`, styles `.home-hero` ; image plafonnée à 240 px sur mobile |
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
| Thème du checkout | même écran, *Theme full path* | `hyva_theme_fallback/general/theme_full_path = frontend/Magento/luma` |
| Routes concernées | même écran, *Apply fallback to requests containing* | `hyva_theme_fallback/general/list_part_of_url` : conserver les valeurs système décrites ci-dessous |
| Commande invité | Admin › *Stores › Configuration › Sales › Checkout › Checkout Options › Allow Guest Checkout* | `checkout/options/guest_checkout = 1`, valeur effective vérifiée pour la vue `default` |
| Valeurs structurelles | `vendor/hyva-themes/magento2-luma-checkout/src/etc/config.xml` (lecture seule) | Les valeurs natives suffisent ; aucune surcharge en base ajoutée. Une future valeur propre au projet ira dans `MadameAiguille_Theme/etc/config.xml` |

Les routes système de la version 1.1.7 sont `/checkout/index`, `paypal/express/review`, `paypal/express/saveShippingMethod`, `paypal/transparent/redirect`, `paypal/transparent/response` et `customer/ajax/login`. Les routes PayPal sont fournies par le paquet : leur présence **n'active pas un paiement**. Le matching porte notamment sur la route Magento résolue : `/checkout/` correspond à `checkout/index/index`. Ne pas élargir la règle à tout `checkout`, sinon le panier et les pages de résultat basculeraient aussi vers Luma. Après modification : `cache:flush`.

### Où préparer les surcharges du lot 6b

Créer au lot 6b un **second thème enfant de `Magento/luma`**, par exemple `shop/app/design/frontend/MadameAiguille/checkout/`, puis configurer `frontend/MadameAiguille/checkout` comme thème fallback. Ce répertoire n'est pas encore créé. Les fichiers du thème Hyvä `MadameAiguille/default` ne sont pas hérités par Luma.

| Besoin | Emplacement futur dans le thème Luma enfant |
|---|---|
| Déclaration | `theme.xml` avec parent `Magento/luma`, `registration.php` |
| Structure, arguments `jsLayout`, logo du tunnel | `Magento_Checkout/layout/checkout_index_index.xml` ; fusion ciblée avec le layout natif |
| Templates Knockout | `Magento_Checkout/web/template/` en conservant le chemin relatif du template natif : `shipping.html`, `payment.html`, `summary/...` |
| Champs UI partagés, uniquement si nécessaire | `Magento_Ui/web/templates/...` (attention au pluriel `templates`) |
| Variables et styles LESS | `web/css/source/_theme.less`, `web/css/source/_extend.less` ; extension ciblée possible dans `Magento_Checkout/web/css/source/_extend.less` |
| Fontes et traductions | `web/fonts/` avec les WOFF2 fournis tels quels et licences ; `i18n/fr_FR.csv` propre au thème Luma |

Références en lecture seule : `vendor/magento/module-checkout/view/frontend/web/template/`, `vendor/magento/theme-frontend-luma/Magento_Checkout/web/css/source/`, et les README de `vendor/hyva-themes/magento2-{luma-checkout,theme-fallback}/`. Les templates UI génériques viennent de `vendor/magento/module-ui/view/base/web/templates/`. Luma compile du **LESS**, pas le Tailwind du thème Hyvä. Les styles générés vivent dans `pub/static/frontend/<Vendor>/<theme>/<locale>/css/` et ne se modifient pas directement. En production, leur génération passe par `setup:static-content:deploy`.

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
vendor/bin/phpunit -c dev/tests/unit/phpunit.xml.dist app/code/MadameAiguille/Theme/Test/Unit   # tests du module
```

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
| 7 — Styleguide et documentation | états du compte sur `/styleguide` via `ViewModel\Styleguide\OrderStates`, sections §21 et §22, mémo Céline complété | `8f33ac5` → documentation |

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
| **Seuil de livraison offerte** | Admin › *Stores › Configuration › Général › Madame Aiguille › Panier › Seuil de livraison offerte* | Défaut **49 €** (décision de Pierre du 10/09/2026, montant des maquettes). **0 masque complètement la barre.** Ce seuil est un affichage : il devra correspondre à la règle de livraison gratuite réellement configurée au lot 5 |
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

**Seules les méthodes réellement retournées par Magento sont affichées**, groupées par transporteur. À ce jour une seule méthode est active (Flat Rate, 5 € par article) : c'est elle qui apparaît. Aucun nom de transporteur ni aucun tarif n'est écrit en dur — la boucle par transporteur accueillera Colissimo, Mondial Relay et Chronopost au lot 5 sans refonte. Quand aucune méthode n'est retournée, un message le dit au lieu de laisser la zone vide.

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

- **Email de virement** (RIB, référence à rappeler, délai d'annulation) : la structure existe, les valeurs viendront de la configuration des moyens de paiement, au **lot 5**.
- **Modalités de retrait** : le gabarit est prêt, le texte exact attend le cadrage avec Céline.
- **Délivrabilité et SMTP** (SPF, DKIM, DMARC) : dépendent du domaine, **lot 8**. Tant que le SMTP de production n'est pas en place, un email peut partir sans arriver.
- **Recette de bout en bout** d'une commande réellement payée qui parcourt tous les statuts : elle attend Mollie, **lot 5**.

## 23. Après les lots 4 et 7 : ce qui reste à faire

Les lots 3, 4 et 7 sont fonctionnellement terminés. Les éléments suivants demandent encore du contenu réel, une configuration de production ou appartiennent aux lots suivants.

> **Réorganisation du 10/09/2026.** Les lots 5 (livraison et paiements) et 6b (habillage du checkout) attendent la création du compte marchand **Mollie** — prestataire de paiement retenu, déjà installé et activé dans le projet — et un brief avec Céline sur les modes de livraison. Le **lot 7** a été livré à leur place ; la suite est donc **5 → 6b → 8**. Toutes les lignes marquées « lot 5 » ci-dessous restent ouvertes plus longtemps que prévu : ce sont, pour la plupart, des **promesses affichées à la cliente qui doivent devenir vraies avant l'ouverture des ventes**.

| Sujet | Action attendue | Responsable / échéance |
|---|---|---|
| Histoire et présentation | Remplacer les textes génériques de `a-propos`, `home_story` et `contact_help` depuis *Contenu › Pages / Blocs* | Céline, avant publication |
| Marchés, congés et annonces | Renseigner `home_actualities` et `contact_locations` ; ne publier que des dates confirmées | Céline, au fil de l'activité |
| Pages juridiques | Rédiger `livraison-retours`, `cgv`, `mentions-legales` et `confidentialite` avec les coordonnées, délais et règles réels | Céline avec conseil adapté, avant ouverture des ventes |
| Tissus et photos | Remplacer les références et visuels génériques de `nos-tissus`, puis fournir les photos catalogue finales au ratio 4:5 | Céline, avant mise en production |
| CTA « Voir toutes les nouveautés » | Choisir sa destination définitive ; l'accueil ne possède pas de catégorie globale « Boutique » | Pierre / Céline, avant publication |
| Contact | Renseigner l'identité d'expéditeur et le destinataire du module Contact, activer les clés reCAPTCHA, confirmer ou ajuster la conservation par défaut de 30 jours, puis faire un essai d'email avec JPG et PNG | Lot 8 / recette de production |
| Email et domaine | Configurer SMTP ainsi que SPF, DKIM et DMARC ; recetter tous les emails sur le domaine final. **Sans SMTP de production, un email peut partir sans arriver** | Lot 8 |
| Alerte ciblée « Me prévenir » | Décider si une alerte de retour d'une création précise est utile. L'inscription newsletter actuelle n'est pas une alerte de stock | Décision produit ultérieure |
| **Franco de port** | Le seuil de 49 € est **affiché** mais aucune règle Magento n'offre encore la livraison : au lot 5, activer la livraison gratuite au même montant, ou ajuster le seuil dans *Madame Aiguille › Panier* | Pierre, lot 5 — **avant ouverture des ventes** |
| **Pays par défaut de l'estimateur** | `general/country/default` et `shipping/origin/country_id` valent encore « États-Unis » : l'estimateur propose ce pays à l'ouverture. À passer sur la France | Pierre, lot 5 |
| **Tarif d'expédition provisoire** | Seul Flat Rate est actif (5 € par article) ; c'est ce que l'estimateur affiche. À remplacer par les table rates au poids des trois transporteurs | Pierre, lot 5 |
| **Produit désactivé pendant qu'il est au panier** | Magento retire la ligne sans message. Décider si une information explicite est souhaitée — elle demanderait un observateur dédié, hors périmètre du lot 4 | Décision Pierre |
| **Rassurance et code promo du panier** | Renseigner `cart_reassurance` avec les moyens de paiement et le délai réellement tenus ; activer le champ code promo le jour où une règle de panier existe | Céline, avant publication |
| Livraison et paiement | Choisir les prestataires et tarifs, puis aligner `product_reassurance`, le footer, la page Livraison et la mention sous le prix sur les modes réellement activés | Lot 5 |
| **Parcours réel des statuts** | Recetter une commande réellement payée qui parcourt les six statuts, du paiement à la livraison. Les statuts, les phrases et la frise sont livrés, mais aucun paiement ne les déclenche encore | Pierre, lot 5 |
| **Email de virement** | Structure prête, valeurs manquantes : RIB, référence à rappeler et délai d'annulation. À écrire avec les coordonnées bancaires réelles | Pierre / Céline, lot 5 |
| **Modalités de retrait en main propre** | Confirmer que la remise en main propre est retenue, puis fixer le texte type : lieu, créneaux, pièce à présenter. Le gabarit reprend le commentaire de statut visible saisi dans la commande | Céline, avant ouverture des ventes |
| **Textes des emails transactionnels** | Relire et personnaliser les gabarits natifs (confirmation, expédition, bienvenue, réinitialisation) dans *Marketing › Modèles d'e-mail* ; l'enveloppe de marque s'applique automatiquement | Céline, avant publication |
| **Expéditeur des emails** | Renseigner les identités d'expéditeur (*Boutiques › Configuration › Général › Contacts de la boutique*) : elles apparaissent dans chaque email envoyé | Céline / Pierre, lot 8 |
| Production | Vérifier le cron Magento, la purge des pièces jointes et des badges Nouveauté, puis étudier le WebP et les performances | Lot 8 |
| Logo et favicon | Rejouer le contrôle visuel à 1440 et 390 px et vérifier le favicon à 16/32 px. Le contrôle automatisé du 10/09/2026 a été interrompu par la limite d'usage de l'outil navigateur ; les formats, ratios, chemins et caches Magento ont été contrôlés | Prochaine recette locale |

Le lot suivant est le **lot 5 — Livraison et paiements**, qui conditionne la véracité du franco affiché, du pays par défaut, des tarifs de l'estimateur et du parcours des statuts. Il attend le compte marchand Mollie et le brief livraison avec Céline. Viennent ensuite le lot 6b (habillage du checkout) puis le lot 8 (back-office, exploitation, mise en production).

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
| Pages `a-propos`, `nos-tissus`, `livraison-retours`, `cgv`, `mentions-legales`, `confidentialite` | Pages éditoriales et juridiques | §7 |

### Réglages — *Boutiques › Configuration › Général › Madame Aiguille*

| Réglage | Effet | Détail |
|---|---|---|
| Réseaux sociaux | Liens affichés dans le pied de page ; un champ vide masque le réseau | §3 |
| Catalogue › Seuil de rareté | À partir de combien d'exemplaires restants la mention « Plus que N exemplaires » s'affiche. `0` désactive | §8 |
| Catalogue › Mention sous le prix | Petite ligne sous le prix en fiche produit **et** sous le total du panier. Vide = rien | §8, §20 |
| Panier › Seuil de livraison offerte | Montant de la barre « Plus que X € pour la livraison offerte ». `0` masque la barre. **Doit correspondre à une vraie règle de livraison gratuite** | §20 |
| Panier › Afficher le champ code promo | Masqué tant qu'aucun code n'existe | §20 |
| Formulaire de contact › Conservation des pièces jointes | Durée avant suppression automatique des photos reçues (30 jours par défaut) | §18 |

### Emails — *Boutiques › Configuration › Ventes › Emails de vente*

| Réglage | Effet | Détail |
|---|---|---|
| Paiement reçu › Activé / Expéditeur / Gabarit | Notification envoyée automatiquement quand une commande passe au statut « Paiement reçu » | §22 |
| Prête pour retrait › Activé / Expéditeur / Gabarit | Notification envoyée quand une commande passe au statut « Prête pour retrait ». **Le corps du message reprend le commentaire de statut visible saisi dans la commande** : c'est là qu'on écrit le lieu, la date et l'heure du retrait | §22 |
| Confirmation, expédition, avoir… | Réglages natifs de Magento, inchangés | §22 |

Les **textes** des emails se modifient dans *Marketing › Communications › Modèles d'e-mail* : dupliquer le gabarit voulu, le modifier, puis le sélectionner dans le réglage correspondant. L'en-tête au logo et le pied de page de la marque s'appliquent automatiquement — inutile de les recopier dans chaque gabarit.

### Commandes — *Ventes › Commandes*

| Quoi | Où | Détail |
|---|---|---|
| Faire avancer une commande | Ouvrir la commande, puis **Commentaires sur l'historique** : choisir le statut et enregistrer | §21 |
| Les six statuts | *En attente de paiement*, *Paiement reçu*, *En préparation*, *Expédiée*, *Prête pour retrait*, *Livrée*. La cliente voit le même libellé, accompagné d'une phrase qui explique où en est sa commande | §21 |
| Prévenir la cliente | Cocher **Visible par le client** avant d'enregistrer un commentaire. Pour un retrait, y écrire les modalités : elles partent dans l'email | §21, §22 |
| Renommer un statut | *Ventes › Statuts de commande* : le libellé change côté cliente **et** côté back-office. Ne pas supprimer un statut ni changer son code | §21 |

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

### Ce qu'il ne faut pas faire

- **Ne pas décocher « Gérer le stock »** sur une création : les badges et le plafond de quantité du panier en dépendent.
- **Ne pas supprimer une catégorie contenant des produits** sans les avoir déplacés d'abord.
- **Ne pas annoncer un délai ou un tarif** dans un bloc CMS tant qu'il n'est pas confirmé : les textes actuels sont des exemples à remplacer.

