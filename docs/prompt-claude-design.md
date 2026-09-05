Tu vas créer le design system et les maquettes du site e-commerce **Madame Aiguille**. Lis l'intégralité de ce brief avant de commencer à dessiner.

---

# 1. Contexte

**Madame Aiguille** est la marque de Céline, créatrice indépendante française qui imagine et coud elle-même des accessoires textiles : trousses de toilette, pochettes, pochettes à livre, cotons démaquillants lavables, petits sacs. Elle vend aujourd'hui sur les réseaux sociaux, sur Vinted Pro et sur des marchés et brocantes. Ce site est son premier vrai canal de vente autonome.

**Modèle économique structurant : les séries limitées.** Chaque modèle est produit à **5 à 10 exemplaires maximum**, parce que le stock de tissu est limité et parce qu'elle aime changer souvent de motif. Le catalogue tourne donc vite, et la rareté est un argument de vente réel, pas un artifice marketing. Le design doit rendre cette rotation visible et désirable.

- **Gamme de prix** : 2 € à 90 €, cœur de gamme **10-20 €**. Panier moyen bas → les frais de port pèsent lourd dans la décision d'achat, il faut les rendre transparents tôt.
- **Cible** : femmes de 20 à 45 ans, sensibles au fait-main et au DIY — jeunes mamans, amatrices de mode éthique, entrepreneuses.
- **La marque en trois mots, par la créatrice** : *délicate, élégante, attentionnée*.
- **Positionnement produit** : « accessible et coloré ».
- **Trafic majoritairement mobile** (acquisition via Instagram). **Conçois mobile d'abord.**

**Tension à arbitrer, et voici l'arbitrage retenu** : l'univers visuel est feutré et romantique, mais le positionnement revendiqué est « accessible et coloré ». La palette neutre sert d'**écrin** ; la couleur vient **des photos de tissus à motifs**. Ne charge pas l'interface en couleur — laisse les produits la porter.

**Contrainte d'implémentation** : le site sera développé sous **Magento Open Source avec le thème Hyvä (Tailwind CSS + Alpine.js)**. Les maquettes doivent être réalisables avec Tailwind sans acrobaties : grilles simples, échelle d'espacement régulière, pas d'effets exotiques. Le tunnel de commande doit rester **très proche de la structure native du checkout Magento** — on retemplate, on ne reconstruit pas.

---

# 2. Design system

Commence par un artboard « Design System » qui documente visuellement tous les tokens et composants ci-dessous, avant les maquettes de pages.

## 2.1 Couleurs

Valeurs relevées dans le logo et la maquette d'ambiance réels de la marque. **Utilise ces valeurs exactes, ne les réinterprète pas.**

| Token | Hex | Rôle |
|---|---|---|
| `brand` | `#8A615C` | Couleur de marque — boutons principaux, titres, liens, prix |
| `brand-dark` | `#6E4D49` | Survol et état actif des boutons |
| `brand-rose` | `#C3867A` | Accent **décoratif uniquement** — filets, icônes, aplats |
| `brand-nude` | `#E9C3AD` | Filets, séparateurs, ornements, bordures |
| `brand-blush` | `#F4E7E7` | Fonds de section, cartes produit |
| `brand-ivory` | `#FAF0EB` | Fond de page par défaut |
| `brand-paper` | `#FFFCFA` | Fond des cartes et du header |
| `brand-ink` | `#5F4A41` | Corps de texte |

Couleurs d'état, à décliner dans la même famille chaude (pas de rouge/vert saturés qui jureraient) : succès, erreur, information. Elles doivent atteindre **4,5:1** sur les fonds clairs.

### Règle d'accessibilité — non négociable

Le site doit respecter **WCAG 2.1 niveau AA**. C'est une exigence explicite du commanditaire.

- ❌ **`brand-rose` (`#C3867A`) ne porte jamais de texte** : 3,0:1 avec du blanc, 2,9:1 sur ivoire. Insuffisant.
- ❌ **`brand-nude` (`#E9C3AD`) est purement décoratif** : 1,6:1.
- ✅ Boutons principaux : **fond `brand` `#8A615C` + texte blanc** = 5,3:1.
- ✅ Corps de texte : **`brand-ink` `#5F4A41` sur ivoire** = 8,1:1.
- ✅ Titres et liens : **`brand` `#8A615C` sur ivoire** = 5,2:1.

Vérifie chaque combinaison texte/fond que tu produis. Tout état de focus doit être visible au clavier (anneau de focus à 3:1 minimum contre l'arrière-plan). Toute cible tactile fait **44 × 44 px minimum**.

## 2.2 Typographie

Deux familles, toutes deux d'Indian Type Foundry, auto-hébergées en production.

| Rôle | Fonte de production | **Substitut à utiliser dans les maquettes** |
|---|---|---|
| Titres, accroches | **Britney** Regular — display italique à très fort contraste, esprit didone | `Bodoni Moda`, italique, graisse 400 |
| Corps de texte, interface, formulaires | **Sentient** — serif de labeur, Light/Regular/Medium + italiques | `Spectral`, graisses 300/400/500 |

Les fichiers de production ne peuvent pas être intégrés ici (licence). **Utilise les substituts ci-dessus**, qui ont une couleur typographique très proche, et **nomme les tokens `--font-display` et `--font-body`** en plaçant les vraies fontes en tête de pile :

```css
--font-display: 'Britney', 'Bodoni Moda', Didot, Georgia, serif;
--font-body: 'Sentient', 'Spectral', 'Iowan Old Style', Georgia, serif;
```

### Échelle typographique

| Usage | Fonte | Mobile / Desktop |
|---|---|---|
| Titre de hero | display | 40 / 64 px |
| Titres de section (H2) | display | 30 / 40 px |
| H1 de page, nom du produit en fiche | body Medium | 26 / 32 px |
| H3, nom du produit en vignette | body Medium | 18 / 20 px |
| Corps de texte | body Regular | **16 px plancher** — interlignage 1,6 |
| Accroches, citations | body Italic | 16 / 18 px |
| Prix | body Medium | 18 / 20 px |
| Boutons, labels | body Medium | 15 / 16 px |
| Mentions légales, aides de saisie | body Regular | **13 px plancher** |

### Règles typographiques — impératives

- **La fonte display ne descend jamais sous 30 px.** En dessous elle devient illisible et ses accents disparaissent. Aucune exception.
- **Aucun texte d'interface en display** : ni bouton, ni label, ni prix, ni élément de checkout, ni ligne de tableau.
- La fonte display est **déjà italique par dessin** — ne lui applique pas d'italique supplémentaire.
- Le nom « Madame Aiguille » dans le header est **une image du logo**, pas du texte stylé.
- Textes en **français**, avec la typographie française correcte : espace insécable avant `: ; ? !`, guillemets `« »`, prix au format `29,00 €`.

## 2.3 Logo

Composition circulaire : « Madame Aiguille » en script arqué au-dessus d'un bouton de couture traversé d'un fil et d'une aiguille, avec « L'ÉLÉGANCE COUSUE MAIN » en capitales espacées arquées en dessous. Palette : brun rosé `#8A615C`, nude `#E9C3AD`, rose poudré `#F4E7E7`.

Dans les maquettes, représente-le sobrement (un cercle stylisé avec le bouton et le nom). Prévois **deux verrous** :

- une **version horizontale** pour le header et les emails — icône à gauche, nom sur une ligne ;
- une **version carrée réduite sans baseline** pour mobile et favicon (la baseline arquée est illisible sous 120 px).

Le logo est **centré** dans le header desktop.

## 2.4 Ornements

Vocabulaire décoratif issu de l'univers de la marque, à utiliser **avec retenue** :

- filets ondulés de part et d'autre des titres de section : `~ Les Incontournables ~`
- petits cœurs en séparateur
- bordures festonnées entre les sections (effet dentelle)
- fonds texturés très légers (papier, floral en filigrane) à **5-10 % d'opacité maximum**
- branchages fins dans le nude

Ces ornements ne doivent jamais concurrencer les photos produit ni gêner la lecture.

## 2.5 Composants à documenter

Dessine chaque composant avec **tous ses états** (repos, survol, focus clavier, actif, désactivé, chargement, erreur) :

- **Boutons** : principal, secondaire, tertiaire/lien, en trois tailles
- **Champs de formulaire** : texte, sélecteur, case à cocher, bouton radio, zone de texte, champ fichier — avec label, aide de saisie, état d'erreur et message d'erreur
- **Carte produit** : image en ratio **4:5**, nom, prix, badge de série limitée, état épuisé
- **Badges** : « Série limitée — 8 pièces », « Nouveauté », « Plus que 2 exemplaires », « Épuisé »
- **Fil d'Ariane**
- **Pagination**
- **Sélecteur de quantité**
- **Sélecteur de taille** (2 options maximum)
- **Alertes** : succès, erreur, information
- **Bloc de mise en avant** de section, avec filet ornemental
- **Header** : desktop et mobile (menu burger)
- **Footer**
- **Mini-panier** en tiroir latéral
- **État vide** : panier vide, aucun résultat de recherche

Documente aussi l'**échelle d'espacement** (base 4 px), les **rayons de bordure**, les **ombres** (très douces — l'univers est mat et papier, pas glossy) et les **points de rupture responsive**.

---

# 3. Maquettes à produire

Produis les artboards suivants, dans cet ordre. **Desktop 1440 px de large, mobile 390 px.**

1. Design System
2. Accueil — desktop
3. Accueil — mobile
4. Page catégorie — desktop
5. Fiche produit — desktop
6. Fiche produit — mobile
7. Panier — desktop
8. Checkout — desktop
9. Checkout — mobile
10. Compte client — desktop

## 3.1 Accueil

Ordre des sections **imposé** :

1. **Header** — navigation `Accueil · Boutique · À propos · Contact`, logo **centré**, panier à droite avec compteur
2. **Hero** — grand visuel produit, nom de la marque, accroche « L'élégance cousue main », sous-accroche « Créations textiles cousues avec amour », **un seul** bouton d'appel à l'action vers la boutique
3. **« Nouveautés »** — ⚠️ **section la plus importante de la page**, exigence explicite de la créatrice : le visiteur doit voir immédiatement ce qui est nouveau, **sans défiler longuement**. Sur mobile, elle doit être atteignable en un seul défilement. 4 produits récents, chacun avec son badge de série limitée
4. **« Les Incontournables »** — 4 produits mis en avant. Exemples réels à utiliser : *Trousse Romantique 29,00 €*, *Cotons Démaquillants 12,00 €*, *Pochette à Livre Isabelle 24,00 €*, *Sac Aurora Mini 32,00 €*
5. **« L'histoire de Madame Aiguille »** — photo de la créatrice à sa machine dans son atelier, court texte, lien vers la page À propos
6. **« Pourquoi choisir Madame Aiguille »** — 4 arguments avec icônes fines : *Fait main avec amour · Créations uniques · Idéal à offrir · Fabrication artisanale*
7. **Instagram** — 4 visuels + lien vers le compte `@madameaiguille`
8. **Newsletter** — « Rejoignez l'univers Madame Aiguille », champ email + bouton
9. **Footer** — mentions légales, CGV, politique de confidentialité, contact, réseaux sociaux, moyens de paiement acceptés

## 3.2 Page catégorie

- Fil d'Ariane, titre de catégorie, court texte d'introduction
- Grille de produits : **2 colonnes sur mobile, 3 à 4 sur desktop**, images en ratio 4:5
- Tri : nouveautés, prix croissant, prix décroissant. **Pas de filtres à facettes** — le catalogue ne compte que quelques dizaines de références, des filtres élaborés seraient du bruit
- Les **produits épuisés restent visibles**, visuellement atténués, badge « Épuisé », **relégués en fin de liste** et non commandables
- Pagination
- Prévois l'**état vide** (catégorie sans produit)

## 3.3 Fiche produit

- Galerie : **3 vues minimum** (vue d'ensemble, détail de la couture ou du motif, mise en situation), zoom, miniatures. Sur mobile : carrousel à défilement horizontal avec indicateurs
- Nom du produit, prix, **badge « Série limitée — 8 pièces »**
- **Indicateur de rareté** quand le stock est faible : « Plus que 2 exemplaires ». Doit être perceptible sans être anxiogène ni clignotant — la marque est délicate, pas agressive
- **Sélecteur de taille** — uniquement sur les modèles concernés, **2 options maximum**. Prévois aussi la variante de fiche **sans** sélecteur (cas majoritaire)
- Sélecteur de quantité **plafonné au stock réel**
- Bouton « Ajouter au panier », pleine largeur sur mobile
- Description, composition, dimensions, **conseils d'entretien**
- ⚠️ **Encart de mise en relation** : « Une question sur le tissu ou le motif ? » avec lien vers le formulaire de contact. C'est le mécanisme retenu pour la personnalisation — il n'y a **pas** de sélecteur de tissu en ligne. Cet encart compte, ne le traite pas comme un détail
- Rassurance : délais d'expédition, modes de livraison, paiement sécurisé
- Suggestions : « Vous aimerez aussi »
- Prévois l'**état épuisé** de la fiche

## 3.4 Panier

- Liste des articles : miniature, nom, taille le cas échéant, prix unitaire, quantité modifiable, sous-total, suppression
- **Estimation des frais de port affichée dès cette page** — indispensable avec un panier moyen de 10-20 € face à des frais de port de 5 à 8 €
- Barre de progression vers la **livraison offerte** : « Plus que 12,00 € pour la livraison offerte »
- Récapitulatif : sous-total, frais de port, total
- Champ code promo, replié par défaut
- Bouton « Passer commande » bien dominant
- Lien « Continuer mes achats »
- **État vide du panier**, avec renvoi vers la boutique

## 3.5 Checkout

Reste très proche de la **structure native du checkout Magento** : deux étapes, avec un récapitulatif de commande persistant à droite sur desktop et repliable en haut sur mobile.

**Étape 1 — Livraison**

- Choix entre commande en tant qu'invité et connexion
- Formulaire d'adresse français : prénom, nom, adresse, complément, code postal, ville, téléphone, email
- **Quatre modes de livraison**, chacun avec son tarif et son délai :
  - **Colissimo** — à domicile
  - **Mondial Relay** — point relais, avec un **bouton « Choisir un point relais »** ouvrant un sélecteur (carte + liste). Prévois l'état « point relais sélectionné », qui affiche l'adresse retenue
  - **Chronopost** — livraison rapide
  - **Remise en main propre** — 0 €, avec une note sur les modalités de retrait
- Mention que les **frais de port sont calculés selon le poids** de la commande

**Étape 2 — Paiement**

- **Carte bancaire** (Stripe) — champs sécurisés
- **Virement bancaire** — ⚠️ écran spécifique important : il faut afficher les coordonnées bancaires **et une référence de commande à rappeler**, plus une explication claire du fait que la commande n'est préparée qu'à réception des fonds, et que **le stock est réservé en attendant**. Sur une série de 8 pièces, cette information est cruciale pour le client
- **Paiement à la remise en main propre**
- Case d'acceptation des CGV
- Récapitulatif final et bouton de validation

Prévois aussi :

- **Page de confirmation de commande**, en deux versions : paiement par carte (commande confirmée) et paiement par virement (en attente de règlement, avec rappel du RIB et de la référence)
- **État d'erreur de paiement** : message clair, panier conservé, invitation à réessayer

Le checkout doit être **le plus sobre du site** : ornements réduits au minimum, aucune distraction, navigation allégée. C'est là que la confiance se gagne ou se perd.

## 3.6 Compte client

Navigation latérale sur desktop, en accordéon sur mobile :

- **Tableau de bord** — dernière commande, adresse par défaut
- **Mes commandes** — liste avec statuts. Les statuts doivent couvrir le cas du virement : *en attente de paiement*, *paiement reçu*, *en préparation*, *expédiée*, *prête pour retrait*, *livrée*. Chaque statut a son traitement visuel distinct
- **Détail d'une commande** — articles, adresse, mode de livraison, mode de paiement, suivi
- **Mes adresses** — liste, ajout, modification, adresse par défaut
- **Mes informations** — email, mot de passe
- Prévois les écrans de **connexion**, de **création de compte** et de **mot de passe oublié**
- Prévois l'**état vide** : « Vous n'avez pas encore passé de commande »

---

# 4. Exigences transverses

- **Mobile d'abord.** Le trafic viendra d'Instagram. Si un écran ne fonctionne pas sur 390 px, il ne fonctionne pas.
- **Photos** : ratio **4:5** partout, fond neutre et constant (lin, bois clair, textile ivoire), lumière naturelle latérale. C'est l'homogénéité du fond qui fait la cohérence d'une grille de catalogue, bien plus que la qualité de chaque photo prise isolément. Les photos actuelles de la créatrice sont d'un niveau « réseaux sociaux » — le design ne doit pas dépendre d'une photographie parfaite.
- **Contenu réel en français**, jamais de faux latin. Utilise les vrais noms de produits donnés plus haut, de vrais prix, de vrais libellés.
- **Aucun texte en dessous des planchers de taille** définis en 2.2.
- **Chaque état compte** : vide, chargement, erreur, épuisé, désactivé. Un design qui ne montre que le cas nominal n'est pas exploitable pour le développement.
- **Réalisable sous Tailwind** : espacements réguliers, grilles simples, pas d'effets impossibles à reproduire proprement.
- **Pas de carrousel automatique** sur le hero — mauvais pour l'accessibilité comme pour la conversion.

---

# 5. Ce qu'il ne faut pas faire

- ❌ Du texte blanc sur `#C3867A` — contraste insuffisant, c'est l'erreur exacte que contient la maquette d'ambiance existante
- ❌ La fonte display sous 30 px, ou dans un bouton, un label, un prix, un tableau
- ❌ Un sélecteur de tissu ou de motif en fiche produit — cela passe par le formulaire de contact
- ❌ Des filtres à facettes élaborés sur les pages catégorie
- ❌ Un checkout reconstruit qui s'écarterait de la structure native de Magento
- ❌ Des ornements ou des fonds texturés qui concurrencent les photos produit
- ❌ Du faux latin, des prix inventés incohérents avec la gamme 2-90 €
- ❌ Une section « Nouveautés » reléguée en bas de page d'accueil

---

Commence par l'artboard Design System, puis enchaîne les pages dans l'ordre donné. Si un arbitrage te semble nécessaire en cours de route, tranche dans le sens de la lisibilité et de la sobriété — c'est un site marchand tenu par une créatrice seule, pas une vitrine de studio.
