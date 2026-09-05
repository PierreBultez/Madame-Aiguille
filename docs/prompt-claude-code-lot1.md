Tu vas démarrer le développement du thème **Madame Aiguille**, une boutique Magento 2 sous thème Hyvä. Lis intégralement ce brief, puis lis les documents cités en §2 **avant** d'écrire la moindre ligne de code.

Ce brief couvre le **lot 1 — les fondations**. Il ne s'agit pas de construire tout le site : il s'agit de poser le socle sur lequel tous les écrans seront ensuite développés, et de me proposer un découpage pour la suite.

---

# 1. État des lieux — vérifié, ne pas re-supposer

L'installation est en place et fonctionnelle. Cache, recherche, indexation : tout a été testé et validé.

| | |
|---|---|
| Racine projet | `~/Documents/aiguille/` |
| Application | `~/Documents/aiguille/shop/` |
| Magento | **Open Source 2.4.9** (`magento/project-community-edition`) |
| Thème | **Hyvä `magento2-default-theme` 1.5.2** (contrainte `^1.5`) |
| CSS | ⚠️ **Tailwind CSS v4** (`tailwindcss ^4.3.1`, `@tailwindcss/cli`) |
| Mode | `MAGE_MODE = developer` |
| Cache / sessions | Redis |
| Base de données | `aiguille` sur `localhost` |
| Node / npm | Node 22, npm 10 |
| Machine | Ubuntu 25.10 — Apache, PHP, MySQL, Redis, Elasticsearch, RabbitMQ |

## Ce qui n'existe pas encore

- ❌ **Aucun thème enfant** — `app/design/frontend/` ne contient que `Magento/`
- ❌ **Aucun module custom** — `app/code/` est vide
- ❌ **Aucun dépôt git**, et **aucun `.gitignore`** à la racine de `shop/`
- ❌ Aucun contenu catalogue

Le dépôt est le tout premier sujet à traiter (cf. §5, étape 0). `vendor/` pèse 774 Mo, `var/` 43 Mo, `generated/` 16 Mo : un `git add .` sans `.gitignore` correct serait une catastrophe.

---

# 2. Sources de vérité — à lire avant de coder

Dans `~/Documents/aiguille/docs/` :

### `cahier-des-charges-docs-developpement/`

| Fichier | Ce que tu y trouves |
|---|---|
| `cahier-des-charges.md` | Contexte métier, périmètre v1, ce qui est hors périmètre |
| `specification-fonctionnelle.md` | Parcours et règles de gestion, écran par écran |
| `architecture-technique.md` | Choix techniques, modules envisagés, modélisation catalogue |
| `charte-graphique.md` | Palette, typographie, échelle, règles d'accessibilité |
| `guide-bonnes-pratiques-hyva.md` | **Conventions de code à respecter** — structure du thème enfant, ViewModels, escaping, Alpine, Tailwind |
| `plan-de-tests.md` | Scénarios de recette |

### `maquettes-direction-artistique/`

Maquettes produites avec Claude Design, en `.dc.html` — ce sont des pages HTML autonomes, ouvre-les dans un navigateur ou lis leur source :

`Design System` · `Accueil` · `Page catégorie` · `Fiche produit` · `Panier` · `Checkout` · `Compte client` · `Contact et À propos`

Le sous-dossier `brand/` contient les visuels (logo en plusieurs déclinaisons, photos produit, ambiance) et `fonts/` les `.woff2` de Britney et Sentient.

> **L'artboard `Design System` fait foi** pour les tokens : couleurs, échelle typographique, espacements, rayons, états des composants. Extrais-en la liste réelle plutôt que de la reconstituer — elle contient des valeurs que la charte ne mentionne pas (tons de bordure, couleurs d'état succès/erreur, gris de texte secondaire).

En cas de contradiction : la **spécification fonctionnelle** l'emporte sur les maquettes pour le comportement, les **maquettes** l'emportent sur la charte pour le visuel. Signale-moi toute contradiction que tu rencontres au lieu de trancher seul.

---

# 3. ⚠️ Tailwind v4 — le piège principal

Hyvä 1.5 est passé à **Tailwind v4**. La quasi-totalité de la documentation et des exemples Hyvä que tu as pu voir concernent Tailwind v3. **Ne applique pas les réflexes v3.**

Concrètement, dans `vendor/hyva-themes/magento2-default-theme/web/tailwind/` :

- **Il n'y a pas de `tailwind.config.js`.** La configuration est en CSS, via `@theme { }` dans `tailwind-source.css`
- La détection des classes se fait par directives **`@source`** (`@source "../../**/*.phtml"`), et non par un tableau `content`
- Un fichier **`hyva.config.json`** complète la configuration : `tailwind.include` pour hériter d'un thème parent ou de modules, et `tokens.values.color` pour les tokens de design, exprimés en **oklch**
- Deux générateurs tournent avant chaque build : `npx hyva-sources` et `npx hyva-tokens`, qui produisent `generated/hyva-source.css` et `generated/hyva-tokens.css`
- Scripts npm disponibles : `npm run watch` (dev), `npm run build` (prod, minifié), `npm run generate`

**Avant d'écrire quoi que ce soit côté CSS**, lis ces fichiers du thème parent pour t'aligner sur les conventions réelles :

```
vendor/hyva-themes/magento2-default-theme/web/tailwind/tailwind-source.css
vendor/hyva-themes/magento2-default-theme/web/tailwind/hyva.config.json
vendor/hyva-themes/magento2-default-theme/web/tailwind/theme/
vendor/hyva-themes/magento2-default-theme/web/tailwind/base/
```

Si la documentation officielle t'est nécessaire : <https://docs.hyva.io/hyva-themes/working-with-tailwindcss/>

### Tokens de marque — valeurs converties en oklch

À déclarer dans `hyva.config.json` du thème enfant, sous `tokens.values.color`. Les équivalents hexadécimaux sont donnés pour contrôle :

```json
"brand":       "oklch(53.4% 0.054 26.8)",   /* #8A615C */
"brand-dark":  "oklch(45.5% 0.046 26.8)",   /* #6E4D49 */
"brand-rose":  "oklch(67.8% 0.078 31.5)",   /* #C3867A */
"brand-nude":  "oklch(84.4% 0.053 51.8)",   /* #E9C3AD */
"brand-blush": "oklch(93.8% 0.014 17.4)",   /* #F4E7E7 */
"brand-ivory": "oklch(96.2% 0.013 48.6)",   /* #FAF0EB */
"brand-paper": "oklch(99.3% 0.004 56.4)",   /* #FFFCFA */
"brand-ink":   "oklch(42.8% 0.032 44.4)"    /* #5F4A41 */
```

Complète cette liste avec les tokens d'état et de bordure relevés dans l'artboard Design System.

### Contenu CMS et purge

Les classes utilisées dans les blocs CMS édités en back-office **ne sont pas vues** par le scanner Tailwind et disparaissent donc du CSS de production. Toute classe destinée à être employée par la cliente dans le CMS doit être déclarée explicitement — en Tailwind v4, via `@source inline(...)`. Documente la liste dans un commentaire.

---

# 4. Règles non négociables

Elles viennent du client et de la charte. Elles ne se discutent pas en cours de route.

## Accessibilité — WCAG 2.1 AA

- ❌ **Jamais de texte sur `brand-rose` `#C3867A`** : 3,0:1 avec du blanc, insuffisant. C'est une couleur décorative
- ❌ **`brand-nude` `#E9C3AD` est purement décoratif** : 1,6:1
- ✅ Boutons principaux : fond `brand` `#8A615C`, texte blanc — 5,3:1
- ✅ Corps de texte : `brand-ink` `#5F4A41` sur ivoire — 8,1:1
- Focus clavier visible partout, à 3:1 minimum contre l'arrière-plan
- Cibles tactiles de 44 × 44 px minimum
- Chaque champ de formulaire a un `<label>` réellement associé

## Typographie

- **Britney** (display) : titres uniquement, **jamais sous 30 px**, jamais dans un bouton, un prix, un libellé ou une ligne de tableau. Elle est déjà italique par dessin, ne lui ajoute pas d'`italic`
- **Sentient** : corps de texte et interface, **16 px plancher**, 13 px pour les mentions
- Fontes **auto-hébergées**, aucun chargement distant, ni Google Fonts ni API Fontshare
- **Servir les `.woff2` fournis tels quels** : la licence ITF interdit le subsetting et la conversion de format
- Ne déclarer que les graisses utilisées : Britney Regular ; Sentient Light, Regular, Medium, Italic
- Précharger Britney Regular et Sentient Regular

## Code

Le fichier `guide-bonnes-pratiques-hyva.md` fait autorité. En résumé :

- **On ne touche jamais à `vendor/`.** Tout le code vit dans le thème enfant et le module custom
- **Escaping systématique** dans les `.phtml` : `$escaper->escapeHtml()`, `escapeUrl()`, `escapeHtmlAttr()`, `escapeJs()` selon le contexte
- **Zéro logique métier dans un template.** Elle va dans un ViewModel, déclaré en layout XML
- Un fichier de layout par handle, jamais de fourre-tout
- Alpine.js pour l'interactivité, en utility-first Tailwind ; `@apply` réservé aux composants réellement répétés
- PHP 8 typé, `declare(strict_types=1)`, PSR-12

---

# 5. Périmètre du lot 1

## Étape 0 — Dépôt git

- `git init` à la racine de `~/Documents/aiguille/` (le dépôt couvre `shop/` **et** `docs/`)
- Écrire un `.gitignore` adapté à Magento 2 : `vendor/`, `generated/`, `var/`, `pub/static/` (sauf `.htaccess`), `pub/media/` (sauf `.htaccess`), `app/etc/env.php`, `node_modules/`, `.idea/`
- ⚠️ **`app/etc/env.php` ne doit jamais être committé** : il contient les identifiants de base de données et la clé de chiffrement
- Vérifier avec `git status` que rien de lourd ni de sensible n'est suivi **avant** le premier commit
- Premier commit sur une branche `main`, puis créer une branche de travail

## Étape 1 — Thème enfant

- Créer `app/design/frontend/MadameAiguille/default/`, héritant de `Hyva/default`
- `theme.xml`, `registration.php`, `composer.json` si pertinent
- Mettre en place la chaîne Tailwind du thème enfant, avec `hyva.config.json` pointant sur le thème parent via `tailwind.include.src`
- `npm install` puis `npm run build`, et vérifier que le CSS se génère
- Activer le thème en base et vider les caches

## Étape 2 — Fontes et tokens

- Intégrer Britney et Sentient depuis `docs/maquettes-direction-artistique/fonts/`
- `@font-face` dans un fichier dédié, importé en tête de la source Tailwind — jamais dispersés
- Préchargement via `default_head_blocks.xml`
- Déclarer les tokens de couleur, l'échelle typographique et les espacements
- Livrer une **page de contrôle** affichant tokens, échelle typographique et états de focus, pour valider visuellement le socle

## Étape 3 — Module `MadameAiguille_Theme`

- Squelette dans `app/code/MadameAiguille/Theme/` : `registration.php`, `etc/module.xml`, `composer.json`
- Ce module accueillera les ViewModels et les attributs catalogue. Ne mets rien dedans pour l'instant au-delà du squelette et d'un premier ViewModel si l'étape 4 en a besoin

## Étape 4 — Header et footer

Les deux éléments présents sur toutes les pages, donc les bons candidats pour valider le socle :

- **Header** : navigation `Accueil · Boutique · À propos · Contact`, **logo centré** (Hyvä le place à gauche par défaut : surcharge de `Magento_Theme::html/header.phtml`), panier avec compteur, menu burger sur mobile
- **Footer** : mentions légales, CGV, politique de confidentialité, contact, réseaux sociaux
- Responsive, navigable au clavier, conforme aux maquettes

## Étape 5 — Plan pour la suite

Rends-moi un **découpage en lots** pour le reste du développement, avec pour chacun : le périmètre, les dépendances, les points d'incertitude et une estimation d'effort relative. Prends en compte les éléments que je sais déjà structurants :

- la modélisation catalogue (séries limitées, attribut `taille` à 2 valeurs, attribut `poids` obligatoire)
- le bloc Nouveautés en page d'accueil
- le formulaire de contact avec pièce jointe et champ « produit concerné »
- les frais de port au poids en table rates, et le sélecteur de point relais Mondial Relay — **la seule dépendance tierce structurante du projet**
- les paiements : Stripe, virement, remise en main propre

**Ne commence aucun de ces lots.** Le lot 1 s'arrête à l'étape 5.

---

# 6. Méthode de travail

- **Commence par un plan**, avant de coder. Présente-le-moi et attends ma validation
- **Commits atomiques**, messages en français, un commit par étape cohérente
- Après chaque étape, dis-moi précisément **ce que je dois vérifier moi-même** dans le navigateur ou en base
- Si une commande `bin/magento` est nécessaire, donne-la explicitement plutôt que de la lancer en silence
- **Pose-moi des questions** dès qu'un point est ambigu. Je préfère trois questions à une implémentation à refaire
- Je suis développeur — Laravel, Vue, Tailwind au quotidien, et j'ai été responsable technique sur Magento 2. Explique-moi les spécificités Hyvä et les subtilités Magento, pas les bases du web

# 7. Interdits

- ❌ Modifier quoi que ce soit dans `vendor/`
- ❌ Committer `app/etc/env.php`, `vendor/`, `generated/`, `var/`, `node_modules/`
- ❌ Appliquer des réflexes Tailwind v3 : créer un `tailwind.config.js`, utiliser un tableau `content`
- ❌ Charger une fonte depuis un CDN ou Google Fonts
- ❌ Écrire une valeur hexadécimale en dur dans un `.phtml` ou une classe arbitraire type `text-[#8A615C]` — tout passe par les tokens
- ❌ Reconstruire le panier ou le checkout : on retemplate le natif Magento
- ❌ Installer une extension tierce sans vérifier d'abord sa compatibilité Hyvä et sans m'en parler
- ❌ Enchaîner les étapes sans me rendre la main

---

Commence par lire les documents et les maquettes, puis propose-moi ton plan pour le lot 1.
