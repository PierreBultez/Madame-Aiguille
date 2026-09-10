# Charte graphique — Site e-commerce "Madame Aiguille"

Document interne — v0.2 (05/09/2026)

> **v0.2** — Typographie arrêtée (Britney + Sentient, ITF), décision de contraste validée par Pierre, règles d'auto-hébergement et contraintes de licence ajoutées.

Ce document formalise l'identité visuelle à partir des éléments fournis par Céline : le logo (`6c3863b2-…png`), la maquette d'ambiance (`a3ed1466-…png`) et ses réponses au questionnaire préliminaire. Il sert de source unique pour la configuration Tailwind du thème enfant Hyvä.

## 1. Positionnement et ton

| | |
|---|---|
| Phrase d'accroche (logo) | **« L'élégance cousue main »** |
| Accroche secondaire (maquette) | « Créations textiles cousues avec amour » — « Accessoires élégants pour sublimer le quotidien » |
| La marque en trois mots (par la cliente) | **délicate, élégante, attentionnée** |
| Positionnement produit | « accessible et coloré » (cœur de gamme 10-20 €) |

Il y a une **tension à arbitrer** entre l'ambiance de la maquette (romantique, feutrée, très pastel, presque haut de gamme) et le positionnement revendiqué (« accessible et coloré »). La maquette d'ambiance porte peu de couleur. Recommandation : conserver la palette neutre comme **écrin** (fonds, header, boutons, typographie) et laisser la **couleur venir des photos produit** — ce sont les tissus à motifs qui apportent le « coloré ». Cela a un corollaire opérationnel : le fond des photos doit rester neutre et homogène, sinon l'ensemble devient visuellement bruyant.

### Cible

Femmes de 20 à 45 ans, sensibles au fait-main et au DIY : jeunes mamans, amatrices de mode éthique, entrepreneuses. Trafic attendu **majoritairement mobile** (acquisition via Instagram) — le design se conçoit donc mobile d'abord.

## 2. Logo

### Version fournie

Composition circulaire : « Madame Aiguille » en script, arqué au-dessus d'un bouton de couture stylisé traversé d'un fil et d'une aiguille, avec « L'ÉLÉGANCE COUSUE MAIN » en capitales espacées arquées en dessous. Fichier source : PNG 2000 × 2000 px sur fond ivoire.

### Déclinaisons à produire

| Variante | Usage | Remarque |
|---|---|---|
| **Carrée / badge** (version fournie, retravaillée) | Favicon, avatar réseaux sociaux, tampon sur les emballages | La baseline arquée devient illisible sous ~120 px : prévoir une version **sans baseline** pour les petites tailles, et une version **icône seule** (le bouton) pour le favicon 32 px |
| **Rectangulaire / horizontale** | Header du site, en-tête des emails transactionnels, factures | Icône à gauche + « Madame Aiguille » sur une ligne + baseline en dessous. C'est la version qui manque aujourd'hui et qui est la plus utilisée |
| **Monochrome** | Filigranes, impression, fonds sombres | Une version tout en `#8A615C` et une version ivoire |

### Fichiers cibles

Brief de production : **`../prompts/prompt-logos-svg.md`**.

| Fichier | Usage | Bascule |
|---|---|---|
| `logo-mark.svg` | Avatar réseaux sociaux, tampon, icône d'app | ≥ 96 px |
| `logo-mark-simple.svg` | Header mobile, pastille de compte, en-tête d'email | 32 à 96 px |
| `favicon.svg` | Onglet navigateur — le bouton seul, redessiné à cette taille | 16 à 32 px |
| `logo-horizontal.svg` | Header desktop, emails transactionnels, factures | ≥ 240 px de large |
| `logo-horizontal-compact.svg` | Header mobile — sans baseline | < 240 px de large |
| `logo-mono.svg` | Filigranes, impression une couleur, marquage | toutes |

### Contraintes techniques

- **Vectoriser le logo (SVG)** : c'est le point le plus important. Le PNG fourni ne supportera ni le redimensionnement ni les écrans à haute densité, et pèse 380 Ko pour un élément présent sur toutes les pages
- Prévoir une **zone de respiration** minimale autour du logo égale à la hauteur du bouton
- Fond ivoire du PNG à détourer (transparence) pour pouvoir le poser sur les différents fonds du site
- Le logo est **centré dans le header** dans la maquette, là où Hyvä le place à gauche par défaut → surcharge de template à prévoir (cf. architecture §4)

## 3. Palette

Valeurs relevées directement dans le logo et la maquette fournis.

| Rôle | Hex | Provenance | Usage |
|---|---|---|---|
| **Brun rosé — couleur de marque** | `#8A615C` | Script du logo | Titres, boutons principaux, liens, prix |
| **Rose poudré** | `#C3867A` | Boutons de la maquette | **Décoratif uniquement** (cf. §3.1) : survols, filets, icônes, aplats |
| **Nude doré** | `#E9C3AD` | Baseline et cercle du logo | Filets, séparateurs, ornements, bordures |
| **Blush** | `#F4E7E7` | Fond du bouton du logo | Fonds de sections, cartes produit |
| **Ivoire chaud** | `#FAF0EB` | Fond de la maquette | Fond de page par défaut |
| **Ivoire clair** | `#FFFCFA` | Fond du logo | Fond des cartes, du header |
| **Brun profond — texte** | `#5F4A41` | Textes de la maquette | Corps de texte, descriptions produit |

La cliente demande des « touches légèrement dorées ». Le nude `#E9C3AD` en tient lieu. Un vrai doré métallique (dégradé) est déconseillé en web : il rend mal, vieillit vite et pose des problèmes de contraste. Si un effet doré est souhaité, le réserver aux **filets fins et aux ornements**, jamais au texte.

### 3.1 Contraste et accessibilité — point de vigilance

Ratios mesurés sur la palette fournie :

| Combinaison | Ratio | Verdict |
|---|---|---|
| `#5F4A41` sur ivoire `#FFFCFA` | **8,1 : 1** | ✅ Excellent — à retenir pour le corps de texte |
| `#8A615C` sur ivoire `#FFFCFA` | **5,2 : 1** | ✅ Conforme AA |
| Blanc sur `#8A615C` | **5,3 : 1** | ✅ Conforme AA — **boutons principaux** |
| Blanc sur `#C3867A` (bouton de la maquette) | **3,0 : 1** | ❌ **Échoue** le seuil AA de 4,5 : 1 |
| `#C3867A` sur ivoire | **2,9 : 1** | ❌ Échoue — inutilisable pour du texte |
| `#E9C3AD` sur ivoire | **1,6 : 1** | ❌ Décoratif uniquement |

**Conséquence concrète** : le rose des boutons de la maquette (`#C3867A`) ne peut pas porter de texte blanc. Le bouton « Découvrir la collection » de la maquette d'ambiance serait non conforme en l'état. Solution retenue : **boutons en `#8A615C`** (la couleur du script du logo — donc parfaitement fidèle à l'identité, et conforme), avec `#C3867A` en couleur de survol ou en aplat décoratif. Alternative si l'on tient au rose : le foncer jusqu'à `#A25849` (5,2 : 1 sur blanc), mais la teinte glisse vers la terre cuite.

Ce n'est pas un détail cosmétique : une partie de la cible consulte le site sur mobile en extérieur, et l'accessibilité conditionne aussi le référencement.

### 3.2 Tokens de couleur — Tailwind v4

⚠️ Hyvä 1.5 tourne sous **Tailwind CSS v4** : il n'y a plus de `tailwind.config.js`. Les couleurs se déclarent comme **tokens de design Hyvä** dans le `hyva.config.json` du thème enfant, en **oklch**, et sont converties automatiquement en variables CSS par `npx hyva-tokens`.

```json
{
  "tokens": {
    "values": {
      "color": {
        "brand":       "oklch(53.4% 0.054 26.8)",
        "brand-dark":  "oklch(45.5% 0.046 26.8)",
        "brand-rose":  "oklch(67.8% 0.078 31.5)",
        "brand-nude":  "oklch(84.4% 0.053 51.8)",
        "brand-blush": "oklch(93.8% 0.014 17.4)",
        "brand-ivory": "oklch(96.2% 0.013 48.6)",
        "brand-paper": "oklch(99.3% 0.004 56.4)",
        "brand-ink":   "oklch(42.8% 0.032 44.4)"
      }
    }
  }
}
```

Les équivalents hexadécimaux sont ceux du tableau ci-dessus, dans le même ordre : `#8A615C`, `#6E4D49`, `#C3867A`, `#E9C3AD`, `#F4E7E7`, `#FAF0EB`, `#FFFCFA`, `#5F4A41`.

⚠️ `brand-rose` ne porte jamais de texte, `brand-nude` est purement décoratif. Cf. §3.1.

## 4. Typographie — **arrêtée**

Deux familles, toutes deux d'**Indian Type Foundry** (Fontshare), sous **ITF Free Font License**. Fichiers dans `docs/fonts/`.

| Rôle | Fonte | Graisses utiles |
|---|---|---|
| **Logo, titres, accroches** | **Britney** (Diana Ovezea & Sabina Chipară) | Light 300, Regular 400 — variable disponible |
| **Corps de texte, interface, formulaires** | **Sentient** (Noopur Choksi) | Light 300, Regular 400, Medium 500, Bold 700 + italiques — variable disponible |

Les deux couvrent **intégralement** le français : accents complets, ligature `œ`, guillemets `« »`, `€`, apostrophe typographique. Vérifié sur les fichiers fournis.

### Pourquoi cette répartition

**Britney** est une display italique à très fort contraste, d'esprit didone. Superbe à grande taille, où elle apporte exactement le caractère calligraphique du logo. Testée à 15-17 px, elle devient illisible : les déliés disparaissent et les accents s'effacent. Elle est donc **strictement réservée à l'affichage**.

**Sentient** est une serif de labeur : lisible dès 13 px, accents solides, italique de qualité. C'est elle qui porte tout ce que la cliente doit lire pour acheter — descriptions, prix, formulaires, checkout.

### Échelle typographique

| Usage | Fonte | Taille mobile / desktop | Remarques |
|---|---|---|---|
| Nom de la marque (header) | *(image du logo)* | — | Jamais du texte stylé : la casse et le crénage du logo ne sont pas reproductibles en CSS |
| Titre de hero | Britney Regular | 40 / 64 px | Une ligne, deux au maximum |
| Titres de section (H2) | Britney Regular | 30 / 40 px | « Les Incontournables », « Nouveautés »… |
| Titre de page, nom de produit en fiche (H1) | Sentient Medium | 26 / 32 px | Sentient et non Britney : ce sont des textes longs et variables |
| Sous-titres (H3), nom de produit en vignette | Sentient Medium | 18 / 20 px | |
| Corps de texte, descriptions | Sentient Regular | **16 px minimum** | Interlignage 1,6 |
| Accroches, citations | Sentient Italic | 16 / 18 px | « Accessoires élégants pour sublimer le quotidien » |
| Prix | Sentient Medium | 18 / 20 px | Chiffres tabulaires si disponibles, pour aligner les colonnes du panier |
| Boutons, labels de formulaire | Sentient Medium | 15 / 16 px | |
| Mentions légales, aides de saisie | Sentient Regular | **13 px plancher** | |

### Règles

- **Britney ne descend jamais sous 30 px.** En dessous, elle n'est plus lisible — c'est une règle, pas une préférence
- **Aucun texte d'interface en Britney** : ni bouton, ni label, ni prix, ni élément de checkout
- Britney est déjà italique par dessin : ne jamais lui appliquer d'`italic` supplémentaire
- Corps de texte à **16 px minimum**, y compris sur mobile
- Deux familles suffisent : ne pas introduire de troisième fonte

### Auto-hébergement — **obligatoire** (aucun script distant)

- Servir les **`.woff2` fournis dans `Fonts/WEB/fonts/`**, avec repli `.woff`. Ignorer les `.eot` (Internet Explorer, mort)
- **Ne pas sous-ensembler (`subsetting`) ni reconvertir les fichiers.** La FFL d'ITF interdit explicitement le subsetting, la conversion de format et toute modification des métadonnées sans accord écrit du fondeur (§02 et §05 de la licence). Les fichiers web fournis sont à servir **tels quels**
- Ne charger que les graisses réellement utilisées : Britney Regular ; Sentient Light, Regular, Medium, Italic. Chaque graisse superflue est un aller-retour réseau de plus
- `font-display: swap` (déjà présent dans les CSS fournis par ITF)
- **Précharger** les deux fontes du premier écran (Britney Regular, Sentient Regular) avec `<link rel="preload" as="font" type="font/woff2" crossorigin>`
- Ne **jamais** passer par l'API Fontshare ni par un CDN : outre la performance, un chargement distant transmet l'IP du visiteur à un tiers (question RGPD)
- Conserver les fichiers de licence `FFL.txt` dans le dépôt, à côté des fontes

### Planches de spécimens

Deux planches rendues avec les fontes réelles et la palette de la marque, utilisables telles quelles pour présenter le choix typographique à Céline :

Les deux planches sont dans `../maquettes-direction-artistique/`.

- `specimen-britney.png` — échelle d'emploi, plancher de 30 px, et la comparaison Britney / Sentient à 16 px qui justifie l'usage de deux fontes
- `specimen-sentient.png` — graisses, lisibilité en petit corps, et deux mises en situation (fiche produit, formulaire de livraison)

### Déclaration des familles — Tailwind v4

Dans le `tailwind-source.css` du thème enfant, via `@theme` :

```css
@theme {
  --font-display: 'Britney', Didot, 'Bodoni MT', Georgia, serif;
  --font-body: 'Sentient', 'Iowan Old Style', Georgia, serif;
}
```

Les `@font-face` vont dans un fichier dédié importé en tête de la source Tailwind, jamais dispersés dans les composants.

## 5. Motifs et ornements

Éléments récurrents dans le logo et la maquette, à reprendre avec parcimonie :

- **Filets ondulés / arabesques** de part et d'autre des titres de section (`~ Titre ~`)
- **Petits cœurs** en séparateur
- **Bordures festonnées** entre les sections (effet dentelle)
- **Fonds texturés** très légers (papier, motif floral en filigrane à très faible opacité)
- **Branchages / feuillages** fins, dans le nude

Implémentation : en **SVG inline** ou en `background-image` SVG, jamais en images bitmap. Une opacité faible (5 à 10 %) pour les fonds texturés, afin de ne pas gêner la lecture ni concurrencer les photos produit.

## 6. Photographie

C'est le poste qui déterminera la perception de qualité du site, davantage que le code.

### État actuel

Photos issues des réseaux sociaux et du catalogue papier réalisé par Céline pour les marchés et brocantes. Qualité et cadrage hétérogènes. Un photographe professionnel est envisagé, sans date.

### Règles à fixer **dès maintenant**, avant toute mise en ligne

- **Un seul ratio** pour toutes les images produit — recommandation : **4:5 (portrait)**, qui occupe bien l'écran mobile et correspond au format Instagram
- **Résolution source** : 2000 px sur le côté long minimum, pour permettre le zoom
- **Fond neutre et constant** : lin, bois clair ou textile ivoire. C'est ce qui fait la cohérence d'une grille de catalogue, bien plus que la qualité individuelle de chaque photo
- **Lumière naturelle**, latérale, sans flash
- **Minimum 3 vues par produit** : vue d'ensemble, détail de la couture ou du motif, mise en situation
- Format de sortie **WebP**, généré par Magento

Ne pas conditionner la mise en ligne au shooting professionnel : lancer avec les photos existantes recadrées au ratio retenu, puis remplacer progressivement. Le ratio fixé dès le départ est ce qui évitera d'avoir à tout reprendre plus tard.

## 7. Structure de la page d'accueil (issue de la maquette)

1. Header — navigation `Accueil · Boutique · À propos · Contact`, logo centré, panier à droite
2. Hero — grand visuel produit, nom de la marque, accroche, un CTA unique
3. **Nouveautés / Actualités** — exigence explicite de la cliente, à placer haut
4. « Les Incontournables » — 4 produits mis en avant
5. « L'histoire de Madame Aiguille » — photo de l'atelier + texte + lien vers À propos
6. « Pourquoi choisir Madame Aiguille » — 4 arguments illustrés
7. Bloc Instagram — 4 visuels + lien vers le compte
8. Newsletter — « Rejoignez l'univers Madame Aiguille » (sous réserve de validation)
9. Pied de page — mentions légales, CGV, contact, réseaux sociaux

> ⚠️ La maquette place la section « Les Incontournables » avant tout le reste et ne comporte pas de section Actualités. C'est un écart avec la demande explicite de la cliente au point 4 du questionnaire. L'ordre ci-dessus intègre les deux.

## 8. Points ouverts

1. Faire vectoriser le logo (SVG) et produire les déclinaisons carrée, horizontale et monochrome — **prérequis à l'intégration**
2. ~~Passage des boutons au brun rosé `#8A615C`~~ — **validé par Pierre** : les bonnes pratiques d'accessibilité priment. Reste à l'expliquer à Céline, la maquette d'ambiance montrant un rose plus clair
3. ~~Arbitrer les polices~~ — **arrêté** : Britney (titres) + Sentient (texte), ITF, auto-hébergées
4. Fixer le ratio d'image produit et le communiquer à Céline **avant** qu'elle ne prépare ses visuels
5. Valider l'ordre des sections de la page d'accueil, en particulier la place des Nouveautés
6. Vérifier que la maquette d'ambiance ne comporte pas de visuels sous licence à remplacer avant la mise en ligne
