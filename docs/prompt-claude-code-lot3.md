Tu reprends le développement du thème **Madame Aiguille**, une boutique Magento 2 sous thème Hyvä. Deux agents ont livré le **lot 1 — fondations** et le **lot 2 — catalogue**. Tu enchaînes le **lot 6a — installation du checkout Luma fallback** (court) puis le **lot 3 — accueil, pages CMS, formulaire de contact**. Lis intégralement ce brief, puis les documents cités en §2, **avant** d'écrire la moindre ligne de code.

---

# 1. État des lieux — vérifié fin du lot 2 (10/09/2026), ne pas re-supposer

| | |
|---|---|
| Racine projet / dépôt git | `~/Documents/aiguille/` — couvre `shop/` **et** `docs/` |
| Application | `~/Documents/aiguille/shop/` |
| Magento | Open Source **2.4.9**, mode `developer`, MSI actif (`Magento_Inventory*`), OpenSearch, base `aiguille` (identifiants dans `shop/app/etc/env.php`, jamais commité) |
| Thème parent | Hyvä `magento2-default-theme` **1.5.2**, **Tailwind CSS v4** |
| Thème enfant | `shop/app/design/frontend/MadameAiguille/default/` — actif (`design/theme/theme_id = 5`) |
| Module | `shop/app/code/MadameAiguille/Theme/` — activé |
| Branches | `lot-2-catalogue` : 10 commits (`92500b5` → `438516e`) au-dessus de `lot-1-fondations`. Vérifie `git log --oneline --all` : si Pierre a fusionné dans `main`, pars de `main` ; sinon pars de `lot-2-catalogue`. Crée `lot-6a-checkout`, puis `lot-3-accueil-cms-contact` depuis celle-ci une fois 6a validé |
| Node / npm | Node 26, npm 11 (`.nvmrc` du thème : 20 minimum) |
| Machine | Ubuntu, Apache, PHP 8.5, MariaDB, Redis. Site : `http://localhost/`. Chrome headless disponible ; l'extension Chrome n'est pas connectée |
| Tests | `vendor/bin/phpunit -c dev/tests/unit/phpunit.xml.dist app/code/MadameAiguille/Theme/Test/Unit` (10 tests) |

## Ce qui existe (lots 1 et 2) — détail dans `docs/documentation-theme.md` v1.2

- Chaîne Tailwind v4 (`web/tailwind/`, `npm run build` / `watch`), fontes Britney / Sentient, tokens, composants CSS (`btn`, champs, `message`, `card`, `badge`, `scallop`, `full-bleed`, swatches texte, `pdp-price`), page de contrôle **`/styleguide`** (404 en production) à enrichir à chaque composant.
- Header (bandeau CMS `header_announcement`, logo centré PNG provisoire, navigation catégories 2 niveaux) et footer (4 colonnes, réseaux sociaux en config admin, liens Informations en layout XML).
- **Catalogue complet** : attributs `taille` (swatch texte), `serie_limitee`, `taille_serie`, `composition`, `dimensions`, `entretien` ; `weight` obligatoire en kg ; attribute set **« Création »** ; config admin *Madame Aiguille › Catalogue* (seuil de rareté, mention sous le prix) ; ViewModels `Product\LimitedSeries` (**toutes** les règles séries limitées / stock / badges — jamais de règle dans un template), `Catalog\Sorting`, `Product\Characteristics` ; page catégorie et recherche (1 colonne, sans facettes, tri Nouveautés / Prix, épuisés regroupés en fin de page, pagination 44 px, états vides) ; fiche produit (galerie 4:5 + lightbox natif, sélecteur de taille, quantité plafonnée au stock, encarts, bloc CMS `product_reassurance`, caractéristiques, « Vous aimerez aussi », barre d'achat mobile, variante épuisée) ; purge des caches produit à chaque mouvement de stock (plugins MSI) et cron nocturne du badge « Nouveauté ».
- **Carte produit réutilisable** : bloc `product_list_item` (`Magento_Catalog::product/list/item.phtml`), rendue via `Hyva\Theme\ViewModel\ProductListItem::getItemHtml…` — c'est elle que doivent utiliser les widgets Nouveautés / Incontournables de l'accueil et le slider natif `product/slider/product-slider.phtml` (déjà restylé au gabarit « mise en avant »).
- Pages CMS légales créées par data patch (`a-propos`, `livraison-retours`, `cgv`, `mentions-legales`, `confidentialite`, contenu « À rédiger ») ; blocs `header_announcement` et `product_reassurance`.
- Catégories en base (celles des maquettes, ids 12 à 17) : Trousses de toilette (> Grandes / Petites trousses), Pochettes à livre, Cotons démaquillants, Petits sacs. Treize produits de test dont un configurable, deux épuisés, un « Plus que 2 », deux nouveautés. Scripts rejouables : `docs/jeux-de-donnees/categories.php` puis `produits-test.php` (README du dossier).
- Traductions du thème : `i18n/fr_FR.csv` (libellés Hyvä rencontrés ; à compléter au fil de l'eau).
- **Aucun checkout installé** : `/checkout` affiche « No Checkout module installed ». Mollie (`mollie/magento2` + compat Hyvä) est présent dans `vendor/` ; Stripe vs Mollie **non tranché** — ne rien installer ni configurer côté paiement.

## Décisions déjà prises — ne pas les rouvrir

- Hyvä Checkout (payant) **écarté** ; **Luma Fallback Checkout** `hyva-themes/magento2-luma-checkout` (OSL-3.0, Packagist Hyvä de Pierre, v1.1.7 au 05/09/2026) retenu.
- Produits épuisés **visibles**, regroupés en fin de liste ; lightbox **natif** ; configurable à une seule taille en stock : sélecteur affiché, option épuisée barrée.
- WebP **reporté** (non natif dans 2.4.9) au lot 8 ou module tiers à valider avec Pierre.
- Pas de sous-libellé dans les boutons de taille ; pas de champ « Me prévenir » tant que la newsletter n'est pas tranchée.

## Pièges rencontrés au lot 2 — détail dans `documentation-theme.md` §2 et §8-§12

- **Ne jamais supprimer de catégorie ou de donnée partagée par script.** Le 10/09, une catégorie de test au chemin erroné `1/2` supprimée par script a effacé toute l'arborescence (Magento supprime tout ce qui commence par le chemin). Pour tester un état vide : désactiver des produits ou passer par l'admin.
- Le handle `catalog_category_view_type_layered` (module LayeredNavigation) impose `2columns-left` **après** le fichier du thème : surcharger le handle lui-même (fait pour catégorie et recherche).
- Page de résultats : appeler `getProductListHtml()` **avant** `getResultCount()`, sinon la requête OpenSearch est figée sans tri.
- Hyvä met en cache le HTML de chaque vignette une heure (tags produit) : les données affichées dans la carte doivent être couvertes par la purge (`Model/Cache/FlushProductCacheBySkus`).
- `pub/media/import/` est **interdit en HTTP** par le `.htaccess` racine : les fichiers de test vont dans `pub/media/madameaiguille/`.
- Les valeurs de configuration structurelles vivent dans `etc/config.xml` du module (versionnées), pas en base.
- `Hyva\Theme\ViewModel\Navigation::getNavigation(2)` = catégories de premier niveau (niveau **absolu**).
- Classes utilisées uniquement dans le CMS : les déclarer dans `@source inline(...)` de `tailwind-source.css`.
- Pour rejouer un data patch en dev : supprimer sa ligne dans `patch_list` puis `setup:upgrade`.
- Swatch texte : le radio est positionné en absolu, le libellé doit rester `position: relative` (`components/swatches.css`).

---

# 2. Sources de vérité — à lire avant de coder

Dans `~/Documents/aiguille/docs/` :

| Fichier | Ce que tu y trouves |
|---|---|
| **`documentation-theme.md`** (v1.2) | **Commence par lui.** Arborescences, build, tokens, composants, « où modifier quoi » pour chaque écran livré, fraîcheur des caches, commandes. À compléter à chaque écran livré (une section par écran, tableau admin / code) |
| **`plan-de-developpement.md`** (v1.2) | Lots 3 → 8, état après le lot 2, décisions, **points ouverts issus du lot 2** (page « Boutique » globale, WebP, champ « Me prévenir », `?product=` sur la page contact) |
| `prompt-claude-code-lot1.md` §4 et §7 | Règles non négociables et interdits, toujours valables |
| `prompt-claude-code-lot2.md` | Méthode de travail validée par Pierre (§5) |
| `cahier-des-charges-docs-developpement/specification-fonctionnelle.md` | §1.1 accueil et règle du bloc Nouveautés, §1.4 newsletter et Instagram, §2.2 formulaire de contact et champ « produit concerné » — **prime sur les maquettes pour le comportement** |
| `cahier-des-charges-docs-developpement/architecture-technique.md` | §5 modules envisagés, §6 sécurité (upload) |
| `cahier-des-charges-docs-developpement/guide-bonnes-pratiques-hyva.md` | ViewModels, escaping, layout, Alpine, Tailwind v4, sécurité §8 |
| `cahier-des-charges-docs-developpement/plan-de-tests.md` | §3 accueil, §4 contact, §9 sécurité |
| `maquettes-direction-artistique/Design System.dc.html` | §6.7 mise en avant de section, §6.6 alertes, §6.10 états vides (panier vide), §6.8 header, §6.11 footer |
| `maquettes-direction-artistique/Accueil.dc.html`, `Contact et À propos.dc.html` | Les écrans du lot 3 (pages HTML autonomes, lire la source) |
| `maquettes-direction-artistique/brand/` | `hero.png`, `atelier.png`, `ig-1..4.png` pour les blocs de l'accueil ; `p-*.png` déjà importés |

En cas de contradiction : la spécification l'emporte pour le comportement, les maquettes pour le visuel. **Signale toute contradiction à Pierre au lieu de trancher seul.**

---

# 3. Règles non négociables (rappel)

- WCAG 2.1 AA : jamais de texte sur `brand-rose` ni `brand-nude` ; texte courant sur fond blush en `brand-dark` (`.on-blush`) ; focus visible ; cibles 44 × 44 px ; chaque champ a un `<label>`.
- Britney (`font-display`) : titres uniquement, **jamais sous 30 px** (`text-display` est le plancher), jamais `italic`. Sentient : 16 px plancher, 13 px pour les mentions.
- **Jamais de valeur hexadécimale ni de classe arbitraire de couleur dans un `.phtml`** — tout passe par les tokens.
- Zéro logique métier dans un template → ViewModel déclaré en layout XML. Escaping systématique. `__()` sur tous les textes (en français directement, comme les lots 1 et 2). Un fichier de layout par handle. PHP 8 typé, `declare(strict_types=1)`.
- Ne jamais toucher à `vendor/`. Pas de `tailwind.config.js`. Pas de fonte distante. Pas de librairie JS supplémentaire.
- Ne pas reconstruire ce que Magento fait nativement : on retemplate.
- **Ne pas installer d'extension tierce sans vérifier sa compatibilité Hyvä et sans en parler à Pierre** — le Luma fallback est la seule installation prévue.
- Pas de données de test dans les data patches ; pas de suppression par script.

---

# 4. Périmètre

## Lot 6a — Luma Fallback Checkout (½ journée, branche `lot-6a-checkout`)

Détail dans `plan-de-developpement.md`, section « Lot 6 ». En résumé :

1. Vérifier l'accès au Packagist Hyvä (`shop/auth.json` ou `~/.composer/auth.json`, jamais commité) puis `composer require hyva-themes/magento2-luma-checkout` — **annoncer la commande**, montrer la version installée.
2. `setup:upgrade --keep-generated`, `cache:flush` ; vérifier `/checkout` avec un produit de test (ajout au panier depuis une fiche), guest checkout activé.
3. Vérifier que RequireJS / Knockout ne se chargent **que** sur les pages du tunnel (performance) : comparer le HTML de l'accueil et de `/checkout`.
4. Lire le README du module et documenter dans `documentation-theme.md` §14 où vivent les surcharges Luma (templates Knockout, LESS) pour le lot 6b. Aucun habillage à ce stade.
5. Commit, feu vert, puis branche `lot-3-accueil-cms-contact`.

## Lot 3 — Accueil, pages CMS, formulaire de contact

Détail dans `plan-de-developpement.md`, section « Lot 3 ». En résumé :

1. **Accueil** (`cms_index_index.xml` + blocs CMS + widgets) dans l'ordre : hero (visuel plafonné à 240 px sur mobile pour que Nouveautés soit sous le pli), **Nouveautés** (widget natif *New Products* sur `news_from_date`, **épuisés exclus**, carte `product_list_item` et ViewModel `LimitedSeries` du lot 2 ; le cron du lot 2 purge déjà les caches aux dates de nouveauté), Incontournables (sélection manuelle ou catégorie non visible), Histoire (bloc CMS + `atelier.png`), Pourquoi choisir (4 arguments), Instagram (galerie statique de 4 visuels), Newsletter **sous réserve**. Chaque bloc éditable par Céline sans code (classes déclarées en `@source inline`).
2. **Gabarit de mise en avant de section** (titre Britney entre filets ornés + accroche + lien) : déjà ébauché dans `product-slider.phtml` et `category/header.phtml` — à factoriser en un template réutilisable.
3. **Pages statiques** : gabarit `1column` typographié (classe `prose` de hyva-modules adaptée aux tokens, largeur de lecture ~65 caractères) pour À propos, Livraison & retours, CGV, Mentions légales, Confidentialité. Textes de Céline : garder « À rédiger » si absents.
4. **Formulaire de contact** : module dédié `MadameAiguille_Contact` recommandé (isoler l'upload) ; champs nom, email, message, **pièce jointe** (JPG/PNG, 5 Mo, validation serveur du type MIME, stockage hors `pub/` ou envoi en pièce jointe puis suppression), **produit concerné pré-rempli depuis `?product=<sku>`** (lien déjà posé sur la fiche, l'encart « série terminée » de la fiche épuisée et l'encart de la catégorie), reCAPTCHA natif, email à Céline + accusé de réception. ViewModel `ContactForm`. Page `/contact` au gabarit de la maquette « Contact et À propos ».
5. **Page 404** et **page panier vide** aux gabarits du Design System (états vides, template `product/list/empty.phtml` comme référence de style).
6. Enrichir `/styleguide` (gabarit de mise en avant, formulaire de contact, 404) et **`documentation-theme.md`** (sections accueil, pages CMS, contact : tableaux admin / code pour Céline), **`plan-de-developpement.md`** (décisions, points ouverts).

**Questions à poser à Pierre avant de coder** (il préfère trois questions à une implémentation à refaire) : « Actualités » = produits seulement ou aussi de l'information ; newsletter au lancement ; page « Nos tissus » ; pièce jointe envoyée par email ou stockée ; **page « Boutique » globale** (catégorie ancrée regroupant tout, ou accueil comme vitrine — conditionne « Voir toute la boutique » des états vides et le fil d'Ariane) ; module dédié ou dans `MadameAiguille_Theme` pour le contact.

---

# 5. Méthode de travail — celle que Pierre a validée pendant les lots 1 et 2

- **Commence par un plan** (étapes, fichiers, questions), présente-le et **attends sa validation**. Questions groupées via l'outil à choix multiples.
- **Une étape à la fois** : après chaque étape, commit atomique (message en français, `Co-Authored-By` du modèle, trailer `Claude-Session`), puis rends la main avec **la liste précise de ce qu'il doit vérifier** (navigateur, admin, base). Il répond « Vérifié, feu vert pour l'étape N ». **Ne jamais enchaîner deux étapes sans feu vert.**
- Toute commande `bin/magento` et `composer` est **annoncée explicitement**. `cache:flush` après un layout, une route, une section `system.xml`, un `di.xml` ; `cache:clean full_page block_html` après un template ou un bloc CMS ; `npm run build` après du CSS.
- Vérifie visuellement avant de rendre la main : `google-chrome --headless=new --disable-gpu --no-sandbox --hide-scrollbars --window-size=1440,3000 --screenshot=out.png http://localhost/...` puis lis l'image (découpe avec PIL si besoin ; pas de numpy). Teste **1440 et 390 px**. Pour les pages longues, augmenter la hauteur et recadrer.
- Pierre est développeur (Laravel, Vue, Tailwind ; ex-responsable technique Magento 2) : explique les spécificités Hyvä / Magento, pas les bases du web. Réponds en français. Signale tout écart avec le brief ou les maquettes, et **toute erreur de ta part immédiatement, avec la cause et la réparation**.
- Le jeu de données de test se rejoue avec `php docs/jeux-de-donnees/categories.php && php docs/jeux-de-donnees/produits-test.php` puis `indexer:reindex` et `cache:flush`.

Commence par lire `documentation-theme.md`, `plan-de-developpement.md` (état après le lot 2, lots 3 et 6), la spécification §1.1, §1.4 et §2.2, le Design System §6.6 à §6.11 et les maquettes Accueil / Contact et À propos, puis propose ton plan pour le lot 6a et le lot 3.
