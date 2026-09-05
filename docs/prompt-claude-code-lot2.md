Tu reprends le développement du thème **Madame Aiguille**, une boutique Magento 2 sous thème Hyvä. Un premier agent a livré le **lot 1 — fondations**. Tu démarres le **lot 2 — catalogue**. Lis intégralement ce brief, puis les documents cités en §2, **avant** d'écrire la moindre ligne de code.

---

# 1. État des lieux — vérifié fin du lot 1 (05/09/2026), ne pas re-supposer

| | |
|---|---|
| Racine projet / dépôt git | `~/Documents/aiguille/` — couvre `shop/` **et** `docs/` |
| Application | `~/Documents/aiguille/shop/` |
| Magento | Open Source **2.4.9**, mode `developer`, base `aiguille` (identifiants dans `shop/app/etc/env.php`, jamais commité) |
| Thème parent | Hyvä `magento2-default-theme` **1.5.2**, **Tailwind CSS v4** |
| Thème enfant | `shop/app/design/frontend/MadameAiguille/default/` — **actif** (`design/theme/theme_id = 5`, scope `stores/default`) |
| Module | `shop/app/code/MadameAiguille/Theme/` — activé |
| Branche | `lot-1-fondations`, 7 commits (`62dca83` → `307946a` + docs) ; `main` n'a que le commit initial. Crée une branche `lot-2-catalogue` depuis `lot-1-fondations` (ou depuis `main` si Pierre a mergé — vérifie `git log --oneline --all`) |
| Node / npm | Node 26, npm 11 (`.nvmrc` du thème : 20 minimum) |
| Machine | Ubuntu, Apache, PHP 8.5, MariaDB, Redis, Elasticsearch. Site : `http://localhost/` |
| git | identité globale configurée (Pierre Bultez) ; `lib/`, `setup/`, `dev/`, `vendor/`, `generated/`, `var/`, `pub/static`, `pub/media`, `node_modules/`, `web/css/styles.css`, `env.php` ignorés |

## Ce qui existe (lot 1)

- Chaîne Tailwind v4 du thème enfant : `web/tailwind/` (`tailwind-source.css` avec `@theme` et `@source inline`, `hyva.config.json` avec les tokens de couleur en oklch, `src/fonts.css`). Build : `cd shop/app/design/frontend/MadameAiguille/default/web/tailwind && npm run build` (ou `npm run watch`).
- Fontes Britney / Sentient auto-hébergées (`web/fonts/`), préchargées.
- Tokens : couleurs `brand*`, `success/error/info*`, `disabled-*`, `soldout-*`, `field-border` ; échelle `text-caption … text-display-2xl` ; rayons plafonnés à 6 px ; ombres `shadow-soft/card/raised/drawer` ; les neutres Tailwind (`gray-*`, `slate-*`, `white`, `black`, `red/green/blue/yellow`) sont **remappés** sur la palette chaude.
- Composants CSS : `btn` / `btn-primary` / `btn-link` / `btn-size-sm|lg`, champs (`form-input`… 48 px, `aria-invalid`), `message` (alertes), `card`, `badge badge-new|limited|scarce|soldout`, `scallop scallop-from-*|to-*` (feston), `header-grid`, `footer-grid`, `on-blush`, `tracking-label`.
- Page de contrôle **`/styleguide`** (404 en production) — référence visuelle de recette, à enrichir à chaque nouveau composant.
- Header : bandeau CMS `header_announcement`, logo centré (PNG 2× provisoire `web/images/logo-horizontal.png`), navigation catégories 2 niveaux (desktop : seconde ligne sous le logo ; mobile : burger + tiroir), recherche, compte, panier.
- Footer : feston, fond blush, 4 colonnes (marque + réseaux sociaux configurés en admin, Boutique = catégories, Informations = liens en layout XML, Paiement & livraison), accordéon mobile, copyright config.
- Module : route `/styleguide`, ViewModel `SocialLinks`, `system.xml` (section admin *Madame Aiguille*), data patches (pages CMS `a-propos`, `livraison-retours`, `cgv`, `mentions-legales`, `confidentialite` ; bloc `header_announcement`).
- Catégories de **test** (créées par script, pas en code) : Trousses de toilette (> Grandes / Petites trousses), Pochettes à livre, Cotons démaquillants, Petits sacs. Plus « Sneakers » et « T-Shirts » et un produit « Jordan Brooklyn » : données d'essai de Pierre, à supprimer avec son accord.
- **Aucun checkout installé** (`/checkout` affiche « No Checkout module installed »). Décision prise : **Luma Fallback Checkout** `hyva-themes/magento2-luma-checkout` (gratuit, OSL-3.0, sur le Packagist Hyvä de Pierre) au lot 6a. Hyvä Checkout (payant) est **écarté définitivement** — ne pas le re-proposer.
- Mollie (`mollie/magento2` + compat Hyvä) est présent dans `vendor/` ; Stripe vs Mollie **non tranché** (Pierre se renseigne) — ne rien installer ni configurer côté paiement.

---

# 2. Sources de vérité — à lire avant de coder

Dans `~/Documents/aiguille/docs/` :

| Fichier | Ce que tu y trouves |
|---|---|
| **`documentation-theme.md`** | **Commence par lui.** Arborescence, chaîne de build et ses pièges, tokens, composants, « où modifier quoi » pour header / footer / CMS. À **compléter à chaque écran livré** |
| **`plan-de-developpement.md`** | Découpage en lots 2 → 8, décisions prises, décisions en attente. Le lot 2 y est détaillé |
| `prompt-claude-code-lot1.md` | Le brief initial : règles non négociables (§4) et interdits (§7) toujours valables |
| `cahier-des-charges-docs-developpement/specification-fonctionnelle.md` | Règles de gestion écran par écran — **prime sur les maquettes pour le comportement** |
| `cahier-des-charges-docs-developpement/architecture-technique.md` | §4 : modélisation catalogue (`taille`, `serie_limitee`, `taille_serie`, `weight` obligatoire) |
| `cahier-des-charges-docs-developpement/guide-bonnes-pratiques-hyva.md` | Conventions de code : ViewModels, escaping, layout, Alpine, Tailwind v4 |
| `cahier-des-charges-docs-developpement/charte-graphique.md` | Typographie, accessibilité, photographie (ratio 4:5) |
| `cahier-des-charges-docs-developpement/plan-de-tests.md` | §3 et §4 : scénarios catalogue et fiche produit |
| `maquettes-direction-artistique/Design System.dc.html` | **Fait foi pour le visuel** : §6.3 carte produit, §6.4 badges, §6.5 navigation & sélecteurs (pagination, quantité, taille), §6.10 états vides, §7 photographie |
| `maquettes-direction-artistique/Page catégorie.dc.html`, `Fiche produit.dc.html` | Les deux écrans du lot 2. Ce sont des pages HTML autonomes : lis leur source (styles inline) |
| `maquettes-direction-artistique/brand/p-*.png` | Photos produit pour le jeu de données de test |

En cas de contradiction : la spécification fonctionnelle l'emporte pour le comportement, les maquettes pour le visuel. **Signale toute contradiction à Pierre au lieu de trancher seul.**

---

# 3. Règles non négociables (rappel — détail dans `prompt-claude-code-lot1.md` §4 et §7)

- WCAG 2.1 AA : jamais de texte sur `brand-rose` ni `brand-nude` ; texte courant sur fond blush en `brand-dark` (`.on-blush`) ; focus visible ; cibles 44 × 44 px ; chaque champ a un `<label>`.
- Britney (`font-display`) : titres uniquement, **jamais sous 30 px** (`text-display` est le plancher), jamais `italic`. Sentient : 16 px plancher, 13 px pour les mentions.
- **Jamais de valeur hexadécimale ni de classe arbitraire de couleur dans un `.phtml`** — tout passe par les tokens. Une nouvelle couleur = `hyva.config.json`.
- Zéro logique métier dans un template → ViewModel déclaré en layout XML. Escaping systématique. `__()` sur tous les textes. Un fichier de layout par handle. PHP 8 typé, `declare(strict_types=1)`.
- Ne jamais toucher à `vendor/`. Pas de `tailwind.config.js`. Pas de fonte distante.
- Ne pas reconstruire ce que Magento fait nativement : on retemplate.
- Ne pas installer d'extension tierce sans vérifier sa compatibilité Hyvä et sans en parler à Pierre.
- Pas de données de test dans les data patches (elles seraient créées en production) : les produits de test se créent par script hors code, comme les catégories du lot 1.

---

# 4. Périmètre du lot 2 — catalogue

Détail dans `plan-de-developpement.md`, section « Lot 2 ». En résumé :

1. **Modèle de données** (data patch dans `MadameAiguille_Theme/Setup/Patch/Data/`) : attributs `taille` (dropdown, 2 valeurs, utilisable en configurable), `serie_limitee` (booléen), `taille_serie` (entier) ; attribute set « Création » avec ces attributs et **`weight` requis**. Seuil de rareté (« Plus que N exemplaires », défaut 3) en configuration admin, section *Madame Aiguille* existante (`etc/adminhtml/system.xml`).
2. **ViewModel `LimitedSeries`** : mention série limitée, stock restant, état épuisé — aucune règle dans les templates.
3. **Page catégorie** : grille 2 / 3 / 4 colonnes, carte produit (image 4:5, badges, prix tabulaire, nom en `text-subtitle`), tri, pagination 44 px, épuisés en fin de liste si Pierre confirme l'option « visibles ». Pas de filtres à facettes.
4. **Fiche produit** : galerie 4:5 + zoom (composant natif Hyvä restylé), sélecteur de taille (2 options, épuisé barré), quantité plafonnée au stock, ajout au panier, lien « Une question sur le tissu ou le motif ? » vers `/contact?product=<sku>` (la page contact sera faite au lot 3), fil d'Ariane, mention « Série limitée — X pièces ».
5. **États vides** : catégorie sans produit, recherche sans résultat.
6. `etc/view.xml` du thème : tailles d'images au ratio 4:5, WebP.
7. **Jeu de données de test** par script (hors code) : une dizaine de produits avec les photos `brand/p-*.png`, poids renseignés, un configurable à deux tailles, un produit épuisé, un « Plus que 2 ».
8. Enrichir `/styleguide` (carte produit, sélecteurs) et **`documentation-theme.md`** (sections page catégorie / fiche produit : où modifier quoi).

**Questions à poser à Pierre avant de coder** (il préfère trois questions à une implémentation à refaire) : produits épuisés visibles ou masqués ; zoom natif ou lightbox plein écran sur mobile ; comportement d'un configurable dont une seule taille est en stock ; suppression des données d'essai Sneakers / T-Shirts / Jordan.

---

# 5. Méthode de travail — celle que Pierre a validée pendant le lot 1

- **Commence par un plan** du lot 2 (étapes, fichiers, questions), présente-le et **attends sa validation**.
- **Une étape à la fois** : après chaque étape, commit atomique (message en français, `Co-Authored-By` du modèle), puis rends la main avec **la liste précise de ce qu'il doit vérifier** (navigateur, admin, base). Il répond « Vérifié, feu vert pour l'étape N ». **Ne jamais enchaîner deux étapes sans feu vert.**
- Toute commande `bin/magento` est **annoncée explicitement** (la lancer est accepté, la taire non). `cache:flush` complet après une nouvelle route, un layout, une section `system.xml` ; `cache:clean full_page block_html` après un template.
- Vérifie visuellement avant de rendre la main : l'extension Chrome n'était pas connectée, Chrome headless fonctionne : `google-chrome --headless=new --disable-gpu --no-sandbox --hide-scrollbars --window-size=1440,3000 --screenshot=out.png http://localhost/...` puis lis l'image (découpe avec PIL si besoin). Teste 1440 et 390 px.
- Questions groupées (l'outil de questions à choix multiples lui convient) plutôt que des décisions prises seul. Signale tout écart avec le brief ou les maquettes.
- Pierre est développeur (Laravel, Vue, Tailwind ; ex-responsable technique Magento 2) : explique les spécificités Hyvä / Magento, pas les bases du web. Réponds en français.
- Pièges déjà rencontrés (détail dans `documentation-theme.md` §2) : Tailwind v4 CLI ne rebase pas les `url()` ; `*/` dans un commentaire CSS casse le build ; `Navigation::getNavigation($maxLevel)` filtre sur le niveau **absolu** des catégories (top = 2, sous-catégories = 3) ; `@utility container` vit dans `components/wrapper.css`.

Commence par lire `documentation-theme.md`, `plan-de-developpement.md` (lot 2), la spécification §1.2 / §2 / §8, le Design System §6.3-6.5 / §6.10 / §7 et les deux maquettes du lot 2, puis propose ton plan.
