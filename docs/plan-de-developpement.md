# Plan de développement — Madame Aiguille

Document interne — v2.8 (09/10/2026), lots 6a, 3, 4, 7, 5 et **6b livrés** ; prochain lot : **8a**, dès que domaine et serveur sont disponibles

> **v2.8** — **Lot 6b livré** sur `lot-6b-habillage-tunnel` (`3416d43`, `2a1db4f`, `670dfdf`, `342bc19`, `2f82598`, `f80ceaf` et clôture). Thème Luma enfant, identité de marque, récapitulatif inspiré des maquettes, composants du lot 5 habillés sans changer leurs calculs, téléphone requis, CGV natives obligatoires et newsletter facultative décochée. Succès / échec Hyvä français, cartes de livraison par ViewModel et styleguide. Recette réelle à 1440 / 390 px et administration dans la session fournie par Pierre ; 106 tests / 282 assertions. Deux commandes de retrait de test créées puis annulées dans l’admin. Limites explicites : paiement / retour Mollie complet, emails reçus, activation reCAPTCHA et facture / avoir avec emballage à recetter au **8a**. Méthodes Mollie provisoires à valider avec Céline. Prochain prompt : `docs/prompts/prompt-lot8a.md`.

> **v2.7** — **Lot 5 livré** sur `codex/lot-5-livraison-paiements` (`8982ecb` → documentation), fusionné dans `main`. Spike Mondial Relay conclu par l'**intégration maison du widget officiel** : il ne demande que le code enseigne, `BDTEST` en attendant l'Offre Start. Livrés : grille au poids versionnée et rejouable, **franco à 60 € porté par une seule valeur** (la barre du panier et le tarif ne peuvent plus diverger, `freeshipping` coupé), point relais, **retrait à l'atelier payé sur place avec rendez-vous réservé à la commande** (collision gérée par clé unique), **emballage cadeau payant**, statuts du lot 7 câblés sur Mollie et sur l'expédition, mention de TVA sur factures et emails, affichage aligné, TikTok. Nouveau module `MadameAiguille_Checkout`. **Écart assumé par Pierre** : il a réactivé dans l'admin plusieurs méthodes Mollie (wallets, Bancontact, iDEAL, Wero, Klarna) après la restriction à la carte, et souhaite les garder — à trancher avec la CGV. **8a différé** faute d'accès au domaine et au serveur : la validation Mollie reste bloquée.

> **v2.6** — Les dernières questions du call sont tranchées. **Site monolingue français**, et **zone réduite aux pays francophones de l'UE** : croisée avec la desserte Mondial Relay, elle se limite à **France, Belgique, Luxembourg** — la Suisse, citée dans la décision, est hors UE et hors desserte Mondial Relay, et Pierre l'a écartée le jour même. **Délai d'expédition annoncé : 4 à 5 jours.** Deux vrais développements s'ajoutent au lot 5 : un **sélecteur de date et d'heure de retrait** dans le tunnel, auto-confirmé, avec lieu unique paramétrable et aperçu cartographique ; et une **option d'emballage cadeau payante à 2 €**, prix réglable en back-office. Tous deux vivent dans le checkout **Knockout** du fallback Luma, pas en Hyvä, et ne sont natifs ni l'un ni l'autre — l'effort du lot repasse de **L à XL**. Contenus juridiques génériques **écrits le jour même** : les quatre pages portent un brouillon marqué comme tel, aligné sur ces décisions.

> **v2.5** — Quatre arbitrages de Pierre closent les points les plus structurants du lot 5 : **zone de vente limitée aux pays desservis par Mondial Relay** (BE, LU, NL, DE, AT, IT, ES, PT — à confirmer sur l'offre pro) ; **aucune TVA facturée**, y compris en Europe, Céline étant en franchise de base — à revoir si les ventes à distance intra-UE dépassent 10 000 € sur l'année ; **franco à 60 €** sur le point relais ; **annulation d'un retrait au rendez-vous non honoré**, donc sans cron, par une action de Céline dans l'administration. Restent ouverts : langue du site, carte du lieu de retrait, prise de rendez-vous — devenue structurante puisqu'elle conditionne la règle d'annulation —, délai d'expédition annoncé et emballage cadeau.

> **v2.4** — **Call Céline du 11/09/2026 dépouillé** (compte rendu complet : `docs/brief-call-celine-2026-09-11.md`). Le lot 5 est recadré et s'allège sur plusieurs points, mais s'alourdit sur trois autres.
>
> Tranché : **entreprise individuelle en franchise de TVA** (SIRET 940 760 911 00013, Saint-Épain 37800) ; **carte bancaire uniquement** en ligne, via Mollie dont le compte est créé ; **Mondial Relay point relais comme unique transporteur** ; **click & collect payé sur place** (TPE ou espèces), jeudi 9 h-18 h et vendredi 9 h-11 h 30.
>
> Tombe du périmètre : le virement SEPA et tout ce qui en découlait (cron d'expiration, relance à J-2, RIB dans l'email), Colissimo et Chronopost — et avec eux **le problème des grilles multiples** : un seul transporteur tient dans le carrier `tablerate` natif, les carriers maison envisagés en annexe du brief ne sont plus nécessaires.
>
> S'ajoute : **vente dans toute l'Europe** (périmètre à préciser, impact TVA et CGV à faire confirmer), **TikTok** au pied de page et **Pinterest** à retirer, **emballage cadeau et carte personnalisée** à chiffrer.
>
> Nouvelle dépendance dure : **Mollie ne valide le compte qu'avec un site en ligne et un paiement réel**. La mise en ligne d'une préproduction (lot **8a**) passe donc *avant* la fin du lot 5. Ordre retenu : **5 + 8a → 6b → 8**.

> **v2.3** — Lot 7 fusionné dans `main` et poussé. Une correction du 11/09/2026 a suivi la recette dans l'administration : l'identifiant des deux gabarits d'email doit être le chemin de configuration avec des underscores, sinon toute la section *Emails de vente* est inaccessible. Le **rituel de fin de lot** est désormais écrit (`documentation-theme.md` §25, rappelé dans `AGENTS.md`) : recette écrans **et** administration, documentation, fusion, push, prompt du lot suivant. Relevé de configuration du 11/09 : `general/country/default` est passé à FR et la livraison gratuite à 49 € est réellement active ; `shipping/origin/country_id` et `tax/defaults/country` valent toujours US.

> **v2.2** — Lot 7 livré sur `codex/lot-7-compte-emails` (`d889482` → documentation). Les deux décisions ouvertes ont été tranchées : **les six statuts sont livrés, « prête pour retrait » comprise**, et **les gabarits d'email restent ceux de Magento**, habillés par une enveloppe commune — Céline garde la main dessus depuis l'administration et les montées de version ne réécrivent rien. Ce qui n'a pas pu être bouclé est isolé : le parcours réel des statuts et le contenu de l'email de virement attendent Mollie (lot 5), les modalités de retrait attendent Céline, la délivrabilité attend le domaine (lot 8).

> **v2.1** — **Réorganisation décidée par Pierre.** Les lots 5 (livraison et paiements) et 6b (habillage du checkout) sont mis **en attente** de deux éléments extérieurs au code : la création du compte marchand Mollie, et un brief avec Céline sur les modes de livraison. Le développement se poursuit donc par le **lot 7** (compte client, emails, statuts de commande), dont l'essentiel ne dépend pas des paiements. **Décision tranchée : le prestataire de paiement est Mollie, pas Stripe** — Mollie 3.1.3 et sa compatibilité Hyvä sont déjà installés et activés dans le projet ; Stripe ne l'a jamais été et sort du périmètre.

> **v2.0** — Lot 4 livré sur `codex/lot-4-panier`. Décisions de Pierre : franco affiché dès la v1 au seuil de 49 € des maquettes, montant configurable dans l'admin ; code promo masqué, activable sans toucher au layout. L'estimateur n'affiche que les méthodes réellement retournées par Magento — à ce jour Flat Rate seul. Trois points restent suspendus au lot 5 : la règle de livraison gratuite qui doit correspondre au seuil affiché, le pays par défaut encore réglé sur les États-Unis, et les tarifs réels des transporteurs.

> **v1.9** — Ordre de reprise corrigé à la demande de Pierre : le lot 4 Panier et mini-panier suit le lot 3. Son estimateur utilisera les méthodes actuellement configurées et sera recetté de nouveau avec les vrais tarifs après le lot 5.

> **v1.8** — Lot 3 validé par Pierre. Logos officiels intégrés au header/footer et au favicon ; toutes les surcharges visuelles, y compris le formulaire Contact, résident dans le thème enfant. Les contenus et configurations restant avant production sont recensés dans `documentation-theme.md` §20.

> **v1.7** — Étapes 4 et 5 validées par Pierre. Module Contact livré avec stockage privé et purge à 30 jours configurable. Étape 6 : 404 interactive accessible, panier vide avec deux nouveautés disponibles, vrais rendus ajoutés au styleguide et recette transversale à 390 / 1440 px. Lot 3 en attente de validation finale.

> **v1.6** — Étape 3 validée par Pierre. Étape 4 : gabarit commun des pages CMS, contenu générique À propos sans écrasement des modifications, page Nos tissus avec quatre références éditables et lien dans le footer. Les pages juridiques restent « À rédiger ». Recette des six URL et contrôles à 390 / 1440 px effectués ; validation Pierre en attente avant le formulaire de contact.

> **v1.5** — Étape 2 validée par Pierre. Étape 3 : accueil complet en blocs CMS, Nouveautés automatiques sans produits épuisés, Incontournables par attribut produit, actualités éditoriales, galerie et newsletter native avec double opt-in. Purge de cache étendue aux sélections de l'accueil ; recette à 390 / 1440 px effectuée.

> **v1.4** — Pierre valide le lot 6a après une commande. Branche `lot-3-accueil-cms-contact` créée depuis `7f46bb1`. Étape 2 : titre de section factorisé, réutilisé par les catégories, le slider produit et le styleguide ; recette à 390 / 1440 px effectuée.

> **v1.3** — Luma Checkout 1.1.7 et Theme Fallback 1.0.4 installés sur `lot-6a-checkout`. Formulaire invité et isolation des scripts contrôlés ; aucun habillage ni changement de paiement. Le fallback change la page entière vers Luma (correction de la description initiale). Décisions du lot 3 consignées ci-dessous ; développement du lot 3 non commencé.

> **v1.2** — Lot 2 livré (catalogue). Décisions consignées ci-dessous ; nouvelles questions pour Céline (page « Boutique » globale, newsletter) et pour Pierre (WebP).
>
> **v1.1** — Décision checkout : **Luma Fallback Checkout** (`hyva-themes/magento2-luma-checkout`, gratuit, OSL-3.0). Hyvä Checkout (1 000 € de licence) est écarté. Lots 5 et 6 mis à jour en conséquence.

Découpage en lots du développement restant, après les fondations (lot 1). Pour chaque lot : périmètre, dépendances, points d'incertitude et effort relatif (S · M · L · XL — à l'échelle d'un développeur seul, le lot 1 valant **M**).

Sources : `cahier-des-charges.md`, `specification-fonctionnelle.md`, `architecture-technique.md`, maquettes `maquettes-direction-artistique/`.

## État après le lot 1

Livré et validé :

- dépôt git, `.gitignore` Magento, branche `lot-1-fondations` ;
- thème enfant `MadameAiguille/default` (parent `Hyva/default` 1.5.2), chaîne Tailwind v4 (`hyva.config.json`, `@theme`, `@source inline` pour le CMS) ;
- fontes Britney / Sentient auto-hébergées et préchargées ; tokens de couleur, échelle typographique, rayons, ombres, composants de base (boutons, champs, alertes, cartes, badges, feston) ; page de contrôle `/styleguide` ;
- module `MadameAiguille_Theme` : route `/styleguide`, ViewModel `SocialLinks`, configuration admin *Réseaux sociaux*, data patches (pages CMS légales vides, bloc bandeau) ;
- header (logo centré, bandeau CMS, menu catégories 2 niveaux, tiroir mobile) et footer (4 colonnes / accordéon).

Reste ouvert, transverse à tous les lots : les **photos produit finales** au ratio 4:5 et les **textes validés** (pages légales, À propos, descriptions). Les logos JPEG fournis dans `docs/logos/` sont intégrés ; une déclinaison vectorielle reste une amélioration facultative, pas un prérequis.

## État après le lot 2 (10/09/2026)

Livré et validé (commits `92500b5` → `ccabeab`, détail dans `documentation-theme.md` §8 à §12) :

- modèle de données : attributs `taille` (swatch texte), `serie_limitee`, `taille_serie`, `composition`, `dimensions`, `entretien` ; `weight` obligatoire, en kg ; attribute set « Création » ; seuil de rareté et mention sous le prix en configuration admin ; `created_at` triable ;
- ViewModels `LimitedSeries` (toutes les règles séries limitées / stock / badges, testé), `Sorting`, `Characteristics` ;
- page catégorie et page de résultats sur le même gabarit (une colonne, sans facettes, tri Nouveautés / Prix, épuisés regroupés en fin de page sous « Séries terminées », pagination 44 px, états vides) ;
- fiche produit complète (galerie 4:5 + lightbox natif, sélecteur de taille, quantité plafonnée au stock, encarts rareté et contact, rassurance en bloc CMS, caractéristiques, « Vous aimerez aussi », barre d'achat mobile, variante épuisée) ;
- fraîcheur des badges : purge des caches produit à chaque réservation MSI et à chaque enregistrement de stock, cron nocturne pour le badge « Nouveauté » ;
- jeux de données de test versionnés (`docs/jeux-de-donnees/`), styleguide enrichi, documentation à jour.

**Décisions prises (Pierre, 05/09/2026)** : produits épuisés **visibles** en fin de liste ; **lightbox natif Hyvä** restylé (pas de librairie) ; configurable à une seule taille en stock : **sélecteur affiché**, option épuisée barrée ; données d'essai Sneakers / T-Shirts / Jordan **supprimées**.

**Points ouverts issus du lot 2**

- **WebP** : Magento 2.4.9 ne génère pas de WebP nativement ; les tailles 4:5 sont en place. Reporté au lot 8 (perf) ou module tiers gratuit à valider (compatibilité Hyvä) — Pierre.
- ~~**Page « Boutique » globale**~~ : l'accueil reste la vitrine ; « Voir toute la boutique » pointe vers l'accueil (décision Pierre, lot 3).
- **Champ « Me prévenir »** (catégorie vide, fiche épuisée) : non livré. La newsletter est active, mais une alerte ciblée par produit demanderait un comportement distinct à décider.
- **Catégories réelles** : celles en base sont celles des maquettes (`docs/jeux-de-donnees/categories.php`) ; à définir avec Céline.
- ~~**Contact** : lecture de `?product=<sku>`~~ — livré dans le module dédié, avec produit actif prérempli.
- **Suivi de la fraîcheur des caches** en conditions réelles : à recetter au lot 4 (première commande de test) ; le cron Magento doit tourner (lot 8).

## État après le lot 4 (10/09/2026)

Livré (commits `3316965` → `eb54441`, détail dans `documentation-theme.md` §20) :

- ViewModels `Cart\FreeShipping`, `Cart\Stock`, `Cart\Summary` et `Cart\Options`, plugin sur la section privée `cart` ; 24 tests unitaires sur le module ;
- page panier : gabarit deux colonnes, tableau à quatre intitulés au-delà de 768 px et cartes empilées en dessous, quantité plafonnée au **stock vendable** (enfant simple pris en compte sur un configurable), rareté remontée dans la ligne, actions à 44 px ;
- barre de franco à 49 €, calculée côté serveur, qui passe en succès et se tait une fois le seuil franchi ;
- récapitulatif : totaux natifs restylés, bouton « Passer commande » conservant la fenêtre d'authentification, bloc CMS `cart_reassurance` éditable ;
- estimation de livraison retemplatée, calcul strictement natif, groupée par transporteur ;
- mini-panier en tiroir : 380 px desktop / plein écran mobile, liste défilante au-delà de quatre articles, pied fixe, quantité plafonnée, fermeture souris/clavier/clic extérieur ;
- traductions des messages de panier et de stock : les paquets de langue Magento 2.4.9 sont vides, sans elles la boutique affichait « Shopping Cart » et « SousTotal » ;
- styleguide : barre de franco dans ses trois états, ligne de panier normale / rare / incommandable, récapitulatif et pied de tiroir.

**Points ouverts, tous rattachés au lot 5** : le franco de 49 € est un affichage tant qu'aucune règle de livraison gratuite ne lui correspond ; le pays par défaut de l'estimateur reste « États-Unis » ; seul Flat Rate est actif, à 5 € par article. Un comportement natif est par ailleurs à trancher : un produit désactivé pendant qu'il est au panier voit sa ligne retirée **sans message**.

### Décisions de Pierre pour le lot 3 — 10/09/2026

Ces décisions remplacent les questions ouvertes correspondantes ci-dessus et dans le périmètre historique du lot 3 :

- **Actualités** : bloc CMS pour marchés, congés et annonces, en complément des Nouveautés produit automatiques.
- **Newsletter** : activée au lancement, module natif avec double opt-in. Le comportement « Me prévenir » d'une série précise reste à cadrer : une inscription à la newsletter n'est pas une alerte de réassort ciblée.
- **Nos tissus** : page CMS incluse dans le lot 3.
- **Navigation** : CTA contextuels vers la catégorie concernée ; « Voir toute la boutique » vers l'accueil. Pas de nouvelle catégorie globale Boutique. Le CTA du hero devra exposer une destination catégorie éditable ; la destination de « Voir toutes les nouveautés » reste à préciser.
- **Contact** : module dédié `MadameAiguille_Contact`, nom, email, produit prérempli via `?product=<sku>`, objet, message limité à 1 000 caractères, case de consentement. Photo optionnelle disponible sur desktop et mobile : JPG/PNG, 5 Mo, validation MIME serveur et stockage **hors `pub/`**. Conservation fixée à 30 jours par défaut, configurable dans l’admin, avec purge quotidienne.
- **Contenus** : textes génériques éditables dans le back-office et emplacements documentés ; aucune biographie, date de marché ou promesse de délai fictive présentée comme validée. Les pages légales restent « À rédiger » sans texte fourni.
- **404** : même direction artistique que les états vides avec un petit easter egg ludique. Proposition : bobine déroulée, « On a perdu le fil… », aiguille animée au clic, accessible au clavier et respectant la préférence de réduction des animations.

Ordre terminé : **1)** lot 6a ; **2)** composant partagé ; **3)** accueil ; **4)** pages CMS et Nos tissus ; **5)** contact ; **6)** 404, panier vide et recette transversale. Pierre a validé le lot 3. La branche `lot-3-accueil-cms-contact` a été créée depuis le lot 6a validé (`7f46bb1`) ; son dernier commit d'implémentation est `9c48987`.

## État après le lot 7 (10/09/2026)

Livré (commits `d889482` → `8f33ac5` + documentation, détail dans `documentation-theme.md` §21 et §22) :

- **six statuts de commande** créés par data patch idempotent depuis une table unique (`Model\Order\StatusConfig`) : en attente de paiement, paiement reçu, en préparation, expédiée, prête pour retrait, livrée. Libellés français identiques côté cliente et côté back-office ; deux `setup:upgrade` de suite ne créent aucun doublon ;
- ViewModel `Order\Progress` : phrase d'avancement, variante de badge, étape courante et **frise datée** construite sur l'historique natif des statuts. Aucun `switch` sur un code de statut dans un gabarit ; les anciennes commandes en statut natif sont ramenées au statut équivalent ;
- **pages du compte** en Hyvä : navigation latérale de 280 px avec l'identité de la cliente et accordéon sous 768 px, tableau de bord, « Mes commandes » en cartes portant une phrase et non un statut sec, détail de commande avec frise, adresses, informations personnelles, mot de passe oublié et réinitialisation. L'opt-in d'assistance distante, hors périmètre, est retiré ;
- **dictionnaire `i18n/fr_FR.csv`** de 220 lignes : les paquets de langue Magento 2.4.9 sont vides, sans lui le compte affichait « Sign In », « Order # » ou « My Account » ;
- **emails** : enveloppe commune au logo et aux couleurs de la marque (`Magento_Email/email/header.html` et `footer.html`, styles LESS), dont héritent tous les emails du site ; deux notifications métier « paiement reçu » et « prête pour retrait », déclenchées par un observateur sur le changement réel de statut et configurables dans *Emails de vente* ; accusé de réception du formulaire de contact réaligné ;
- **styleguide** : tableau des six statuts, une carte de commande par statut, les deux frises et la navigation du compte, tous produits par le vrai `Order\Progress` sur des commandes d'exemple non enregistrées ;
- 39 tests unitaires sur le module.

**Points ouverts** : le parcours réel des six statuts et le contenu de l'email de virement (RIB, référence, délai d'annulation) attendent Mollie — **lot 5** ; les modalités de la remise en main propre attendent Céline ; la délivrabilité (SPF, DKIM, DMARC) et le SMTP attendent le domaine — **lot 8**. Le contrôle visuel a été fait à 1440 et 390 px.

## État après le lot 5 (09/10/2026)

- **Le tunnel vend pour de vrai, en test** : Mondial Relay au poids, retrait sur rendez-vous, paiement Mollie ou sur place, emballage cadeau, franco à 60 €. Tout se règle dans l'admin sauf la grille, versionnée en CSV.
- **Incertitudes levées** : module Mondial Relay (widget maison, sans extension), carriers maison (inutiles, `tablerate` suffit), franco (une seule valeur), Gift Wrapping (construit en Open Source), réservation des créneaux (clé unique en base, pas de verrou applicatif).
- **Ce qui bloque encore la vente** : 8a (domaine, préproduction, SMTP, webhook Mollie, paiement réel), code enseigne et tarifs Mondial Relay de Céline, contenus juridiques, choix final des moyens de paiement Mollie. Détail : `documentation-theme.md` §23.
- **État historique au lot 5** : les composants étaient fonctionnels mais bruts. Leur habillage a depuis été livré au 6b (§27 de la documentation).

## État après le lot 6b (09/10/2026)

Correctif après livraison : hero de l’accueil centré et plafonné à 1440 px (`8316318`), contrôlé à 1440 / 390 / 2560 px. Détails : `docs/recettes/correction-hero.md`. Le périmètre et l’ordre des lots restent identiques.

- Le fallback utilise `frontend/MadameAiguille/checkout`, parent Luma ; le panier, le compte et les confirmations restent Hyvä.
- Les décisions de Pierre sont appliquées : récapitulatif des maquettes, téléphone et CGV obligatoires, newsletter non pré-cochée. Pas de nouveau champ message sans arbitrage ; message cadeau natif conservé.
- Recette et preuves : `docs/recettes/lot-6b.md`. Commandes `000000021` et `000000022` annulées, créneaux libérés, aucun abonné créé sans consentement.
- Domaine, serveur et SMTP restent à fournir pour **8a**, ainsi que les clés reCAPTCHA et le code enseigne Mondial Relay de production. Le choix Mollie doit être validé avec Céline.
- La branche distincte `traduction-fr` n’est pas intégrée à ce lot ; prévoir son arbitrage avant un provisionnement neuf. Les WOFF2 originaux sont à provisionner dans les deux thèmes (README).

## Vue d'ensemble

| Lot | Titre | Dépend de | Effort | Risque |
|---|---|---|---|---|
| 2 | Catalogue : modèle de données, catégorie, fiche produit | 1 | **L** | faible |
| 3 | Accueil, pages CMS, formulaire de contact | 2 | **L** | moyen (contenu) |
| 4 | Panier et mini-panier | 2, config livraison du lot 5 | **M** | faible |
| 5 | Livraison et paiements (Mondial Relay point relais, **Mollie CB**, click & collect avec rendez-vous, emballage cadeau) + **8a mise en ligne** | 2, 6a + identifiants Mondial Relay + domaine | **XL** | moyen |
| 6 | Checkout Luma fallback : installation (6a) et habillage (6b) — **livrés** | 5 | **L** | moyen |
| 7 | Compte client, emails transactionnels, statuts de commande — **livré** | 3 ; recette finale après 5 et 6b | **M** | faible |
| 8 | Back-office, exploitation, mise en production | tous | **M** | moyen |

Ordre réalisé : **2 → 6a → 3 → 4 → 7 → 5 → 6b**. Ordre restant : **8a → 8** ; la validation Mollie impose domaine, HTTPS et paiement réel. 8a a été différé faute d’accès au domaine et au serveur.

Les lots 5 et 6b ont été livrés après le brief. La recette de bout en bout — paiement confirmé, tous les statuts et emails reçus — reste à rejouer sur la préproduction HTTPS du 8a. Le lot 7 avait été avancé pendant l’attente du brief.

Le lot 4 a construit le panier et l'estimateur avec les méthodes actuellement disponibles ; les tarifs et la barre de franco seront recettés de nouveau après la configuration réelle des transporteurs.

---

## Lot 2 — Catalogue — **livré le 10/09/2026**

**Périmètre** (tel que planifié ; réalisé intégralement, voir « État après le lot 2 »)

- Attributs catalogue via data patch du module (`Setup/Patch/Data/`) : `taille` (dropdown, 2 valeurs, utilisé pour les configurables), `serie_limitee` (booléen), `taille_serie` (entier). `weight` natif rendu **obligatoire** dans l'attribute set par défaut.
- Attribute set « Création » avec ces attributs et le poids en champ requis.
- Modélisation : produit simple (cas majoritaire), configurable à un seul axe `taille` (cas minoritaire) — cf. spécification §2.1.
- ViewModel `LimitedSeries` : mention « Série limitée — X pièces », « Plus que N exemplaires » sous le seuil de 3, état épuisé. Seuil en configuration admin (section *Madame Aiguille*).
- Page catégorie : grille 2 / 3 / 4 colonnes, carte produit (image 4:5, badges `badge-new` / `badge-limited` / `badge-scarce` / `badge-soldout`, prix tabulaire), tri (prix, nouveautés), pagination 44 px, produits épuisés en fin de liste **si** l'option « rester visible » est retenue. Pas de filtres à facettes (v1).
- Fiche produit : galerie 4:5 avec zoom (composant natif Hyvä restylé), sélecteur de taille (2 options, état épuisé barré), sélecteur de quantité plafonné au stock, ajout au panier, lien « Une question sur le tissu ou le motif ? » vers `/contact?product=<sku>`, fil d'Ariane.
- États vides : catégorie sans produit, recherche sans résultat (page de résultats sur le gabarit catégorie).
- `etc/view.xml` du thème : tailles d'images (vignette, galerie, zoom, mini-panier) au ratio 4:5, WebP.
- Jeu de données de test : une dizaine de produits avec photos de `brand/p-*.png`, pour la recette des lots suivants. Hors code (script ou import CSV), comme les catégories.

**Dépendances** : lot 1. Les catégories réelles restent à définir avec Céline (celles du lot 1 sont issues des maquettes).

**Incertitudes — tranchées le 05/09/2026**

- ~~Produit épuisé : visible et marqué, ou masqué ?~~ → **visible**, regroupé en fin de liste ; à exclure du bloc Nouveautés (lot 3).
- ~~Zoom natif ou lightbox plein écran ?~~ → **lightbox natif Hyvä** restylé.
- ~~Configurable avec un seul enfant en stock~~ → **sélecteur affiché**, option épuisée barrée.

**Effort : L.** Le gros du travail est le retemplating de deux pages denses (catégorie, fiche) plus le ViewModel.

---

## Lot 3 — Accueil, pages CMS, formulaire de contact — **livré et validé le 10/09/2026**

**Périmètre**

- Page d'accueil (`cms_index_index.xml` + blocs CMS + widgets) dans l'ordre validé : hero (visuel plafonné à 240 px sur mobile pour que « Nouveautés » soit sous le pli), **Nouveautés** (widget *New Products* natif basé sur `news_from_date`, produits épuisés exclus — plugin ou collection dédiée ; réutiliser la carte `product_list_item` et le ViewModel `LimitedSeries` du lot 2, dont le cron purge déjà les caches aux dates de nouveauté), Incontournables (widget produits sur sélection manuelle ou catégorie « Incontournables » non visible dans le menu), Histoire (bloc CMS + image atelier), Pourquoi choisir (4 arguments, bloc CMS), Instagram (galerie statique de 4 visuels, bloc CMS), Newsletter (`Magento_Newsletter`, sous réserve). Chaque bloc restylable par Céline sans code, dans la limite des classes déclarées en `@source inline`.
- Gabarit de mise en avant de section (titre display + filets ondulés + accroche + lien) réutilisé partout.
- Pages statiques : À propos, Livraison & retours, CGV, Mentions légales, Confidentialité — gabarit `1column` typographié (classe `prose` de hyva-modules, adaptée aux tokens), largeur de lecture ~65 caractères.
- **Formulaire de contact** — module custom léger dans `MadameAiguille_Theme` (ou module dédié `MadameAiguille_Contact`) : surcharge du contrôleur `contact/index/post` ou nouveau contrôleur ; champs nom, email, message, **pièce jointe** (JPG/PNG, 5 Mo, validation serveur du type MIME, stockage hors `pub/` ou envoi en pièce jointe d'email puis suppression), **produit concerné** pré-rempli depuis la fiche (`?product=`), reCAPTCHA natif, email à Céline + accusé de réception au visiteur. ViewModel `ContactForm`.
- Page 404 et page « panier vide » aux gabarits du Design System (états vides).

**Dépendances** : lot 2 pour les widgets produit (Nouveautés, Incontournables) et le lien depuis la fiche. Contenus (textes, photos) fournis par Céline — **principal facteur de délai**.

**Décisions appliquées**

- Actualités produit automatiques complétées par des blocs CMS éditoriaux pour les marchés, congés et annonces.
- Newsletter native activée avec double opt-in.
- Page « Nos tissus » livrée avec contenu générique modifiable dans l'admin.
- Pièce jointe JPG/PNG de 5 Mo maximum, validée côté serveur, conservée hors `pub/` et purgée selon une durée configurable de 30 jours par défaut.

**Effort : L.** L'accueil est du gabarit ; le formulaire de contact est le seul développement PHP significatif du projet côté front.

---

## Lot 4 — Panier et mini-panier — **livré le 10/09/2026**

**Périmètre** (réalisé intégralement, voir « État après le lot 4 »)

- Tiroir mini-panier (Design System §6.9) : 380 px desktop / plein écran mobile, barre de progression vers le franco (si retenu), liste défilante au-delà de 4 articles, pied fixe. Comportement natif Hyvä conservé (Alpine, `private-content`).
- Page panier : lignes (image 4:5, quantité plafonnée au stock, suppression), récapitulatif, **estimation des frais de port** dès le panier (bloc natif « Estimate Shipping », code postal → tarifs par transporteur), message « plus que X € pour la livraison offerte ».
- Messages d'ajout au panier (alerte succès avec le reste pour le franco).
- Cas limites : produit épuisé entre l'ajout et le passage en caisse, dernière pièce prise par une autre cliente (messages natifs restylés et traduits).

**Dépendances** : lot 2 (produits, stock) ; lot 5 pour que l'estimation affiche de vrais montants (les gabarits peuvent être faits avant avec un tarif provisoire).

**Incertitudes — tranchées le 10/09/2026 par Pierre**

- ~~Franco de port : montant à confirmer~~ → **49 €, affiché dès la v1**, montant configurable dans l'admin (0 masque la barre). À faire correspondre à une vraie règle de livraison gratuite au lot 5.
- ~~Code promo : masqué ou visible ?~~ → **masqué**, activable par un réglage admin sans toucher au layout ; un coupon déjà appliqué reste toujours visible et retirable.

**Effort : M.**

---

## Lot 5 — Livraison et paiements — **livré le 09/10/2026, hors 8a**

> **Prompt de reprise : `docs/prompts/prompt-lot5.md`.** Périmètre arrêté par le call Céline du 11/09/2026 — compte rendu et questions ouvertes dans `docs/brief-call-celine-2026-09-11.md`. **Livraison documentée dans `documentation-theme.md` §26** ; spike dans `docs/spike-mondial-relay.md`. Seul le **8a** reste à faire, différé par Pierre.

**Périmètre arrêté**

- **Mondial Relay point relais, transporteur unique.** Une seule grille au poids → le carrier **`tablerate` natif suffit** (`Weight vs. Destination`, condition déjà réglée sur `package_weight`). Grille versionnée en CSV avec un script d'import rejouable. Flat Rate à couper.
- **Sélecteur de point relais** : spike en tout début de lot, avec les identifiants **Offre Start** de Céline (compte à ouvrir ou transférer — il n'est pas pro aujourd'hui). Repli assumé si rien de compatible : point relais choisi par email après commande.
- **Mollie en carte bancaire seule** : clés de test puis de production, les 38 méthodes exposées restreintes à la CB. `checkmo` à désactiver.
- **Click & collect** : méthode à 0 €, **payée sur place** (TPE ou espèces) → un mode de paiement hors ligne restreint à ce mode de livraison. Créneaux jeudi 9 h-18 h et vendredi 9 h-11 h 30. C'est lui qui fait vivre le statut « Prête pour retrait » du lot 7. **Annulation au rendez-vous non honoré** : pas de cron, Céline annule la commande dans l'administration et le stock se relibère — procédure à écrire dans le mémo Céline.
- **Franchise en base de TVA** : `tax/defaults/country` → FR, `shipping/origin` → Saint-Épain 37800, prix TTC = prix encaissés, mention « TVA non applicable, art. 293 B du CGI ». **Aucune TVA facturée, y compris sur les ventes européennes** — à revoir au-delà de 10 000 € de ventes à distance intra-UE sur l'année.
- **Zone de vente** : **France, Belgique, Luxembourg** — pays francophones de l'UE desservis par Mondial Relay. Monaco suit la France. La Suisse est écartée : hors UE, hors desserte Mondial Relay.
- **Franco à 60 €** sur le point relais : seuil à porter de 49 € à 60 € dans *Madame Aiguille › Panier* **et** dans `carriers/freeshipping/free_shipping_subtotal`.
- **Statuts du lot 7 câblés sur le réel** : paiement capté → « Paiement reçu », expédition → « Expédiée ».
- **Contrôle « poids renseigné »** : commande CLI ou requête SQL documentée, à passer avant chaque mise en production.
- **Aligner l'affichage sur le réel** : `product_reassurance`, `cart_reassurance`, footer, page *Livraison et retours*, mention sous le prix.

**8a — Mise en ligne anticipée (nouvelle dépendance)**

Mollie ne valide le compte qu'avec un **site en ligne** et **un paiement réel**. Domaine, DNS, préproduction HTTPS et SMTP passent donc avant la fin du lot 5. Si la préproduction est protégée par une authentification HTTP, **exclure la route du webhook Mollie**, sinon les paiements ne remontent jamais.

**Ce qui est sorti du périmètre**

Virement SEPA et tout ce qui en découlait (cron d'expiration, relance à J-2, RIB dans l'email de virement du lot 7), Colissimo, Chronopost, PayPal, paiement fractionné — et le besoin de carriers maison, puisqu'il ne reste qu'une grille.

**Ce qui s'y ajoute et reste à chiffrer**

- **Vente en Belgique et au Luxembourg** : grilles Mondial Relay par pays, mentions des CGV. **Site monolingue français** — confirmé.
- **TikTok** au pied de page, **Pinterest** à retirer.
- **Sélecteur de rendez-vous de retrait** : créneaux issus des disponibilités de Céline (jeudi 9 h-18 h, vendredi 9 h-11 h 30), **confirmation automatique** si le créneau est libre, donc réservation réelle du créneau et gestion des collisions. Lieu de retrait unique, adresse **paramétrable en back-office**, aperçu cartographique. Le créneau choisi doit se retrouver sur la commande, dans l'administration, dans le compte client et dans l'email « Prête pour retrait ». **Composant du tunnel : à écrire en Knockout**, pas en Alpine.
- **Emballage cadeau payant à 2 €**, prix réglable en back-office. **Pas natif en Magento Open Source** — le *Gift Wrapping* est réservé à Adobe Commerce. À construire : option au tunnel ou au panier, ligne de total dédiée, report sur la commande, la facture et les emails. Le message cadeau natif, lui, existe déjà et s'affiche dans le détail de commande depuis le lot 7.
- ~~**Contenus juridiques génériques**~~ : **faits le 09/10/2026** — les quatre pages portent un brouillon aligné sur les décisions du call, marqué comme tel, avec les valeurs inconnues entre crochets. Restent la relecture et le remplissage.

**Décisions de Pierre au démarrage (09/10/2026)** : widget Mondial Relay maison ; rendez-vous et emballage engagés après chiffrage ; franco porté par la seule valeur du panier ; lieu de retrait générique ; 8a différé ; méthodes Mollie de l'admin conservées telles quelles.

**Restent à fournir** : l'adresse réelle du lieu de retrait, le code enseigne et les tarifs Mondial Relay, la rédaction définitive des pages juridiques, l'accès au domaine et au serveur pour 8a.

**Effort : XL.** Ramené à L par le transporteur et le moyen de paiement uniques, puis **remonté à XL** par le sélecteur de rendez-vous et l'emballage cadeau, qui sont deux développements dans le tunnel Knockout. Plus **8a**.

---

## Lot 6 — Checkout (Luma fallback)

**Décision prise (05/09/2026)** : Hyvä Checkout payant écarté ; **Luma Fallback Checkout** officiel retenu. **Installé le 10/09/2026** : `hyva-themes/magento2-luma-checkout` 1.1.7 et `hyva-themes/magento2-theme-fallback` 1.0.4, OSL-3.0. Le message « No Checkout module installed » est remplacé par le checkout natif Magento (Knockout / RequireJS). **Correction après lecture des README et du code installé : toute la page bascule vers Luma**, avec le layout checkout simplifié de Luma ; le header/footer Hyvä ne sont pas conservés automatiquement. L’habillage livré au 6b utilise le thème enfant Luma distinct `MadameAiguille/checkout`.

**6a — Installation (½ journée, à faire juste après le lot 2)**

Installation et vérifications techniques terminées, **validées par Pierre après une commande**. `setup:upgrade --keep-generated` et `cache:flush` réussis ; invité activé, formulaire testé à 1440 et 390 px après ajout d'un produit depuis sa fiche. RequireJS/Knockout absents des pages accueil, catégorie et panier contrôlées, présents uniquement sur le checkout parmi ces pages. Dix tests du module existant passent. Détails, limites de recette et chemins de surcharge : `documentation-theme.md` §14.

- `composer require hyva-themes/magento2-luma-checkout`, `setup:upgrade`, vérification de `/checkout` avec un produit de test, guest checkout activé.
- Vérifier que les scripts RequireJS ne se chargent **que** sur les pages du tunnel (performance).
- Documenter dans `documentation-theme.md` où vivent les surcharges Luma (`Magento_Checkout/web/template/*.html`, `web/css/source/*.less` dans un thème Luma enfant ou via le mécanisme du fallback — à lire dans le README du module).

**6b — Habillage — livré le 09/10/2026** (prompt de départ : `docs/prompts/prompt-lot6b.md`)

- Thème `MadameAiguille/checkout`, parent Luma, tokens LESS synchronisés à la charte, Sentient originale, logo et pied minimal.
- Deux étapes natives, champs et actions de 48 px, erreurs françaises, récapitulatif avec articles puis totaux, tiroir mobile natif.
- Relais, rendez-vous, cadeau et sa ligne de total habillés ; sélection clavier des résultats de la carte, avertissement `BDTEST` conservé.
- Téléphone requis, accord CGV natif manuel obligatoire, lien CMS, newsletter native facultative non pré-cochée. Méthodes Mollie conservées, **encore à valider avec Céline**.
- Succès retrait / relais et échec dans le thème Hyvä ; ViewModels, tests et styleguide. Deux commandes de retrait créées puis annulées via l’administration.
- Création de compte après commande conservée native, préremplie ; mécanisme reCAPTCHA disponible, **activation et clés du domaine final au 8a**.

**Dépendances** : lots 5 et 4 livrés. **Recette** : `docs/recettes/lot-6b.md`, documentation §27. Paiement/retour Mollie complet, réception newsletter, reCAPTCHA actif et facture / avoir avec emballage restent au 8a. Fusion de panier à la connexion et session expirée restent à la recette transversale du lot 8.

**Incertitudes levées** : degré de fidélité (inclut le récapitulatif), téléphone obligatoire, CGV obligatoires, newsletter facultative décochée. Aucun champ message supplémentaire ajouté sans décision. Les validations et calculs du lot 5 sont préservés.

**Effort : L** (6a S, 6b M-L).

---

## Lot 7 — Compte client, emails, statuts de commande — **livré le 10/09/2026**

> **Livré** sur `codex/lot-7-compte-emails`, voir « État après le lot 7 ». Les deux décisions ouvertes ont été tranchées par Pierre : **six statuts, « prête pour retrait » comprise** — l'ajouter plus tard aurait obligé à reprendre la frise et l'email — et **gabarits natifs de Magento habillés par une enveloppe commune**, plus sûrs pour les montées de version et modifiables par Céline depuis l'administration.

**Périmètre** (réalisé intégralement, voir « État après le lot 7 »)

- Pages compte (`customer_account_*`, `sales_order_*`) : navigation latérale restylée, tableau des commandes avec statuts en badges, adresses, informations personnelles, mot de passe oublié. Formulaires au gabarit du Design System.
- Statuts de commande : *en attente de paiement*, *paiement reçu*, *en préparation*, *expédiée*, *prête pour retrait*, *livrée* — statuts et états Magento (data patch), libellés français.
- Emails transactionnels : en-tête / pied aux couleurs de la marque (module `hyva-themes/magento2-email-module` présent), variantes virement (RIB + référence), paiement reçu, expédition, prêt pour retrait, accusé de réception du formulaire de contact, bienvenue, réinitialisation.
- Textes français : dictionnaire `i18n/fr_FR.csv` du thème pour les chaînes Hyvä encore en anglais (« Sign In », « Create an Account », « We can't find products… », etc.).

**Dépendances révisées (10/09/2026)** : lot 3 (accusé de réception du formulaire de contact) et lot 4 (panier) suffisent pour développer. Le lot 6 n'est plus un prérequis : les statuts se créent par data patch, les pages du compte et les gabarits d'email s'habillent sans qu'aucun paiement soit actif.

**Ce qui ne pourra pas être bouclé avant les lots 5 et 6b** — à isoler dès le départ pour ne pas bloquer le reste :

- la recette de bout en bout d'une commande réellement payée qui parcourt tous les statuts ;
- le contenu exact de l'email de virement (RIB, référence, délai d'annulation) : la structure se construit, les valeurs viennent du lot 5 ;
- l'email « prête pour retrait », qui dépend des modalités de la remise en main propre, à cadrer avec Céline ;
- la délivrabilité (SPF / DKIM / DMARC), qui dépend du domaine — lot 8.

**Incertitudes — levées le 10/09/2026**

- ~~Emails HTML : le module email Hyvä impose-t-il ses gabarits ?~~ → il laisse surcharger les gabarits natifs. L'habillage passe par `Magento_Email/email/header.html` et `footer.html` du thème enfant, avec des styles LESS ; tous les emails en héritent, y compris ceux qu'on n'a pas touchés.
- ~~Jeu de statuts définitif~~ → **six statuts livrés**. « Prête pour retrait » n'apparaît dans la frise de la cliente que si la commande passe réellement par ce statut ; tant que la remise en main propre n'est pas confirmée, il reste simplement inutilisé.

**Effort : M — réalisé.**

---

## Lot 8 — Back-office, exploitation, mise en production

**Périmètre**

- Rôle ACL « Céline » : catalogue, ventes, clients (lecture), CMS (pages, blocs) — sans configuration, modules, thèmes, utilisateurs.
- Alerte email de stock bas (native), seuil par produit ou global.
- Guide utilisateur illustré (`docs/guide-back-office.md`) : ajouter un produit (poids !), gérer un stock, traiter une commande, encaisser un virement, modifier le bandeau / les blocs d'accueil / les visuels Instagram, changer les liens réseaux sociaux.
- CI/CD GitHub Actions : `composer install`, `npm ci && npm run build` dans `web/tailwind`, `setup:upgrade`, `setup:static-content:deploy fr_FR`, `cache:flush` ; environnement de staging (`staging.madame-aiguille.fr`, auth HTTP).
- Production : mode `production`, Varnish/Valkey, HTTPS, sitemap, robots, redirection `madameaiguille.fr` → `madame-aiguille.fr`, sauvegardes BDD + médias.
- Recette complète (plan de tests §3 à §10), audit Lighthouse (accueil, catégorie, fiche), contrôle poids sur tout le catalogue, paiement réel de test.
- Logo SVG (brief `prompts/prompt-logos-svg.md`) intégré à la place du PNG provisoire, favicon.

**Dépendances** : tous les lots ; nom de domaine réservé ; contenus complets.

**Incertitudes**

- Docker ou installation native pour le staging ? Stratégie de déploiement (zero-downtime ou fenêtre de maintenance) ?
- Rythme de mise à jour mensuel de Céline face aux séries qui s'épuisent : l'alerte de stock bas est la réponse minimale ; à rediscuter après un mois d'exploitation.

**Effort : M** (hors rédaction du guide, qui dépend de la maturité de l'admin).

---

## Décisions à prendre avec Céline avant publication et pendant le lot 5

1. ~~Produits épuisés : visibles ou masqués (lot 2)~~ — **visibles** (Pierre, 05/09/2026), à confirmer par Céline.
1 bis. ~~Page « Boutique » globale~~ — accueil retenu comme vitrine ; les CTA contextuels mènent aux catégories.
2. ~~« Actualités »~~ — nouveautés produit et blocs CMS pour marchés, congés et annonces.
3. ~~Newsletter et page « Nos tissus »~~ — toutes deux livrées au lot 3.
4. ~~Franco de port~~ — **60 €** en point relais (Pierre, 09/10/2026).
5. Paliers de poids : posés (0,25 · 0,5 · 1 · 2 · 5 kg) ; **tarifs et poids d'emballage attendus de Céline**.
6. Remise en main propre : créneaux et restriction (France) posés ; **adresse réelle attendue**.
7. ~~Virement~~ — sans objet (carte bancaire en ligne, paiement sur place au retrait).
8. ~~Statut fiscal / TVA~~ — franchise en base, mention en place.
9. Comptes réseaux sociaux réels : Instagram et Facebook saisis, **URL TikTok à fournir**.
10. Nom de domaine et serveur (lot **8a**).
11. Méthodes Mollie définitives, texte CGV et confidentialité (newsletter / transporteurs), adresse exacte du retrait : **à valider avant ouverture**.

## Décisions techniques à prendre (Pierre)

1. ~~Hyvä Checkout ou checkout Luma~~ — **tranché : Luma Fallback Checkout** (`hyva-themes/magento2-luma-checkout`), lot 6a.
2. ~~Stripe ou Mollie~~ — **tranché : Mollie** (10/09/2026). Déjà installé et activé ; reste la création du compte marchand et la saisie des clés.
3. ~~Module Mondial Relay~~ — **widget officiel intégré en maison** (spike du 09/10/2026).
4. ~~Formulaire de contact~~ — module dédié `MadameAiguille_Contact`; logique dans le module, surcharge visuelle dans le thème enfant.
5. ~~Catégories de test « Sneakers » et « T-Shirts » à supprimer~~ — fait au lot 2.
6. **WebP** : reporté au lot 8 ou module tiers gratuit (compatibilité Hyvä à vérifier avant installation).
7. Fraîcheur des caches : valider en conditions réelles au lot 4 ; s'assurer que le cron Magento tourne (lot 8).
