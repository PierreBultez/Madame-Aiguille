# Documentation du thème Madame Aiguille

Document interne — v1.0 (05/09/2026), état en fin de lot 1. **À compléter à chaque lot** (une section par écran livré).

Ce document décrit ce qui a été construit, où se trouve chaque chose, et **où modifier quoi** — dans le code ou dans le back-office Magento. Il complète `guide-bonnes-pratiques-hyva.md` (conventions de code) et `charte-graphique.md` / l'artboard *Design System* (décisions visuelles).

## 1. Vue d'ensemble

| Élément | Emplacement | Rôle |
|---|---|---|
| Thème enfant | `shop/app/design/frontend/MadameAiguille/default/` | Tout le visuel : CSS, templates, layouts, fontes, images |
| Module | `shop/app/code/MadameAiguille/Theme/` | Tout le PHP : ViewModels, routes, configuration admin, data patches |
| Documentation | `docs/` | Cahier des charges, spécifications, charte, maquettes, ce document |

Parent : `Hyva/default` 1.5.2 (`vendor/hyva-themes/magento2-default-theme`). **On ne modifie jamais `vendor/`.** Un template du parent se surcharge en le copiant au même chemin relatif dans le thème enfant (`Magento_Theme/templates/html/header.phtml`, par exemple).

Le thème est activé pour la vue *Default Store View* (`design/theme/theme_id = 5`, admin *Contenu › Design › Configuration*).

### Arborescence du thème

```
MadameAiguille/default/
├── theme.xml, registration.php, composer.json
├── Hyva_Theme/web/svg/lucide/          icônes ajoutées au jeu Lucide (pinterest.svg)
├── MadameAiguille_Theme/
│   ├── layout/madameaiguille_styleguide_index_index.xml
│   └── templates/styleguide.phtml       page de contrôle /styleguide
├── Magento_Theme/
│   ├── layout/default.xml               header, logo, footer (fusionné avec le parent)
│   ├── layout/default_head_blocks.xml   preload des fontes
│   └── templates/html/
│       ├── header.phtml                 header (bandeau, grille, panier)
│       ├── header/logo.phtml            logo responsive
│       ├── header/menu/desktop.phtml    navigation ≥ md
│       ├── header/menu/mobile.phtml     burger + tiroir < md
│       ├── footer.phtml                 pied de page
│       └── footer/{brand,shop,links,services,column,copyright}.phtml
└── web/
    ├── css/styles.css                   GÉNÉRÉ — jamais édité, jamais commité
    ├── fonts/                           .woff2 + licences FFL
    ├── images/logo-horizontal.png       logo 2× provisoire (PNG) — à remplacer par le SVG
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
├── etc/module.xml, etc/config.xml, etc/acl.xml
├── etc/adminhtml/system.xml             section admin « Madame Aiguille »
├── etc/frontend/routes.xml              route /styleguide
├── Controller/Index/Index.php           page de contrôle (404 en production)
├── ViewModel/SocialLinks.php            liens réseaux sociaux (footer)
└── Setup/Patch/Data/
    ├── CreateStaticPages.php            pages CMS légales
    └── CreateHeaderAnnouncementBlock.php bloc bandeau
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
| `on-blush` | `base/brand.css` | texte courant sur fond blush |

Règles de base (`base/brand.css`) : liens sans classe soulignés nude, `font-display` jamais italique, anneau de focus ivoire sur fond `bg-brand`, chiffres tabulaires sur `.price`.

## 5. Header

Template : `Magento_Theme/templates/html/header.phtml`. Layout : `Magento_Theme/layout/default.xml` (bloc `header-content`).

| Quoi | Où | Comment |
|---|---|---|
| **Bandeau d'annonce** | Admin › *Contenu › Blocs › `header_announcement`* | Modifier le texte ; vider ou désactiver le bloc pour masquer le bandeau. Vider le cache `full_page` ensuite (`bin/magento cache:clean full_page`) |
| **Logo** | `web/images/logo-horizontal.png` (provisoire) ; ou admin › *Contenu › Design › Configuration › Header › Logo* (prioritaire s'il est renseigné) | Tailles : 240 px mobile · 288 px md · 356 px lg (classes `w-60 md:w-72 lg:w-89` dans `header/logo.phtml`). Texte alternatif : config `design/header/logo_alt` |
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
| `header_announcement` | bloc | bandeau du header |

Classes Tailwind utilisables dans le CMS : celles listées dans `@source inline(...)` de `tailwind-source.css` (couleurs `bg-/text-/border-brand*`, échelle `text-*`, espacements courants, grilles, `btn`, `badge`, `scallop`, `card`, `prose`). Pour en ajouter une : la déclarer là, puis `npm run build`.

## 8. Page de contrôle `/styleguide`

Route `styleguide` (module, `etc/frontend/routes.xml`), contrôleur `Controller/Index/Index.php` — **404 en mode production**. Layout `MadameAiguille_Theme/layout/madameaiguille_styleguide_index_index.xml`, template `MadameAiguille_Theme/templates/styleguide.phtml`. Tenir la page à jour à chaque nouveau composant : c'est la référence visuelle de recette.

## 9. Commandes utiles

```bash
bin/magento cache:flush                      # après un nouveau layout, une route, une section system.xml
bin/magento cache:clean full_page block_html # après modification d'un template ou d'un bloc CMS
bin/magento setup:upgrade --keep-generated   # nouveau module, nouveau data patch
bin/magento config:set <chemin> <valeur>     # ex. design/footer/copyright "…"
bin/magento indexer:reindex                  # après création de catégories/produits par script
```

## 10. Conventions rappelées

- Escaping systématique (`$escaper->escapeHtml/Url/HtmlAttr/Js`), `__()` sur tous les textes, zéro logique métier dans les `.phtml` (→ ViewModel déclaré en layout XML).
- Aucune valeur hexadécimale ni classe arbitraire de couleur dans un template : uniquement des tokens.
- Alpine pour l'interface, jamais pour recalculer une donnée serveur.
- Un fichier de layout par handle. Surcharger un template entier seulement si le layout ne suffit pas.
- Commits atomiques en français, un par étape cohérente.

## 11. Journal des livraisons

| Lot | Contenu | Commits |
|---|---|---|
| 1 — Fondations | dépôt, thème enfant, chaîne Tailwind, fontes, tokens, composants, styleguide, module, header, footer | `62dca83` → `7af01df` |
