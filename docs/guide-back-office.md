# Guide du back-office — Madame Aiguille

Pour Céline. Ce guide suit les gestes du quotidien : ajouter une création, suivre son stock, traiter une commande en
point relais ou en retrait, changer le bandeau, les blocs et les réseaux. Les captures montrent l'administration telle
que vous la voyez avec votre rôle « Atelier ».

Ce que vous ne voyez pas (paiements, transporteurs, comptes administrateurs, réglages techniques) se règle avec Pierre.
Le détail de chaque réglage est dans la documentation technique, §24 « Mémo Céline ».

## 1. Se connecter

1. Ouvrez l'adresse privée de l'administration transmise par Pierre (gardez-la dans votre gestionnaire de mots de
   passe ; elle n'est écrite nulle part ailleurs).
2. Hors de l'atelier (smartphone, déplacement), le navigateur demande d'abord un **identifiant et un mot de passe
   communs**, transmis par Pierre. Depuis une adresse enregistrée, cette étape n'apparaît pas.
3. Saisissez votre identifiant et votre mot de passe Magento.
4. Saisissez le **code à six chiffres** de Google Authenticator. À la toute première connexion, Magento vous envoie un
   email pour associer l'application à votre compte. Téléphone perdu ou changé : Pierre réinitialise la double
   authentification.

Le **tableau de bord** résume les ventes et les dernières commandes. Le menu de gauche ne montre que vos rubriques.

![Tableau de bord et menu du rôle Atelier](guide-back-office/01-tableau-de-bord.jpg)

## 2. Ajouter une création

*Catalogue › Produits*, puis **Ajouter un produit** (produit simple). En haut du formulaire, choisissez le **Jeu d'attributs « Création »** : la fiche se recharge avec les bons champs. La flèche à droite du bouton sert à choisir un autre type, par exemple un modèle en deux tailles (configurable).

![Liste des produits](guide-back-office/02-produits.jpg)

Remplissez, de haut en bas :

| Champ | Ce qu'il faut savoir |
|---|---|
| Activer le produit | Laissez « Non » tant que la fiche n'est pas complète : la création n'est pas visible en boutique |
| Jeu d'attributs | Toujours **Création** |
| Nom de produit | Le nom affiché en boutique |
| REF | Une référence unique, par exemple `MA-TRO-LIN` |
| Prix | Prix payé par la cliente (pas de TVA : franchise en base) |
| Quantité | Le **nombre de pièces** de la série |
| Poids | **Obligatoire**, en kilogrammes (`0.16` pour 160 g). Le poids de l'emballage (0,2 kg) est ajouté automatiquement pour calculer le port |
| Catégories | Une ou plusieurs rubriques de la boutique |
| Visibilité | « Catalogue, Recherche » pour une création vendue seule |
| Définir comme Nouveau | Dates de début et de fin du badge « Nouveauté » et de la section Nouveautés de l'accueil |

![Haut de la fiche produit](guide-back-office/03-fiche-produit.jpg)

![Quantité, poids et catégories](guide-back-office/04-poids-et-stock.jpg)

Plus bas :

- **Série limitée** : cochez-la et indiquez le nombre de pièces ; la fiche affiche « Série limitée — N pièces ».
- **Images et vidéos** : photos au format **portrait 4:5**, trois vues au moins. Une photo d'un autre format est
  rognée, jamais déformée. La première est la vignette.
- **Contenu** : la description ; les champs « Composition », « Dimensions » et « Conseils d’entretien » remplissent
  les onglets de la fiche.
- La section **Subscriptions by Stripe** est à ignorer : la boutique ne vend pas d'abonnement.

Un **modèle en deux tailles** est un produit *configurable* sur l'attribut Taille : chaque taille a son stock et son
poids. Demandez à Pierre la première fois.

Cliquez sur **Enregistrer**, vérifiez la fiche en boutique, puis passez *Activer le produit* à « Oui ».

## 3. Suivre le stock

- Le stock baisse tout seul à chaque commande. À 0, la création affiche « Épuisé » et reste visible en fin de liste.
- Une annulation remet les pièces en stock.
- **Rapports › Stock bas** liste les créations **sous 2 pièces** : la dernière pièce d'une série y apparaît. Aucun
  email n'est envoyé, il faut consulter ce rapport. Le seuil se règle par création (*Quantité de notification*).

![Rapport de stock faible](guide-back-office/05-stock-faible.jpg)

## 4. Traiter une commande

*Ventes › Commandes*, puis **Voir** sur la ligne. Le bouton **Colonnes** permet de masquer les colonnes inutiles de la
liste.

![Liste des commandes](guide-back-office/06-commandes.jpg)

En haut de la commande : **Facturer**, **Expédier**, **Avoir** (remboursement), **Annuler**, **Envoyer un email**.

![Une commande](guide-back-office/07-commande.jpg)

### Changer le statut et prévenir la cliente

En bas de la commande, dans **Notes pour cette commande** : choisissez le **Statut**, écrivez un commentaire si besoin,
cochez **Avertir le client par email** et **Visible dans le magasin** pour qu'elle le lise, puis **Mettre à jour**.

Les six statuts : *En attente de paiement*, *Paiement reçu*, *En préparation*, *Expédiée*, *Prête pour retrait*,
*Livrée*. « Paiement reçu » et « Prête pour retrait » envoient chacun leur email automatiquement.

### Commande en point relais

Elle est payée en ligne à la commande (« Paiement reçu »).

1. L'adresse de livraison est celle du point relais (« NOM — Point relais FR-… »).
2. Créez l'étiquette dans votre **espace pro Mondial Relay** avec cette adresse.
3. Dans la commande, **Expédier**, puis **Ajouter un numéro de suivi** (Mondial Relay et le numéro de l'étiquette).
4. En bas, cochez **Envoyer une copie de la livraison par mail** — sans cette case, la cliente ne reçoit pas l'email
   d'expédition —, puis **Valider l'expédition**. La commande passe « Expédiée ».

![Expédition et numéro de suivi](guide-back-office/14-expedition-suivi.jpg)

### Commande en retrait à l'atelier

Le rendez-vous est dans **Mode de livraison et paiement › Informations de livraison** (« Retrait à l'atelier … — jeudi
12 novembre 2026 à 10 h 00 »). Les **Informations de paiement** disent si elle est déjà payée.

![Retrait payé en ligne : paiement et rendez-vous](guide-back-office/08-retrait-paiement.jpg)

1. **Quand la commande est prête** : statut **Prête pour retrait**, puis **Mettre à jour**. L'email part avec le
   rendez-vous, le lieu, l'itinéraire, et « déjà réglée en ligne » ou « règlement sur place ».

   ![Statut Prête pour retrait](guide-back-office/09-statut-pret-pour-retrait.jpg)

2. **Au rendez-vous** :
   - payée en ligne (*Carte bancaire en ligne*) : **Expédier** suffit ;
   - payée sur place (*Paiement sur place*) : encaissez au TPE ou en espèces, puis **Facturer** (l'email « Paiement
     reçu » part), puis **Expédier**.

   Dans les deux cas, la commande passe « Livrée ».
3. **Rendez-vous non honoré** : **Annuler** la commande. Le stock et le créneau sont libérés.

### Emballage cadeau et message

L'emballage apparaît en ligne « Emballage cadeau » dans les totaux. Le texte à écrire sur la carte est dans **Options
cadeau** de la commande.

### Rembourser

Depuis la **facture** de la commande (onglet *Factures*), bouton **Avoir** : pour une commande payée par carte, le
bouton **Rembourser** rembourse la cliente via Stripe. Cochez **Envoyer une copie de l'avoir par mail** pour la
prévenir. Une commande payée sur place se rembourse « hors ligne », après l'avoir remboursée vous-même.

## 5. Le bandeau, les blocs et les pages

*Contenu › Blocs* : chaque zone de texte du site est un bloc, repéré par son identifiant.

![Liste des blocs](guide-back-office/10-blocs.jpg)

| Identifiant | Où il s'affiche |
|---|---|
| `header_announcement` | Le bandeau tout en haut du site |
| `home_hero`, `home_story`, `home_actualities` | L'accueil : visuel principal, histoire, nouvelles de l'atelier |
| `product_reassurance` | Les arguments sous le bouton d'achat des fiches |
| `cart_reassurance` | Les arguments du panier |
| `contact_help`, `contact_locations` | La page contact |

Ouvrez le bloc, modifiez le texte, **Enregistrer**. Le changement est visible en boutique aussitôt. Ne changez
pas l'**Identifiant** : le site ne retrouverait plus le bloc.

![Bandeau d'annonce](guide-back-office/11-bandeau.jpg)

*Contenu › Pages* : « À propos », « Nos tissus », les pages juridiques (CGV, mentions légales, livraison et retours,
confidentialité).

## 6. Les réglages de la boutique

*Magasins › Configuration › Madame Aiguille* : réseaux sociaux, mentions légales, catalogue, panier, formulaire de
contact, Mondial Relay, retrait à l'atelier, emballage cadeau. Ouvrez un groupe, décochez **Utiliser les valeurs
système** pour modifier un champ, puis **Sauvegarder la configuration**.

- **Réseaux sociaux** : l'adresse de chaque réseau ; laissez vide pour masquer son icône dans le pied de page.

  ![Réseaux sociaux](guide-back-office/12-reseaux-sociaux.jpg)

- **Retrait à l'atelier** : nom et adresse du lieu, plan d'accès, **disponibilités de chaque semaine**, durée d'un
  créneau, délai de prévenance, jours fermés.

  ![Réglages du retrait](guide-back-office/13-retrait-reglages.jpg)

- **Emballage cadeau** : proposer ou non l'option, son prix et son texte.
- **Mondial Relay** : le code enseigne (ne pas toucher) et le poids de l'emballage (0,2 kg).

*Marketing* : **Règles de prix du panier** pour un code promo, **Gabarits d'email** pour relire les textes des emails,
**Abonnés à la newsletter**.

## 7. Consulter les statistiques

Les statistiques de visite du site (combien de visites, quelles pages, d'où viennent les visiteuses, ce qu'elles
cherchent) sont dans **Matomo**, un outil installé sur le serveur de la boutique. Elles sont anonymes : on ne sait pas
*qui* visite, seulement *combien* et *comment*.

1. Dans l'administration, *Rapports › Statistiques du site* : la page de Matomo s'ouvre.

   ![Le lien vers les statistiques](guide-back-office/15-statistiques-menu.jpg)

2. Connectez-vous avec **votre compte Matomo** (identifiant et mot de passe reçus par email à l'invitation de Pierre,
   distincts de ceux de Magento). Vous pouvez aussi ouvrir directement `https://stats.madame-aiguille.fr`.
3. Le **tableau de bord** résume la période choisie en haut (jour, semaine, mois…).

   ![Tableau de bord de Matomo](guide-back-office/16-matomo-tableau-de-bord.jpg)

Les rapports les plus utiles, dans le menu de gauche :

| Je veux savoir… | Rapport |
|---|---|
| Combien de visites, et quand | *Visiteurs › Vue d'ensemble* |
| Qui est sur le site en ce moment | *Visiteurs › Temps réel* |
| Quelles pages sont les plus vues | *Comportement › Pages* |
| Par quelle page on arrive, par laquelle on part | *Comportement › Pages d'entrée* et *Pages de sortie* |
| D'où viennent les visites (Google, Instagram, lien direct…) | *Acquisition › Tous les canaux* et *Réseaux sociaux* |
| Téléphone ou ordinateur | *Visiteurs › Appareils* |
| Pays et régions | *Visiteurs › Localisation* |
| Ce que les visiteuses cherchent dans la boutique | *Comportement › Recherche interne* : un mot souvent cherché sans résultat est une idée de création ou de mot-clé |
| Les liens cassés | *Comportement › Titres des pages*, ligne « 404 » : les pages introuvables et la page d'où venait le lien |

Chaque début de mois, un **rapport par email** arrive sur `contact@madame-aiguille.fr`.

Les visites de Pierre ne sont pas comptées ; les vôtres le seront tant que l'adresse internet de l'atelier n'est pas
transmise à Pierre, qui l'exclura. Les chiffres du jour se mettent à jour
toutes les heures ; seul le *Temps réel* est instantané. Votre compte est en **lecture seule** : vous ne pouvez rien
casser.

## 8. En cas de doute

- Une action irréversible (avoir, annulation) : prenez le temps de relire le montant et la commande.
- Un message d'erreur, une page qui ne s'ouvre pas, un réglage introuvable : notez l'heure et la page, et prévenez Pierre.
