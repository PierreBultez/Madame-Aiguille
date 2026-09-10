# Cahier des charges — Site e-commerce "Madame Aiguille"

Document interne — v0.2 (brouillon de travail, non destiné à être envoyé tel quel à la cliente)

> **Mise à jour v0.2 (05/09/2026)** — Intégration du questionnaire préliminaire rempli par Céline, du logo fourni et de la maquette d'ambiance. Les éléments marqués **[NOUVEAU]** proviennent de ces sources. Un point de fond a changé : le catalogue n'est **pas** constitué de pièces uniques mais de **séries limitées de 5 à 10 pièces** (cf. §4).

## 1. Contexte

Madame Aiguille est la marque de Céline, créatrice indépendante (filleule de Pierre), qui conçoit et coud elle-même des accessoires textiles : trousses, pochettes, cotons démaquillants, petits sacs, etc.

**[NOUVEAU]** Céline exerce une autre activité professionnelle en parallèle : le temps qu'elle peut consacrer au site est limité, ce qui est une contrainte structurante pour le back-office et la charge de mise à jour (cf. §6 et §7).

**[NOUVEAU]** Situation actuelle : elle n'a plus de site depuis plus d'un an et vend via ses réseaux sociaux et un compte **Vinted Pro**. Elle vend également en physique sur des marchés et brocantes, avec un catalogue papier qu'elle a réalisé elle-même. Le projet vise à faire passer l'activité « à une étape supérieure, plus concrète », avec une méthode de commande et de paiement plus simple, plus structurée et plus sécurisée.

Le projet est porté techniquement par Pierre Bultez, qui assurera à la fois le développement et l'hébergement sur son infrastructure existante.

> **[CONFIRMÉ]** Vinted ne propose pas d'export/API public permettant de récupérer catalogue et avis clients automatiquement : le catalogue sera ressaisi manuellement sur le nouveau site, sans import automatisé.

## 2. Objectifs du projet

Objectifs exprimés par Céline **[NOUVEAU]**, par ordre d'importance :

1. **Gagner en visibilité** et professionnaliser l'image de la marque
2. **Vendre en ligne** de façon structurée et sécurisée (vs. transactions négociées de la main à la main sur les réseaux)
3. **Faciliter la prise de contact**, en particulier pour permettre au client de choisir le motif et/ou la matière selon son projet
4. **Réduire les appels et messages pour des renseignements basiques**, grâce à des photos et des descriptions qui font comprendre son univers

### Objectifs mesurables à 6 mois **[NOUVEAU]**

| Indicateur | Cible exprimée par la cliente |
|---|---|
| Ventes en ligne | 10 à 15 commandes par mois |
| Sollicitations pour questions basiques | En baisse nette par rapport à aujourd'hui |
| Contenu | Catalogue « bien fourni », avec de vraies photos et de vraies descriptions |

Objectifs complémentaires côté projet :

- Canal de vente autonome, sans commission ni règles imposées par une marketplace
- Expérience visuelle cohérente avec l'identité artisanale et délicate de la marque (ce qui exclut le thème Magento par défaut, Luma, jugé daté)
- Parcours d'achat complet et fiable : catalogue, fiche produit, panier, paiement, suivi, compte client
- Site gérable au quotidien par Céline seule, sans dépendance systématique à Pierre pour les opérations courantes
- Base technique robuste et évolutive (cours de couture, avis clients, codes promo… à venir plus tard)

## 3. Parties prenantes

| Rôle | Personne | Responsabilité |
|---|---|---|
| Cliente / porteuse du projet | Céline — marque « Madame Aiguille » (filleule de Pierre) | Définit les besoins métier, fournit le contenu (produits, textes, photos), valide les livrables, gère le site au quotidien après mise en ligne |
| Développeur / prestataire | Pierre Bultez | Conception, développement, intégration, déploiement, maintenance technique |

## 4. L'offre et le catalogue **[RÉVISÉ]**

### Nature des produits

- Créations imaginées **et** cousues par Céline elle-même
- **Séries limitées de 5 à 10 pièces maximum** par modèle — c'est le mode de fonctionnement retenu par la cliente, motivé par deux raisons : le stock de tissu disponible en magasin est limité, et cela lui permet de **changer souvent de motif**
- Conséquence directe pour le site : le catalogue **tourne vite**. La mise en avant des nouveautés et le renouvellement des fiches produit sont donc structurants, pas accessoires (cf. §5, section « Actualités »)

> ⚠️ **Correction par rapport à la v0.1** : la v0.1 partait sur des « pièces uniques faites main non réapprovisionnables » (produit simple, stock = 1). Le questionnaire indique en réalité des **séries limitées de 5 à 10 pièces**. La modélisation catalogue est revue en conséquence (cf. spécification fonctionnelle §2 et architecture §4). Une pièce réellement unique reste un cas particulier possible (série de 1), mais ce n'est plus le modèle dominant.

### Variantes **[NOUVEAU]**

- Sur **certains** produits uniquement : **2 tailles/formats maximum**
- Souhait exprimé : proposer **une gamme de tissus au choix au moment de la commande**
  - Céline a elle-même nuancé ce point : elle voit ce choix passer par **le formulaire de contact**, pour pouvoir échanger avec la cliente. Retenu pour la v1 : **pas de sélecteur de tissu en fiche produit**, mise en relation via le formulaire (cf. §5)

### Gamme de prix **[NOUVEAU]**

| | |
|---|---|
| Amplitude complète | 2 € à 90 € |
| Cœur de gamme (produits les plus vendus) | 10 € à 20 € |
| Positionnement voulu | « accessible et coloré » |

Implication : le panier moyen sera bas. Les frais de port pèseront proportionnellement lourd dans la décision d'achat — le calcul des frais de port et l'incitation au multi-achat sont des sujets à traiter sérieusement (cf. §5).

### Hors offre **[NOUVEAU]**

- **Pas de retouches** : explicitement écarté par la cliente
- **Cours de couture** : souhaités « par la suite », quand elle aura trouvé un lieu où les donner → **hors périmètre v1**, à garder en tête comme évolution (page de présentation + réservation éventuelle)

## 5. Périmètre fonctionnel (v1)

### Inclus

- Catalogue produits avec catégories (types de créations)
- Fiches produit détaillées (photos multiples, description, prix), couvrant les créations en série limitée (avec ou sans variante de taille)
- **[NOUVEAU]** Section **« Nouveautés / Actualités »**, visible **dès la page d'accueil**, permettant aux visiteurs de voir immédiatement ce qui est nouveau — besoin explicitement formulé par la cliente et cohérent avec le rythme de renouvellement des séries limitées
- **[PRÉCISÉ]** Page **Contact** avec formulaire, servant à la fois aux demandes générales et aux échanges sur le **choix de motif / de tissu** pour une commande
- Panier d'achat
- Tunnel de commande (checkout) : adresse, choix du mode de livraison, paiement
- Compte client : création de compte, historique de commandes, gestion des adresses
- Paiement en ligne sécurisé **+ virement bancaire + remise en main propre** (cf. ci-dessous)
- Gestion des commandes et des stocks côté back-office Magento
- Responsive design — trafic attendu majoritairement mobile (cible sur réseaux sociaux)
- Pages de contenu statique : À propos / « L'histoire de Madame Aiguille », mentions légales, CGV, contact

### Moyens de paiement **[NOUVEAU — révisé]**

Souhaits de la cliente : **carte bancaire, virement, et remise en main propre**.

| Moyen | Statut | Remarque |
|---|---|---|
| Carte bancaire | **Retenu** — **Mollie** (10/09/2026) | Moyen principal, à finaliser à la création du compte marchand |
| Virement bancaire | **Retenu** | Natif Magento (« Bank Transfer Payment ») ; commande en attente jusqu'à réception des fonds — **implique un suivi manuel par Céline**, à cadrer avec elle |
| Remise en main propre | **Retenu** | À traiter comme un mode de **retrait** (pas de frais de port) associé à un paiement sur place ; pertinent vu son activité sur les marchés et brocantes. Point à cadrer : lieu et modalités de retrait |
| PayPal | Non retenu à ce stade | — |

### Livraison **[NOUVEAU — révisé]**

- Zone : **France métropolitaine uniquement** en v1
- Transporteurs **confirmés par la cliente** : **Mondial Relay, Chronopost, Colissimo** (les trois, et non plus « Chronopost éventuellement »)
- **Souhait explicite : calcul automatique des frais de port selon le poids**
  - Faisable **sans API transporteur** via les *table rates* Magento configurées par palier de poids → retenu pour la v1
  - **Implique de renseigner le poids sur chaque fiche produit** : c'est une contrainte de saisie nouvelle pour Céline, à intégrer au guide back-office
- À trancher avec elle : seuil éventuel de **franco de port** (livraison offerte à partir de X €), pertinent vu le panier moyen bas

### Nom de domaine **[NOUVEAU — quasi arrêté]**

- Recommandation de Pierre : **`madame-aiguille.fr`**
- Céline est d'accord, en hésitant avec `madameaiguille.fr`
- **Action** : vérifier la disponibilité des deux et réserver — idéalement les deux, avec redirection de l'un vers l'autre

### À confirmer / probable en v1

- Newsletter : présente dans la maquette d'ambiance (« Rejoignez l'univers Madame Aiguille »), non évoquée dans le questionnaire — fonctionnalité native Magento, coût d'activation faible, **à valider avec Céline** (implique une obligation RGPD et une charge éditoriale récurrente)
- Bloc Instagram en page d'accueil : présent dans la maquette — à traiter comme une galerie d'images gérée manuellement plutôt que comme un flux automatisé (cf. spécification fonctionnelle)
- Codes promo et avis clients : non réévoqués par la cliente — restent des évolutions possibles, non bloquantes pour le lancement

## 6. Hors périmètre (v1)

- Cours de couture (inscription/réservation) — **évolution identifiée**
- Service de retouches — **explicitement écarté par la cliente**
- Configurateur en ligne de tissu/motif avec devis automatique (le formulaire de contact tient ce rôle en v1)
- Marketplace multi-vendeurs, B2B, application mobile, multi-langue/multi-devise
- Programme de fidélité, parrainage
- Avis clients (module natif Magento suffirait si le besoin se confirme)
- Blog / contenu éditorial élaboré au-delà de la section Actualités et des pages statiques

## 7. Contenu et photos **[NOUVEAU]**

Point de vigilance : **le contenu est le principal facteur de risque du planning**, pas la technique.

- Photos disponibles aujourd'hui : celles publiées sur ses réseaux et reprises dans son catalogue papier — qualité « réseaux sociaux », non homogène
- Céline prévoit de faire appel à **un photographe professionnel qu'elle a déjà en tête**, mais sans date
- Objectif qu'elle formule elle-même : « un site bien fourni avec de véritables photos et descriptions »

**Recommandation** : ne pas conditionner la mise en ligne au shooting professionnel. Lancer avec les photos existantes recadrées à un format homogène, et prévoir le remplacement progressif. À cadrer avec elle : un **format et un ratio d'image uniques** dès le départ, pour éviter d'avoir à tout reprendre.

À produire côté cliente : descriptions produit, texte « À propos / L'histoire de Madame Aiguille », CGV et mentions légales (à rédiger avec elle, en fonction de son statut).

## 8. Contraintes techniques

- Backend : Magento Open Source (choix arrêté — cf. document d'architecture)
- Frontend : thème Hyvä (Tailwind CSS + Alpine.js, rendu serveur natif Magento — pas de front découplé/headless)
- Hébergement : serveur dédié existant de Pierre (Ubuntu, Apache, PHP, MySQL, Redis, Elasticsearch, RabbitMQ, Certbot)
- Déploiement : via GitHub Actions
- Nom de domaine à réserver ; SSL via Certbot déjà en place
- **[NOUVEAU]** Identité visuelle fournie par la cliente : logo, phrase d'accroche « L'élégance cousue main », palette et intentions typographiques — cf. **charte-graphique.md**

## 9. Contraintes de délai et budget

> **[CONFIRMÉ]** Pas de budget financier : le site est offert par Pierre à sa filleule, et sert aussi de projet d'apprentissage personnel. Hébergement sur le VPS existant. Délai : le plus tôt possible, sans date imposée — priorité à un MVP fonctionnel (catalogue + panier + checkout + compte + actualités) plutôt qu'à une boutique exhaustive dès le lancement.

**[NOUVEAU]** Le questionnaire mentionne une enveloppe de 30 à 50 €/mois évoquée par Céline. Ce chiffre provient d'une question de formulaire standard et **ne s'applique pas** : Pierre lui a confirmé de ne pas en tenir compte. Le seul coût résiduel réel est le **nom de domaine** (~10-15 €/an).

## 10. Exploitation et maintenance **[NOUVEAU]**

- **Rythme de mise à jour envisagé par Céline : mensuel.** Ce rythme est cohérent avec sa disponibilité, mais **en tension avec le modèle des séries limitées de 5 à 10 pièces** : si le stock tourne plus vite que les mises à jour, le site affichera des produits épuisés. Sujet à rediscuter avec elle — a minima, prévoir un back-office assez simple pour qu'une mise à jour ponctuelle ne lui coûte que quelques minutes
- **Maintenance technique assurée par Pierre**, « au moins au début, le temps qu'elle se familiarise avec les aspects techniques » (souhait explicite de la cliente)
- Prévoir un **guide utilisateur illustré** : ajouter un produit, gérer un stock, traiter une commande, encaisser un virement

## 11. Points ouverts à valider avec la cliente

1. **Franco de port** : livraison offerte à partir d'un montant ? (recommandé vu le panier moyen de 10-20 €)
2. **Paliers de poids** pour le calcul automatique des frais de port, par transporteur — et acceptation par Céline de saisir un poids sur chaque produit
3. **Remise en main propre** : lieu, créneaux, périmètre géographique
4. **Virement** : est-elle prête à gérer le suivi manuel des encaissements ?
5. **Newsletter** : la veut-elle réellement au lancement ?
6. **Statut fiscal** (auto-entrepreneuse ?) : impacte la TVA, la facturation et les mentions légales
7. **Nom de domaine** : arbitrage final `madame-aiguille.fr` vs `madameaiguille.fr` et réservation
8. **Rythme de mise à jour mensuel** vs rotation rapide des séries limitées : comment concilier les deux
9. Codes promo et avis clients dès le lancement, ou report en v2
