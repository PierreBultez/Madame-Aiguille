Tu vas produire les déclinaisons vectorielles du logo de **Madame Aiguille**, en SVG. Lis tout le brief avant d'écrire la moindre ligne.

---

# 1. Contexte

**Madame Aiguille** est la marque de Céline, créatrice indépendante française qui coud à la main des accessoires textiles : trousses, pochettes, cotons démaquillants, petits sacs. Univers **délicat, élégant, attentionné**. Cible : femmes de 20 à 45 ans, sensibles au fait-main.

Un logo existe déjà, dessiné pour elle. Il n'est disponible qu'en PNG et il est **trop détaillé pour les petites tailles**. Ta mission n'est pas de le réinventer : c'est de le **traduire en vectoriel** et d'en produire les déclinaisons qui manquent.

## Le logo existant

Composition circulaire, en trois couches :

1. **Un cercle extérieur** évoquant un tambour à broder, tracé en nude, ouvert en haut où passe une aiguille
2. **Un bouton de couture** au centre : disque rose poudré, **quatre trous** disposés en carré, cerné d'un liseré pointillé
3. **Un fil** qui traverse deux des quatre trous en diagonale et retombe en boucle souple sous le bouton
4. Deux **brindilles feuillues** fines, en brun, de part et d'autre du bouton à l'intérieur du disque
5. **« Madame Aiguille »** en script, arqué au-dessus
6. **« L'ÉLÉGANCE COUSUE MAIN »** en capitales espacées, arqué en dessous

Les éléments 4, 5 et 6 sont précisément ce qui devient illisible en petit.

## Palette — valeurs exactes, à ne pas réinterpréter

| Token | Hex | Rôle dans le logo |
|---|---|---|
| `brand` | `#8A615C` | Brun rosé — trait principal, texte, brindilles |
| `nude` | `#E9C3AD` | Cercle extérieur, fil, aiguille |
| `blush` | `#F4E7E7` | Remplissage du bouton |
| `paper` | `#FFFCFA` | Fond ivoire (transparent dans les fichiers livrés) |

⚠️ **Contrainte de lisibilité, pas de goût :** le nude `#E9C3AD` sur fond ivoire ne présente qu'un rapport de contraste de **1,6:1**. Il disparaît en petit. Dans toutes les variantes destinées à moins de 96 px, **le trait dominant doit être le brun `#8A615C`**, le nude n'étant plus qu'un accent secondaire — voire supprimé.

## Typographie

Le mot « Madame Aiguille » se compose en **Britney Regular** (Indian Type Foundry). Les fichiers sont dans `fonts/Britney_Complete/`. Britney est une display italique à très fort contraste, d'esprit didone.

La baseline « L'ÉLÉGANCE COUSUE MAIN » se compose en **Sentient** (`fonts/Sentient_Complete/`), en capitales, avec un interlettrage large (`letter-spacing` d'environ 0,18 em).

---

# 2. Ce que tu dois produire

Six fichiers SVG, plus une planche de contrôle. Chaque fichier est autonome et optimisé.

## 2.1 `logo-mark.svg` — marque carrée, détail complet

`viewBox="0 0 512 512"`. Pour l'avatar des réseaux sociaux, le tampon sur les emballages, l'icône d'application.

- Reprend le tambour, le bouton à quatre trous, le liseré pointillé, le fil et l'aiguille
- **Sans aucun texte** : ni le nom, ni la baseline
- Les brindilles feuillues sont **simplifiées** : trois à quatre feuilles par brindille au lieu du foisonnement actuel, et un trait plus épais
- Marge intérieure d'au moins 8 % de la largeur autour du dessin

## 2.2 `logo-mark-simple.svg` — marque carrée simplifiée

`viewBox="0 0 512 512"`. Pour l'affichage entre **32 et 96 px** : header mobile, pastille de compte client, en-tête d'email.

- **Supprime** les brindilles et le liseré pointillé
- **Conserve** le tambour, le bouton à quatre trous, le fil et l'aiguille
- Épaissit les traits : à 32 px, un trait fin devient invisible ou se réduit à un gris sale
- Le trait dominant passe en `brand` `#8A615C` ; le nude ne subsiste que sur le fil

## 2.3 `favicon.svg` — marque ultra-réduite

`viewBox="0 0 32 32"`, pensé pour **16 px**. Redessiné à cette taille, pas réduit depuis la version précédente.

- **Le bouton seul** : disque plein `#8A615C` et quatre trous évidés
- Ni tambour, ni fil, ni aiguille, ni pointillé
- Deux couleurs maximum, aucun trait de moins de 1,5 px à 32 px
- Test à passer : reconnaissable à 16 px, dans un onglet, à côté d'une douzaine d'autres

## 2.4 `logo-horizontal.svg` — verrou horizontal complet

`viewBox="0 0 900 240"` environ, à ajuster au dessin réel. **C'est la version qui manque le plus** : header desktop, en-tête d'email transactionnel, facture, papeterie.

- **À gauche** : la marque de `logo-mark-simple.svg`, alignée sur la hauteur d'œil du texte
- **À droite**, sur deux lignes :
  - « Madame Aiguille » en **Britney Regular**, `#8A615C`, sur **une seule ligne, sans arc**
  - « L'ÉLÉGANCE COUSUE MAIN » en **Sentient**, capitales, `letter-spacing: 0.18em`, `#8A615C` — et non en nude, pour rester lisible
- Le texte n'est **pas** arqué : l'arc appartient à la version circulaire, il n'a pas de sens sur une ligne
- Aligner optiquement, pas mathématiquement : l'italique de Britney demande un léger décalage

## 2.5 `logo-horizontal-compact.svg` — verrou horizontal réduit

Même construction **sans la baseline**, pour le header mobile où la hauteur est comptée. `viewBox="0 0 700 160"` environ.

## 2.6 `logo-mono.svg` — version monochrome

Le verrou horizontal complet, entièrement en `fill="currentColor"` et `stroke="currentColor"`, **sans aucune couleur codée en dur**. Il doit fonctionner posé sur clair comme sur foncé, en héritant la couleur de son contexte CSS. Usage : filigranes, impression une couleur, marquage sur emballage.

## 2.7 `planche-logos.html` — planche de contrôle

Une page HTML autonome qui affiche **toutes** les variantes :

- aux tailles réelles d'usage : 512, 180, 96, 64, 48, 32, 24, 16 px
- sur **trois fonds** : ivoire `#FFFCFA`, blush `#F4E7E7`, et brun `#8A615C` pour valider la version monochrome
- avec, pour chaque variante, son nom de fichier, son poids en octets et son usage prévu

Cette planche est l'outil de validation. Si une variante y est illisible, elle est à refaire.

---

# 3. Contraintes techniques SVG — impératives

- **`viewBox` sur chaque fichier, jamais d'attributs `width`/`height` fixes.** Le dimensionnement se fait en CSS
- **Aplats uniquement.** Aucun dégradé, aucun `<filter>`, aucune ombre portée. L'univers de la marque est mat et papier ; en plus, ces effets se dégradent mal en petit et alourdissent le fichier
- **Aucune image raster embarquée**, aucun `<image>`, aucune donnée en base64
- **Aucun `<text>` dans les fichiers finaux** hormis la mention prévue en §4 : les tracés sont convertis, pour ne dépendre d'aucune fonte installée
- **Traits arrondis** : `stroke-linecap="round"`, `stroke-linejoin="round"` — cohérent avec le geste cousu main
- **Épaisseur de trait minimale : 1,5 % de la largeur du `viewBox`.** En dessous, le trait s'efface à l'affichage réduit
- **Identifiants préfixés** (`ma-logo-…`) : ces SVG seront inlinés plusieurs fois dans une même page, des `id` génériques créeraient des collisions
- **Accessibilité** : `role="img"`, un `<title>` explicite, et `aria-hidden="true"` sur les variantes purement décoratives accompagnées d'un texte alternatif
- **Optimisation** : aucune métadonnée d'éditeur, coordonnées à deux décimales maximum, `<g>` inutiles supprimés. **Objectif : moins de 4 Ko par fichier**, favicon sous 1 Ko
- **Fond transparent.** Le logo ne porte jamais son propre fond ivoire
- **Zone de respiration** : une marge égale au diamètre du bouton tout autour, intégrée au `viewBox`

---

# 4. Le texte en Britney — procédure

Tu ne disposes pas des tracés de Britney. Procède donc en deux temps, et livre les deux états :

1. **Version de travail** — `logo-horizontal-text.svg` : le texte reste un `<text>` avec `font-family:'Britney'`, accompagné d'une déclaration `@font-face` pointant vers le `.woff2` local. Cette version sert au calage (taille, interlettrage, alignement optique) et se prévisualise dans la planche HTML
2. **Version finale** — `logo-horizontal.svg` : les mêmes tracés, texte converti en chemins

Fournis la commande de conversion à exécuter (Inkscape 1.x) :

```bash
inkscape logo-horizontal-text.svg \
  --export-type=svg --export-text-to-path \
  --export-filename=logo-horizontal.svg
```

Précise dans ta livraison que **la conversion doit être refaite** à chaque modification du texte, et que la version de travail est celle qu'on édite.

⚠️ Les fontes sont sous ITF Free Font License, qui **interdit le subsetting et la conversion de format**. La conversion d'un texte en tracés dans un logo n'est pas concernée : elle produit un dessin, pas un fichier de fonte. Ne cherche pas à générer un `.woff2` allégé.

---

# 5. Interdits

- ❌ Réinventer le logo : on le traduit et on le simplifie, on ne le remplace pas
- ❌ Du nude `#E9C3AD` comme couleur principale sur une variante destinée à moins de 96 px
- ❌ Des dégradés, des ombres, des effets de brillance ou de métal
- ❌ Du texte arqué sur les versions horizontales
- ❌ Une baseline conservée sur une variante de moins de 120 px de large : elle y est illisible
- ❌ Une réduction mécanique du dessin complet pour faire le favicon : il se redessine
- ❌ Des `<path>` de plusieurs milliers de points issus d'un vectoriseur automatique : le dessin est géométrique, il se construit en cercles, arcs et courbes de Bézier propres
- ❌ Des `id` non préfixés

---

# 6. Critère de réussite

Ouvre la planche de contrôle et regarde la colonne 16 px. Si le favicon y est une tache brune indistincte, recommence. Si le verrou horizontal à 32 px de haut laisse deviner « Madame Aiguille », c'est gagné.

Livre ensuite un court récapitulatif : quel fichier pour quel usage, et à partir de quelle taille basculer de l'un à l'autre.
