# Spécification fonctionnelle — Site e-commerce "Madame Aiguille"

Document interne — v0.2

> **Mise à jour v0.2 (05/09/2026)** — Intégration du questionnaire préliminaire de Céline et de la maquette d'ambiance. Changements majeurs : modélisation catalogue revue (séries limitées, cf. §2), ajout de la section Actualités (§1.1), du bloc Instagram et de la newsletter (§1.4), ajout du virement et de la remise en main propre (§4), frais de port calculés au poids (§4).

Ce document détaille les parcours utilisateurs et les règles de gestion attendues. Il s'appuie sur les fonctionnalités natives de Magento Open Source ; l'objectif est de rester le plus proche possible du comportement standard Magento/Hyvä et de ne développer du sur-mesure que là où c'est nécessaire.

L'ambiance visuelle cible est décrite dans **charte-graphique.md** et illustrée par la maquette `a3ed1466-…png` fournie par la cliente.

## 1. Navigation & catalogue

### 1.0 Structure de navigation

D'après la maquette d'ambiance, l'arborescence principale reste volontairement minimale :

`Accueil` · `Boutique` · `À propos` · `Contact` — plus l'accès au panier à droite du header.

Le logo « Madame Aiguille » est **centré** dans le header (cf. maquette). Une adaptation du header Hyvä est donc à prévoir, celui-ci plaçant le logo à gauche par défaut.

### 1.1 Page d'accueil

Structure retenue, alignée sur la maquette d'ambiance :

1. **Hero** — visuel produit pleine largeur, nom de la marque, phrase d'accroche (« L'élégance cousue main » / « Créations textiles cousues avec amour ») et un CTA unique vers la boutique
2. **[NOUVEAU] Nouveautés / Actualités** — bloc **visible sans avoir à faire défiler jusqu'en bas**, présentant les dernières créations mises en ligne. Besoin explicitement formulé par la cliente : « que quand ils ouvrent le site ils voient directement une section Nouveautés/Actualités ». C'est le bloc le plus structurant de la page compte tenu du rythme de renouvellement des séries limitées
3. **Sélection / « Les Incontournables »** — 4 produits mis en avant
4. **« L'histoire de Madame Aiguille »** — photo de l'atelier + court texte + lien vers la page À propos
5. **« Pourquoi choisir Madame Aiguille »** — 4 arguments illustrés d'icônes (fait main, créations uniques, idéal à offrir, fabrication artisanale)
6. **Bloc Instagram** — 4 visuels renvoyant vers son compte
7. **Newsletter** — champ email + bouton (sous réserve de validation, cf. §1.4)

Implémentation : blocs CMS Magento + un ou deux widgets produit, stylés en Tailwind côté Hyvä. Objectif : que Céline puisse changer les produits mis en avant **sans toucher au code**.

#### Règle de gestion — bloc Nouveautés

- Alimenté automatiquement par un widget « Nouveaux produits » Magento (basé sur la date de création ou sur les dates `news_from_date`/`news_to_date`), et non par une sélection manuelle : cela évite que le bloc se périme si Céline ne le met pas à jour
- Les produits épuisés doivent être exclus de ce bloc (cf. §8)
- **[À trancher]** Le mot « Actualités » peut aussi désigner de l'information non-produit (participation à un marché, une brocante, une fermeture pour congés). À clarifier avec Céline : un simple bandeau/bloc CMS éditable suffirait, sans créer de blog

### 1.2 Pages catégories

- Liste des produits d'une catégorie, avec image, nom, prix
- Tri (prix croissant/décroissant, nouveautés)
- **Filtres à facettes : non prioritaires en v1.** Sur quelques dizaines de références dont beaucoup s'épuisent vite, des filtres élaborés apportent peu. À réévaluer si le catalogue grossit
- Pagination ou affichage complet selon le nombre de produits
- **[NOUVEAU]** Les produits épuisés doivent rester consultables mais visuellement identifiés (cf. §8) — arbitrage à valider avec la cliente

### 1.3 Recherche

- Recherche texte simple (nom/description), moteur Elasticsearch déjà disponible côté infra
- Page de résultats sur le même gabarit que les pages catégorie

### 1.4 Newsletter et réseaux sociaux **[NOUVEAU]**

- **Newsletter** : bloc d'inscription en bas de page d'accueil (présent dans la maquette). Module natif Magento (`Magento_Newsletter`). **Sous réserve de validation par la cliente** : implique un double opt-in RGPD, une politique de confidentialité à jour, et surtout une charge éditoriale récurrente qu'elle n'a pas évoquée
- **Instagram** : le bloc de la maquette est traité comme une **galerie d'images statique** (bloc CMS avec 4 visuels et lien vers le compte), et non comme un flux automatisé. Justification : les API Instagram imposent une authentification à renouveler et cassent régulièrement — coût de maintenance disproportionné ici. Céline met à jour ces 4 visuels lors de sa mise à jour mensuelle

## 2. Fiche produit **[RÉVISÉ]**

- Galerie photo avec zoom (plusieurs vues par création)
- Titre, description, prix
- **[NOUVEAU]** Indication du **nombre de pièces restantes** lorsque le stock est faible (ex. « Plus que 2 exemplaires ») — cohérent avec le modèle des séries limitées et efficace commercialement
- **[NOUVEAU]** Mention du caractère **série limitée** (« Série limitée — X pièces ») sur les fiches concernées
- Bouton d'ajout au panier avec sélecteur de quantité, plafonné au stock disponible
- Fil d'Ariane pour revenir à la catégorie
- **[NOUVEAU]** Lien contextuel vers le formulaire de contact : « Une question sur le tissu ou le motif ? » — c'est le canal retenu pour le choix de tissu (cf. §2.2)

### 2.1 Modélisation du catalogue

> ⚠️ **Révision par rapport à la v0.1.** La v0.1 opposait « créations à variantes » et « pièces uniques (stock = 1) ». Le questionnaire décrit en réalité des **séries limitées de 5 à 10 pièces**, avec au maximum **2 tailles** sur certains modèles. Le modèle est donc simplifié :

| Cas | Type Magento | Stock | Remarque |
|---|---|---|---|
| Modèle en série limitée, taille unique | **Produit simple** | 5 à 10 | Cas majoritaire |
| Modèle en série limitée, 2 formats/tailles | **Produit configurable** avec un seul attribut `taille` (2 valeurs) et 2 produits simples enfants | 5 à 10 par taille | Cas minoritaire |
| Pièce réellement unique | **Produit simple**, stock = 1 | 1 | Cas particulier, pas le modèle dominant |

Conséquences :

- **Un seul axe de variante en v1 : la taille.** Pas d'attribut `tissu` ni `couleur` configurable en fiche produit — le tissu se choisit par échange via le formulaire de contact
- Le nombre de produits configurables reste faible → charge de saisie limitée pour Céline, ce qui compte vu sa disponibilité
- Un attribut `serie_limitee` (booléen) et/ou `taille_serie` (entier) permet d'afficher automatiquement la mention « Série limitée » sans intervention manuelle
- **[NOUVEAU]** Un attribut **`poids`** doit être renseigné sur **chaque** produit : il conditionne le calcul automatique des frais de port (cf. §4). C'est une nouvelle contrainte de saisie, à faire figurer dans le guide back-office et à ne pas laisser optionnelle

### 2.2 Choix du tissu / du motif — via le formulaire de contact **[PRÉCISÉ]**

La cliente souhaitait « proposer une gamme de tissus que les clientes pourraient choisir au moment de passer commande », puis a elle-même orienté ce besoin vers **un formulaire de contact permettant de mettre en place une sélection de motifs et d'échanger plus facilement**.

Retenu pour la v1 :

- Une **page Contact** avec formulaire (nom, email, message, pièce jointe optionnelle pour une photo d'inspiration)
- Un **champ « produit concerné »** pré-rempli lorsque l'on arrive depuis une fiche produit, pour que Céline sache immédiatement de quoi on parle
- La demande part par email chez Céline, qui échange ensuite directement avec la cliente
- **Pas de sélecteur de tissu en fiche produit, pas de devis automatique, pas de paiement en ligne pour ces demandes en v1**

**[À trancher avec la cliente]** Une page « Nos tissus » présentant en photo la gamme disponible, référencée par un nom ou un numéro, rendrait l'échange bien plus efficace (la cliente écrirait « je voudrais le motif n°4 »). Faible coût, gros gain sur l'objectif « moins d'appels pour des renseignements basiques ».

Un configurateur en ligne (choix de tissu avec aperçu et devis automatique) reste **exclu du périmètre v1**.

## 3. Panier

- Ajout produit sans recharger la page (AJAX natif Magento/Hyvä)
- Mini-panier accessible depuis le header (Alpine.js), avec aperçu des articles et total
- Page panier complète : modification des quantités, suppression d'articles, récapitulatif du total
- Persistance du panier pour un visiteur non connecté (session) et pour un client connecté
- **[NOUVEAU]** Affichage de l'estimation des frais de port dès la page panier — important compte tenu d'un panier moyen de 10 à 20 € face à des frais de port de 5 à 8 €
- **[NOUVEAU]** Si un franco de port est retenu (cf. cahier des charges §11), afficher le montant restant pour en bénéficier
- Champ code promo — natif Magento, non prioritaire, activable plus tard

## 4. Tunnel de commande (checkout) **[RÉVISÉ]**

Checkout natif Magento retemplaté par Hyvä :

1. **Informations de livraison** — adresse, sélection du mode de livraison et de son coût
2. **Paiement** — sélection du moyen de paiement
3. **Récapitulatif et validation** — relecture, acceptation des CGV, validation finale

### 4.1 Modes de livraison

Livraison **France métropolitaine uniquement** en v1. Trois transporteurs **confirmés** par la cliente, plus un mode de retrait :

| Mode | Implémentation v1 | Points d'attention |
|---|---|---|
| **Colissimo** (domicile) | Table rates Magento, paliers de poids | — |
| **Mondial Relay** (point relais) | Table rates + **widget de sélection de point relais** | Nécessite un module tiers compatible Hyvä — c'est le point le plus coûteux du lot |
| **Chronopost** (rapide) | Table rates, paliers de poids | Confirmé par la cliente (n'était qu'« éventuel » en v0.1) |
| **[NOUVEAU] Remise en main propre** | Méthode d'expédition à 0 € | Modalités (lieu, créneaux, périmètre) à cadrer avec la cliente. À restreindre par code postal si elle ne veut pas la proposer à toute la France |

### 4.2 Calcul des frais de port **[NOUVEAU — souhait explicite de la cliente]**

> « Le mieux serait effectivement de calculer les frais de port automatiquement selon le poids. »

- Retenu : **table rates Magento configurées par palier de poids** (condition `Weight vs. Destination`), par transporteur. Cela répond au besoin **sans API transporteur temps réel**, plus simple à livrer et à maintenir
- **Prérequis bloquant** : le poids doit être renseigné sur chaque produit (cf. §2.1). Un produit sans poids fausse le calcul → à contrôler en recette
- Les paliers doivent être calés sur les grilles tarifaires réelles des trois transporteurs, en intégrant le poids de l'emballage
- **[À trancher]** Franco de port à partir d'un montant

### 4.3 Moyens de paiement **[RÉVISÉ]**

| Moyen | Implémentation | Règle de gestion |
|---|---|---|
| **Carte bancaire** | Module Stripe officiel | Aucune donnée bancaire ne transite par le serveur ; commande validée automatiquement après paiement accepté |
| **[NOUVEAU] Virement bancaire** | Natif Magento (« Bank Transfer Payment ») | La commande est créée en statut **« En attente de paiement »**. Les coordonnées bancaires et une **référence de commande à rappeler** sont affichées à la validation **et** dans l'email de confirmation. Le stock est réservé. **[À trancher]** délai au-delà duquel une commande non réglée est annulée et le stock relibéré — indispensable avec des séries limitées |
| **[NOUVEAU] Remise en main propre** | Paiement hors ligne, associé au mode de retrait | Commande créée en attente, réglée lors de la remise |
| PayPal | Non retenu | — |

### 4.4 Règles de gestion

- **[À confirmer]** Achat en tant qu'invité (guest checkout) autorisé ou compte obligatoire. Recommandation : autoriser le guest checkout, la friction d'inscription étant disproportionnée pour un panier de 15 €
- **[NOUVEAU]** Régime fiscal de la cliente à vérifier : si elle est en franchise en base de TVA (probable pour une activité de cette taille exercée en complément), les prix s'affichent sans TVA et la mention « TVA non applicable, art. 293 B du CGI » est obligatoire sur les factures. Cela se paramètre côté Magento et doit être tranché **avant** la première vente
- Email de confirmation de commande envoyé automatiquement
- Gestion des échecs de paiement : message clair, panier conservé, possibilité de réessayer

## 5. Compte client

- Création de compte (email/mot de passe), avec option de création pendant le checkout
- Réinitialisation de mot de passe (email natif Magento)
- Historique des commandes avec statut — **[NOUVEAU]** les statuts doivent couvrir le cas du virement : *en attente de paiement*, *paiement reçu*, *en préparation*, *expédiée*, *prête pour retrait*, *livrée*
- Gestion des adresses (ajout/modification/suppression, adresse par défaut)
- Modification des informations personnelles

## 6. Back-office (gestion quotidienne par Céline)

Objectif : restreindre l'accès admin Magento à ce dont elle a réellement besoin, via les rôles/ACL. Contrainte forte : **elle a une autre activité professionnelle** et prévoit une mise à jour mensuelle — chaque écran superflu est un frein.

Accès prévus pour son rôle :

- **Catalogue** : création/modification de produits, photos, stocks, **poids**
- **Ventes** : consultation et traitement des commandes, mise à jour des statuts d'expédition, **[NOUVEAU]** passage manuel d'une commande en « payée » à réception d'un virement
- **Clients** : consultation basique
- **[NOUVEAU] Contenu (CMS)** : édition du bloc Actualités et des visuels Instagram, sans accès aux thèmes ni aux widgets de mise en page

Accès masqués (réservés à Pierre) : configuration système, modules/extensions, réglages paiement/livraison, utilisateurs admin, développeur/API.

**[NOUVEAU — recommandation]** Vu le rythme mensuel annoncé face à des séries de 5 à 10 pièces qui peuvent s'épuiser plus vite, prévoir une **notification email automatique de stock bas** (native Magento) pour qu'elle n'ait pas à surveiller le site.

> **[À FAIRE EN AVAL]** Guide utilisateur illustré : ajouter un produit (avec le poids), gérer un stock, traiter une commande, encaisser un virement, mettre à jour le bloc Actualités.

## 7. Emails transactionnels

- Confirmation de commande — **[NOUVEAU]** version spécifique pour le virement, avec RIB et référence à rappeler
- **[NOUVEAU]** Confirmation de réception du paiement (virement encaissé)
- Confirmation d'expédition
- **[NOUVEAU]** « Votre commande est prête pour le retrait » (remise en main propre)
- Bienvenue à la création de compte
- Réinitialisation de mot de passe
- **[NOUVEAU]** Accusé de réception du formulaire de contact, côté visiteur

Templates Magento natifs, personnalisés visuellement (logo, couleurs de la marque — cf. charte graphique) sans développement spécifique.

## 8. Gestion des stocks **[RÉVISÉ]**

- **Séries limitées** : stock initial de 5 à 10 pièces, décrémenté à chaque vente. Une série épuisée n'est **pas** automatiquement réapprovisionnée — elle le sera seulement si Céline retrouve le même tissu, ce qui est l'exception
- **Produit épuisé** : **[À trancher avec la cliente]** deux options
  - *Rester visible, marqué « Épuisé »*, non commandable → conserve le référencement, montre le savoir-faire, et alimente la sensation de rareté. **Recommandé**, à condition d'exclure ces produits du bloc Nouveautés et de les reléguer en fin de liste dans les catégories
  - *Être masqué du catalogue* → catalogue toujours « propre », mais pages perdues et travail de dépublication récurrent
- Pièce unique : stock = 1, comportement natif
- **[NOUVEAU]** Le stock doit être **réservé** dès la création d'une commande en attente de virement, et **relibéré** si le virement n'arrive pas dans le délai retenu
- Pas de gestion multi-entrepôt

## 9. Cas limites à couvrir en recette

- Commande d'un produit épuisé entre l'ajout au panier et le paiement
- Deux visiteuses achètent la dernière pièce d'une série au même moment : seul le premier paiement validé aboutit, l'autre est informée clairement **avant** la validation finale
- Échec ou abandon du paiement en cours de tunnel
- **[NOUVEAU]** Commande par virement jamais réglée : que devient le stock réservé ?
- **[NOUVEAU]** Produit sans poids renseigné : le calcul des frais de port ne doit pas échouer silencieusement ni afficher 0 €
- **[NOUVEAU]** Panier mêlant un article livrable et le mode « remise en main propre »
- Adresse de livraison hors France métropolitaine
- Connexion pendant un panier invité (fusion du panier)
- Session expirée pendant le tunnel de commande
