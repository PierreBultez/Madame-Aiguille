# Call Céline — vendredi 11 septembre 2026

**Objet** : débloquer les lots 5 (livraison et paiements) et 6b (habillage du checkout)
**Document interne Pierre.** Préparation du 11/09/2026, **complétée du compte rendu après le call**.

---

# RÉSULTAT DU CALL — ce qui a été décidé

> Compte rendu ajouté après le call. **Cette section fait foi** ; les sections numérotées qui suivent sont le document de préparation, conservé pour la trace des options étudiées et des tarifs relevés.

## Identité de l'entreprise (§3 — répondu)

| | |
|---|---|
| Nom commercial | **MADAME AIGUILLE** |
| Raison sociale | BULTEZ CELINE |
| Forme juridique | **Entreprise individuelle**, non inscrite au RCS |
| SIREN · SIRET | `940 760 911` · `940 760 911 00013` |
| Activité | Fabrication d'autres vêtements et accessoires |
| Siège et expédition | **35 Grande Rue, 37800 Saint-Épain** |
| TVA | **Aucun numéro de TVA → franchise en base.** Mention « TVA non applicable, art. 293 B du CGI » sur les factures et en pied de site. **Décision Pierre du 09/10/2026 : au plus simple, aucune TVA facturée, y compris sur les ventes européennes.** Limite à surveiller : au-delà de 10 000 € de ventes à distance intra-UE sur l'année, le régime change et le guichet OSS devient obligatoire — à revoir à ce moment-là, avec un conseil |

Conséquences techniques : `shipping/origin` → Saint-Épain (37800, FR), `tax/defaults/country` → FR, prix TTC = prix encaissés, aucune TVA sur les documents.

## Paiement (§4 — répondu)

- **Compte Mollie créé.** Reste à le faire valider : Mollie exige un **site en ligne** et **un paiement réel effectué par nous-mêmes** pour confirmer le compte. → la mise en ligne d'une préproduction sur le vrai domaine devient une **dépendance du lot 5**, pas une étape de fin de projet.
- **Carte bancaire uniquement** en ligne. Pas de virement (ni Mollie ni classique), pas de PayPal, pas de paiement fractionné. Les 38 méthodes Mollie exposées par défaut sont à restreindre à la CB.
- **Exception click & collect : paiement sur place**, au TPE ou en espèces (voir ci-dessous).

Ce qui tombe du périmètre du lot 5 : le virement SEPA, le cron d'expiration des commandes en attente de virement, la relance à J-2, et le contenu RIB de l'email de virement esquissé au lot 7.

## Livraison (§5 — répondu)

- **Mondial Relay uniquement**, en point relais. Colissimo et Chronopost sont écartés.
- **Compte Mondial Relay : celui de Céline n'est pas un compte pro.** Action : ouvrir ou transférer vers l'**Offre Start**, puis transmettre code enseigne et clé privée — sans eux, pas de carte de points relais sur le site.
- **Délai d'expédition** : aujourd'hui 4 à 5 jours, **cible 48 h**.
- **Livraison offerte** : 49 € maintenu pour l'instant, **60 € envisagé** — non tranché.
- **Zone de vente : pays francophones de l'Union européenne** (stratégie révisée par Pierre le 09/10/2026, en remplacement de « tous les pays Mondial Relay »). Croisée avec la desserte Mondial Relay, la liste se réduit à **France, Belgique, Luxembourg** — Monaco étant traité comme la France (codes postaux 980xx, même territoire douanier et fiscal).
  > **La Suisse est écartée** (confirmé par Pierre le 09/10/2026) : elle n'est pas dans l'Union européenne et n'est pas desservie par Mondial Relay en point relais. La règle tient en une phrase — **pays de l'UE francophones et desservis par Mondial Relay**.
- **Franco : 60 €** (décision Pierre du 09/10/2026), sur le point relais Mondial Relay. Le seuil affiché passe donc de 49 € à 60 € dans *Madame Aiguille › Panier* **et** dans la règle de livraison gratuite.

## Click & collect (§5.8 — répondu)

- Retenu, avec **paiement sur place** (TPE ou espèces), pas en ligne.
- **Créneaux fixes** : jeudi 9 h – 18 h, vendredi 9 h – 11 h 30.
- **Sélecteur de date et d'heure dans le tunnel** (décision Pierre du 09/10/2026) : la cliente choisit son créneau parmi les disponibilités de Céline, et **le rendez-vous est confirmé automatiquement** si le créneau est libre. Aucune validation manuelle.
- **Un lieu de retrait unique**, avec un aperçu cartographique. Adresse et lieu **modifiables depuis le back-office** ; un lieu générique est posé en attendant celui de Céline.
- **Annulation : au rendez-vous non honoré** (décision Pierre du 09/10/2026). Le créneau étant désormais choisi à la commande, chaque retrait a bien une date : le garde-fou craint plus haut tombe de lui-même. Pas de délai en jours, donc **pas de cron** : c'est Céline qui annule la commande dans l'administration après un rendez-vous manqué, ce qui relibère le stock. À documenter dans le mémo Céline.
- Ces créneaux donnent enfin son contenu à l'email « Prête pour retrait » du lot 7.

## Catalogue et contenus (§7 et §8 — répondu)

- **Délai d'expédition annoncé : 4 à 5 jours ouvrés** (décision Pierre du 09/10/2026) — la réalité d'aujourd'hui, pas la cible de 48 h.
- **Emballage cadeau : option payante à 2 €**, prix modifiable depuis le back-office (décision Pierre du 09/10/2026).
- **Pages juridiques** : contenu générique à générer en attendant les textes définitifs (décision Pierre du 09/10/2026).
- **10 à 50 produits** au total au lancement.
- **Pinterest : non utilisé** → à retirer du pied de page.
- **TikTok à ajouter** → nouveau réseau à câbler dans la configuration et le pied de page.
- **Emballage cadeau + carte personnalisée** : nouveau besoin exprimé, hors périmètre actuel.

---

# QUESTIONS RESTÉES OUVERTES

**Les dix questions ont été tranchées par Pierre le 09/10/2026** et sont remontées dans « Résultat du call ». Il ne reste que des valeurs à fournir.

| # | Question | Pourquoi ça bloque |
|---|---|---|
| 1 | **Lieu de retrait réel** : adresse exacte de Céline pour le click & collect. Un lieu générique est posé en attendant | Le champ est prévu dans le back-office, seule la valeur manque |
| 2 | Rédaction définitive des **CGV, mentions légales et page Livraison et retours** sur la base des contenus génériques, choix d'un **médiateur de la consommation**, confirmation du **domaine** | Obligatoire avant la première vente, et avant la validation Mollie |

---

# ACTIONS

| # | Action | Qui | État |
|---|---|---|---|
| 1 | Créer le compte Mollie | Céline | ✅ fait |
| 2 | Ouvrir ou transférer le compte **Mondial Relay Offre Start**, transmettre code enseigne et clé privée | Céline | ⬜ |
| 3 | Peser l'emballage type et les créations | Céline | ⬜ |
| 4 | Donner les tarifs Mondial Relay pro et la politique de prix | Céline | ⬜ |
| 5 | Trancher les questions restantes | Pierre / Céline | 🔸 9 sur 10 tranchées le 09/10 |
| 6 | Rédiger CGV, mentions légales, Livraison et retours · choisir un médiateur | Céline (+ relecture) | ⬜ |
| 7 | Confirmer le domaine et son titulaire | Céline / Pierre | ⬜ |
| 8 | **Mettre une préproduction en ligne sur le domaine** (lot 8a) | Pierre | ⬜ |
| 9 | **Effectuer un paiement réel pour valider le compte Mollie** | Pierre / Céline | ⬜ après 8 |
| 10 | Développer le lot 5 | Pierre | ✅ livré le 09/10/2026, hors 8a (`documentation-theme.md` §26) |

---

## 0. Ce qu'il fallait obtenir — préparation

Si le call dérape, ces trois points passent avant tout le reste :

1. **Mollie** — elle crée son compte (aujourd'hui ou ce week-end) et m'y invite. Sans ça, zéro paiement possible.
2. **Le brief livraison** — transporteurs retenus, emballage et poids, prix facturés, franco, remise en main propre.
3. **Son statut juridique et fiscal** — SIRET, micro-entreprise, franchise de TVA. Ça conditionne Mollie, la TVA, les factures et les mentions légales.

### Déroulé proposé

| Temps | Sujet | Section |
|---|---|---|
| 5 min | Où on en est | §1 |
| 5 min | Statut juridique et fiscal | §3 |
| 10 min | Paiement — Mollie | §4 |
| 20 min | Livraison et retrait | §5 |
| 5 min | Checkout | §6 |
| 10 min | Juridique, domaine, contenus | §7 |
| 5 min | Récap des actions | §9 |

---

## 1. Où on en est — le message à faire passer

> « Tout ce qui ne dépendait que de moi est fait. Pour pouvoir vendre, il manque trois choses qui viennent de toi. »

| Lot | Contenu | État |
|---|---|---|
| 1 | Fondations : thème, couleurs, typos, header, footer | ✅ Livré |
| 2 | Catalogue : pages catégorie, fiches produit, séries limitées, badges | ✅ Livré |
| 3 | Accueil, pages de contenu, formulaire de contact, newsletter, 404 | ✅ Livré |
| 4 | Panier et mini-panier, barre « livraison offerte dès 49 € » | ✅ Livré |
| 6a | Tunnel de commande installé (version standard, pas encore habillée) | ✅ Livré |
| 7 | Compte client, suivi de commande en 6 étapes, emails aux couleurs de la marque | ✅ Livré |
| **5** | **Livraison et paiements** | ⛔ **Bloqué — attend Céline** |
| **6b** | **Habillage du tunnel de commande** | ⛔ **Bloqué — dépend du 5** |
| 8 | Back-office simplifié, guide, mise en ligne | À faire |

Ce qu'elle peut « voir » concrètement : l'accueil, les catégories, les fiches, le panier, le compte client et les emails. Ce qui ne marche **pas encore pour de vrai** : les frais de port (tarif provisoire de 5 € par article), la livraison offerte (affichée, mais pas raccordée aux vrais transporteurs), le paiement (module installé mais désactivé), le point relais.

### Ce qui a changé depuis nos derniers échanges (à lui dire)

- **Paiement : Mollie au lieu de Stripe.** Même usage pour elle (carte bancaire), mais Mollie gère aussi le virement, il est déjà intégré au site, et la carte bancaire française y coûte 1,20 % + 0,25 €.
- **Tunnel de commande : version standard de Magento, habillée aux couleurs de la marque**, et non redessinée au pixel près des maquettes. C'est le choix gratuit et robuste (l'alternative coûte 1 000 € de licence).

---

## 2. Pourquoi les lots 5 et 6b sont bloqués

| Blocage | Conséquence aujourd'hui | Ce qu'il faut de Céline | Ce que ça débloque |
|---|---|---|---|
| Pas de compte Mollie | Aucun paiement possible, même en test | Créer le compte, m'inviter (§4) | Clés de test → je développe tout le paiement |
| Pas de brief livraison | Tarif provisoire de 5 €/article, pas de point relais, franco 49 € sans vraie règle derrière | Transporteurs, emballage, prix, retrait (§5) | Grilles au poids, point relais, retrait |
| Statut fiscal inconnu | TVA et mentions non paramétrées ; pays fiscal encore sur États-Unis | SIRET, régime, adresse (§3) | TVA, factures, mentions légales, dossier Mollie |
| Site pas en ligne, domaine non confirmé | **Mollie ne valide un compte que sur un site en ligne** avec prix et descriptions | Domaine, CGV, vrais produits (§7) | Activation des paiements réels |
| Lot 6b dépend du 5 | On n'habille pas un tunnel dont les écrans (modes de livraison, paiements) ne sont pas définitifs | Rien de plus que le 5, plus les CGV pour la case à cocher | Tunnel aux couleurs de la marque |

**À retenir** : le blocage Mollie se lève en ~15 minutes de son côté (création du compte + invitation). La **validation** du compte par Mollie, elle, peut prendre jusqu'à 10 jours ouvrés et exige un site en ligne → il faut lancer ça **tôt**, en parallèle du développement.

---

## 3. Statut juridique et fiscal (5 min)

*Pourquoi* : Mollie demande l'identité de l'entreprise ; la TVA, les factures et les mentions légales en dépendent. Elle a un Vinted Pro, donc elle est *a priori* déjà immatriculée — à confirmer.

| Question | Réponse |
|---|---|
| Forme juridique : micro-entreprise ? Autre ? | |
| Numéro SIRET | |
| Activité déclarée (artisanale / commerciale) et nom commercial « Madame Aiguille » déclaré ? | |
| **Franchise en base de TVA ?** (seuil 2026 pour la vente de biens : 85 000 € — la baisse à 25 000 € a été abandonnée) | |
| Adresse du siège à publier dans les mentions légales (domicile ? domiciliation ?) | |
| **Adresse d'expédition des colis** (sert au paramétrage d'origine, encore sur les États-Unis) | |
| Compte bancaire pour recevoir les paiements : pro ou perso, à son nom ? | |
| A-t-elle déjà un **médiateur de la consommation** ? (obligatoire pour vendre aux particuliers, micro-entreprises comprises) | |

> **Reco** — Si franchise de TVA : prix affichés = prix encaissés, aucune TVA sur les factures, mention **« TVA non applicable, art. 293 B du CGI »** sur les factures et en pied de site. Je ne suis pas juriste : pour le médiateur et les mentions, un passage par sa CMA ou un conseil est conseillé.

---

## 4. Paiement — Mollie (10 min)

### 4.1 Le compte : c'est elle qui doit le créer

Le titulaire doit être **l'entreprise de Céline** : sa pièce d'identité, son SIRET, son compte bancaire. Je ne peux pas le faire à sa place.

- [ ] Création du compte sur mollie.com — **date** : ____________
- [ ] URL du site déclarée : `madame-aiguille.fr` (voir §7)
- [ ] **M'inviter comme utilisateur** du tableau de bord (ou m'envoyer la clé API de test) → je peux développer dès réception
- [ ] Préparer pour la vérification : pièce d'identité, justificatif d'entreprise, IBAN (vérification jusqu'à 10 jours ouvrés, avec parfois des demandes complémentaires par email)

### 4.2 Ce que ça coûte (grille publique Mollie France, relevée le 11/09/2026)

| Moyen | Frais par paiement | Sur un panier de 15 € |
|---|---|---|
| Carte bancaire française (CB) | 1,20 % + 0,25 € | ≈ 0,43 € |
| Carte européenne (Visa/Mastercard hors CB) | 1,80 % + 0,25 € | ≈ 0,52 € |
| Apple Pay / Google Pay | tarif de la carte utilisée | ≈ 0,43 € |
| Virement SEPA | 0,25 € | 0,25 € |
| PayPal | frais PayPal + 0,10 € | — |
| Klarna / Alma (paiement fractionné) | à partir de 4,50 % + 0,35 € | ≈ 1,03 € |

Pas d'abonnement ni de frais fixes. Versements sur son compte : 5 par mois gratuits, puis 0,25 € l'unité.

### 4.3 Questions

| Question | Reco | Réponse |
|---|---|---|
| Carte bancaire | Oui | |
| Apple Pay / Google Pay | Oui — le trafic sera surtout mobile | |
| PayPal | Non en v1 (déjà écarté ; demande un compte PayPal Business) | |
| Paiement en 3 fois (Klarna, Alma) | Non — trop cher pour un panier de 10-20 € | |
| **Virement : via Mollie ou virement « classique » ?** | **Via Mollie** (voir ci-dessous) | | cb uniquement
| Délai avant annulation d'une commande par virement non payée | **7 jours** (le stock d'une série limitée reste bloqué pendant ce temps) | |
| Relance automatique avant annulation ? | Oui, 2 jours avant | |
| Chèque | Non (actif par défaut dans Magento, je le coupe) | |

**Le choix du virement, à lui expliquer simplement :**

- **Via Mollie (0,25 €)** : la cliente reçoit un IBAN Mollie et une référence ; quand l'argent arrive, **la commande passe toute seule en « Paiement reçu »** et l'email part. Céline n'a rien à pointer, et son RIB personnel n'apparaît nulle part. L'expiration et l'annulation sont gérées par Mollie.
- **Virement classique (gratuit)** : son propre RIB dans l'email de commande ; elle doit **surveiller son compte**, retrouver la référence et passer la commande à la main en « Paiement reçu ». C'était la charge « suivi manuel » identifiée dès le cahier des charges.

---

## 5. Livraison et remise en main propre (20 min — le cœur du call)

### 5.1 Comment elle expédie aujourd'hui

| Question | Réponse |
|---|---|
| Quels transporteurs utilise-t-elle aujourd'hui (Vinted, ventes directes) ? | |
| Lequel ses clientes préfèrent-elles ? | |
| Où dépose-t-elle ses colis, et à quelle fréquence ? | |
| A-t-elle déjà un compte pro chez un transporteur (Mondial Relay, La Poste) ? | |
| Envoie-t-elle parfois en **lettre suivie** pour les petites pièces ? | |

### 5.2 Quels transporteurs au lancement ?

Elle avait confirmé Colissimo, Mondial Relay et Chronopost. Avec un panier de 10 à 20 € et des pièces légères, je propose de simplifier la v1 :

> **Reco v1 : Mondial Relay (point relais) + Colissimo (domicile) + remise en main propre.** Chronopost reporté : l'express Chrono 13 coûte ≈ 23 € TTC sous 1 kg, plus que le panier moyen ; et son offre point relais (Shop2Shop) fait doublon avec Mondial Relay tout en demandant une seconde carte de sélection de point relais.

| Mode | Retenu en v1 ? |
|---|---|
| Mondial Relay — point relais | |
| Colissimo — domicile | |
| Colissimo — point retrait | |
| Chronopost | |
| Remise en main propre | |

### 5.3 Comptes transporteurs et création des étiquettes

- **Mondial Relay « Offre Start »** : compte pro sans engagement, sans minimum de volume, sans abonnement, réservé aux entreprises immatriculées en France, tarifs pro affichés dès 3,09 € HT. **Il me faut ce compte** : ses identifiants (code enseigne, clé privée) servent à afficher la carte des points relais sur le site.
- **Colissimo** : étiquettes achetables en ligne sans contrat.
- **Les étiquettes ne seront pas générées par le site en v1** : pour chaque commande, elle crée l'étiquette sur le site du transporteur à partir de l'adresse de la commande. À 10-15 commandes par mois, ça reste raisonnable ; l'automatisation passe par des modules payants, à envisager plus tard.

| Question | Réponse |
|---|---|
| OK pour ouvrir un compte Mondial Relay Offre Start ? Date ? | |
| OK pour créer les étiquettes à la main sur les sites des transporteurs ? | |

### 5.4 Emballage et poids

Le calcul automatique des frais de port se fait au poids : **poids des articles + poids de l'emballage**. Les poids des produits de test du site vont de 60 à 260 g (estimations, pas de vraies pesées) : une commande de 1 à 3 articles tiendrait presque toujours sous 500 g.

| Question | Réponse |
|---|---|
| Quel emballage : enveloppe, pochette, boîte ? Papier de soie, carte, goodies ? | |
| **Poids moyen de l'emballage** (à peser) | |
| Article le plus lourd de sa gamme (sac ?) et son poids | |
| Peut-elle **peser chaque création** (balance de cuisine) ? Le poids est obligatoire sur chaque fiche | |
| Taille du plus grand colis (pour les limites de dimensions) | |

> **Reco paliers** : 0–250 g · 250–500 g · 500 g–1 kg · 1–2 kg · 2–5 kg. Au-delà : très improbable, commande à traiter par le formulaire de contact.

### 5.5 Combien facturer à la cliente ?

Ordres de grandeur 2026 relevés le 11/09 — **à revérifier sur ses propres comptes**. Attention : en franchise de TVA, elle ne récupère pas la TVA sur les étiquettes, donc c'est le **prix TTC** qui compte pour elle.

| Transporteur | ≤ 250 g | ≤ 500 g | ≤ 1 kg |
|---|---|---|---|
| Mondial Relay point relais (tarif particulier) | ≈ 4,15 € | — | ≈ 5,99 € |
| Mondial Relay Offre Start (pro) | dès 3,09 € HT | | |
| Colissimo en ligne — domicile | 5,49 € | 7,59 € | 9,59 € |
| Colissimo en ligne — point retrait | 4,79 € | 6,89 € | 8,89 € |
| Chronopost Chrono 13 (express) | ≈ 23,40 € TTC (0–1 kg) | | |

| Question | Options | Réponse |
|---|---|---|
| Politique de prix | a) coût réel arrondi (ex. 4,90 €) · b) forfait unique par mode · c) léger lissage pour absorber l'emballage | |
| Prix affiché par mode et par palier | à remplir ensemble si elle a ses tarifs | |

### 5.6 Livraison offerte (franco)

Le site affiche aujourd'hui « livraison offerte dès **49 €** » (montant modifiable dans l'admin).

| Question | Reco | Réponse |
|---|---|---|
| Garder 49 € ? (≈ 2 à 4 articles au panier moyen actuel) | Oui | |
| Offerte sur quel(s) mode(s) ? | **Point relais uniquement** ; le domicile reste payant | |

### 5.7 Délais et zone

| Question | Reco | Réponse |
|---|---|---|
| Délai d'expédition annoncé (elle a un autre travail) | « Expédié sous 3 à 5 jours ouvrés » — mieux vaut sous-promettre | |
| Jours de dépôt habituels | | |
| Zone : France métropolitaine uniquement ? Corse incluse ? | Métropole + Corse si les transporteurs l'acceptent au même tarif | |
| Comment signaler congés et marchés ? | Bandeau du haut de site (déjà en place, modifiable par elle) | |

### 5.8 Remise en main propre

C'est ce mode qui fait vivre le statut « Prête pour retrait » et son email, déjà développés au lot 7.

| Question | Reco | Réponse |
|---|---|---|
| On la garde en v1 ? | Oui, cohérent avec ses marchés | |
| Où : domicile, atelier, marchés ? | | |
| Pour quelle zone (ville, codes postaux, département) ? | Limiter aux codes postaux proches, sinon tout le pays la verra | |
| Créneaux, prise de rendez-vous | Elle écrit lieu + date + heure dans la commande → l'email « Prête pour retrait » part automatiquement | |
| **Paiement : en ligne avant, ou sur place ?** | **En ligne avant** (évite les lapins et sécurise le stock d'une série limitée) ; sur place en option | |
| Si sur place : espèces ? carte (a-t-elle un terminal type SumUp pour ses marchés) ? | | |
| Délai maximal pour venir chercher avant annulation | 15 jours | |

### 5.9 Retours et rétractation

Vente en ligne à des particuliers = **droit de rétractation de 14 jours obligatoire**, avec des exceptions pour les pièces faites sur mesure. Ces réponses alimentent la page « Livraison et retours » et les CGV.

| Question | Réponse |
|---|---|
| Frais de retour à la charge de la cliente ? (possible si c'est écrit dans les CGV) | |
| Conditions : article non porté, étiquettes, emballage d'origine ? | |
| Échange possible, ou remboursement uniquement ? | |
| Une création avec un tissu choisi via le formulaire de contact : considérée comme personnalisée (exclue du retour) ? | |

---

## 6. Checkout — ce dont j'ai besoin pour le lot 6b (5 min)

| Question | Reco | Réponse |
|---|---|---|
| OK pour un tunnel **aux couleurs et typos** de la marque, mais de structure standard Magento ? | Oui (gratuit, fiable, sans maintenance lourde) | |
| Tunnel en **2 étapes** natives (Livraison → Paiement et récapitulatif) au lieu des 3 prévues dans la spec ? | Garder 2 : plus court | |
| Achat sans compte (invité) : déjà activé, création de compte proposée après la commande | Oui | |
| Téléphone obligatoire ? (utile aux SMS des transporteurs) | Oui | |
| Case « J'accepte les CGV » obligatoire → **il faut les CGV** | Oui | |
| Case d'inscription à la newsletter dans le tunnel (non pré-cochée) ? | Oui | |
| Un champ « Message pour Céline » (cadeau, précision) ? | v2 — pas prévu nativement, demande du développement | |
| Textes des pages de confirmation (paiement accepté, virement en attente, retrait) : elle les relit ? | Je propose, elle valide | |

---

## 7. Juridique, domaine et contenus nécessaires pour ouvrir les ventes (10 min)

Mollie vérifie le site avant d'activer les paiements réels : il doit être **en ligne**, avec des produits, leurs prix et leurs descriptions. Les CGV et les mentions légales sont de toute façon obligatoires avant la première vente.

| Sujet | Question | Réponse |
|---|---|---|
| **Domaine** | `madame-aiguille.fr` est-il réservé ? À son nom ? Et `madameaiguille.fr` en redirection ? | |
| Email pro | Une adresse `contact@madame-aiguille.fr` ? Qui reçoit les commandes et les messages du formulaire ? | |
| **CGV** | Qui rédige ? (elle, sur modèle, avec ses réponses des §3 à §5 ; relecture CMA ou juriste conseillée) Pour quand ? | |
| Mentions légales | Nom, adresse, SIRET, médiateur ; l'hébergeur, c'est moi qui fournis les informations | |
| Confidentialité | Modèle adapté (newsletter, formulaire de contact avec photo conservée 30 jours) | |
| Produits réels | Combien de fiches prêtes pour le lancement ? Photos au format portrait **4:5**, poids, descriptions | |
| Textes | « À propos / L'histoire », blocs de l'accueil | |

> **Reco** — Je mets une **préproduction en ligne sur le vrai domaine** dès que possible, pour lancer la vérification Mollie en parallèle au lieu d'attendre la fin du projet.

---

## 8. Si le temps le permet — points ouverts non bloquants

- [ ] **Catégories réelles** (celles en place viennent des maquettes)
- [ ] Produits épuisés **visibles** en fin de liste avec la mention « Épuisé » : confirmer ce choix
- [ ] Alerte « Me prévenir » quand une création revient : utile ? (la newsletter n'est pas une alerte ciblée)
- [ ] Liens réels des réseaux sociaux
- [ ] Destination du bouton « Voir toutes les nouveautés »
- [ ] Code promo de lancement ? (le champ est prêt, masqué)
- [ ] Relecture des emails automatiques (confirmation, expédition, bienvenue)
- [ ] Dates de marchés à venir pour le bloc Actualités

---

## 9. Récap de fin de call — décisions et actions

| # | Action | Qui | Pour quand |
|---|---|---|---|
| 1 | Créer le compte Mollie et m'inviter | Céline | |
| 2 | Ouvrir le compte Mondial Relay Offre Start et me transmettre les identifiants | Céline | |
| 3 | Peser l'emballage type et quelques créations | Céline | |
| 4 | Me donner ses tarifs transporteurs / sa politique de prix | Céline | |
| 5 | Confirmer le domaine (titulaire) | Céline / Pierre | |
| 6 | Rédiger CGV, mentions légales, page Livraison et retours | Céline (+ relecture) | |
| 7 | Choisir un médiateur de la consommation | Céline | |
| 8 | Mettre la préproduction en ligne sur le domaine | Pierre | |
| 9 | Développer le lot 5 avec les clés de test | Pierre | dès réception |
| 10 | | | |

**Prochain point** : ____________

---

## Annexe — Analyse technique (pour moi, pas à aborder avec Céline)

En relisant le plan et l'état du code, cinq points techniques vont peser sur le lot 5 au-delà des réponses de Céline :

1. **Les *table rates* natifs ne font qu'une seule méthode.** Le carrier `tablerate` de Magento porte **une** grille par site web : impossible d'avoir en natif trois méthodes Colissimo / Mondial Relay / Chronopost avec chacune sa grille, comme le prévoit le plan. Deux options :
   - **carriers maison** dans un module `MadameAiguille` (une classe par transporteur, grilles CSV versionnées, franco par mode, refus explicite si un poids manque) : environ une journée, testable, et conforme à la règle « grilles versionnées » du prompt du lot 5. **Ma préférence** ;
   - **Owebia Advanced Shipping** (gratuit, OSL) : puissant mais une syntaxe de plus à maintenir, et une incompatibilité signalée sur 2.4.7-p1 → à vérifier sur 2.4.9 avant tout `composer require`.
2. **Point relais Mondial Relay** : module officiel développé par Magentix (payant, prix non affiché), O'Pickup d'Owebia (200 €), ou intégration maison du widget officiel Mondial Relay dans le checkout Knockout. Tous demandent les identifiants Offre Start de Céline. Sendcloud est écarté : ≥ 40 €/mois pour 15 commandes. Le spike reste la première tâche du lot, mais il peut démarrer **dès aujourd'hui** avec les identifiants de test de Mondial Relay.
3. **Webhooks Mollie : il faut une URL publique.** En local il faut un tunnel (ngrok) ; mieux vaut une préproduction sur le VPS. Si elle est protégée par une authentification HTTP, **exclure la route du webhook** Mollie, sinon les paiements ne remontent jamais.
4. **La vérification Mollie impose un site en ligne** → avancer une partie du lot 8 (« 8a » : domaine, DNS, préproduction HTTPS, SMTP) en parallèle du lot 5. Ordre proposé : **5 + 8a → 6b → 8**.
5. **Si le virement passe par Mollie**, le plan change en mieux :
   - `payment/mollie_methods_banktransfer/order_status_pending` → `madameaiguille_pending_payment` ;
   - `payment/mollie_general/order_status_processing` → `madameaiguille_payment_received` → l'observateur du lot 7 envoie l'email « Paiement reçu » tout seul ;
   - `due_days` (1 à 100, 14 par défaut) remplace le cron d'expiration maison. Reste à vérifier la relibération du stock à l'expiration, et à revoir l'email de virement du lot 7 (plus de RIB perso) ;
   - le virement natif `banktransfer` n'est plus nécessaire.

Configuration à corriger quelles que soient les réponses : `shipping/origin/*` (US, 90034 → adresse d'expédition de Céline), `tax/defaults/country` → FR, `payment/checkmo/active` → 0, `carriers/flatrate/active` → 0 quand les vrais carriers seront prêts, méthodes Mollie limitées à celles qu'elle retient (38 exposées aujourd'hui). Le carrier natif `freeshipping` affiche le franco comme une méthode à part : à fondre dans les carriers maison si elle veut la gratuité sur le seul point relais.

---

### Sources consultées le 11/09/2026

- Tarifs Mollie France : https://www.mollie.com/fr/pricing
- Création et vérification d'un compte Mollie : https://docs.mollie.com/docs/create-an-account
- Mollie Magento 2, test et mise en production (webhooks) : https://docs.mollie.com/docs/magento-2-test-and-go-live
- Mondial Relay Offre Start, prérequis : https://www.mondialrelay.fr/faq-pro/envoyer-un-colis/quels-sont-les-pre-requis-de-volume-pour-ouvrir-un-compte-offre-start/
- Mondial Relay, module Magento 2 : https://www.mondialrelay.fr/faq-pro/modules-mise-en-place-technique/existe-til-un-module-pour-une-boutique-sous-magento-2/ · https://mondialrelay.magentix.fr/fr/magento-2/ · https://fr.store.owebia.com/magento2-module-opickup-mondial-relay.html
- Grille Mondial Relay particuliers 2026 (source tierce) : https://margeoapp.com/blog/tarifs-mondial-relay-2026
- Colissimo en ligne 2026 : https://www.laposte.fr/professionnel/colissimo-en-ligne/tarifs
- Chronopost 2026 (source tierce) : https://tarifs-postaux.fr/tarif-chronopost.htm
- Sendcloud, tarifs : https://saask.fr/softwares/sendcloud/prix/
- Owebia Advanced Shipping : https://github.com/owebia/magento2-module-advanced-shipping
- Franchise en base de TVA 2026 : https://www.indy.fr/blog/unification-seuils-tva-2026/
- Médiateur de la consommation : https://lentreprisefacile.fr/blog/mediateur-consommation-micro-entrepreneur-obligatoire-2026/
