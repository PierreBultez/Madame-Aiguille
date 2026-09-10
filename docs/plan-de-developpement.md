# Plan de développement — Madame Aiguille

Document interne — v1.5 (10/09/2026), lot 6a validé ; lot 3, accueil livré pour recette

> **v1.5** — Étape 2 validée par Pierre. Étape 3 : accueil complet en blocs CMS, Nouveautés automatiques sans produits épuisés, Incontournables par attribut produit, actualités éditoriales, galerie et newsletter native avec double opt-in. Purge de cache étendue aux sélections de l'accueil ; recette à 390 / 1440 px effectuée. Validation Pierre en attente avant les pages CMS.

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

Reste ouvert, transverse à tous les lots : les **SVG du logo** (brief `prompts/prompt-logos-svg.md`) — le header utilise un PNG 2× provisoire ; les **photos produit** au ratio 4:5 ; les **textes** (pages légales, À propos, descriptions).

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
- **Champ « Me prévenir »** (catégorie vide, fiche épuisée) : posé seulement si la newsletter est retenue (lot 3).
- **Catégories réelles** : celles en base sont celles des maquettes (`docs/jeux-de-donnees/categories.php`) ; à définir avec Céline.
- **Contact** : la page du lot 3 doit lire `?product=<sku>` (lien « Une question sur le tissu ou le motif ? » et encarts de mise en relation).
- **Suivi de la fraîcheur des caches** en conditions réelles : à recetter au lot 4 (première commande de test) ; le cron Magento doit tourner (lot 8).

### Décisions de Pierre pour le lot 3 — 10/09/2026

Ces décisions remplacent les questions ouvertes correspondantes ci-dessus et dans le périmètre historique du lot 3 :

- **Actualités** : bloc CMS pour marchés, congés et annonces, en complément des Nouveautés produit automatiques.
- **Newsletter** : activée au lancement, module natif avec double opt-in. Le comportement « Me prévenir » d'une série précise reste à cadrer : une inscription à la newsletter n'est pas une alerte de réassort ciblée.
- **Nos tissus** : page CMS incluse dans le lot 3.
- **Navigation** : CTA contextuels vers la catégorie concernée ; « Voir toute la boutique » vers l'accueil. Pas de nouvelle catégorie globale Boutique. Le CTA du hero devra exposer une destination catégorie éditable ; la destination de « Voir toutes les nouveautés » reste à préciser.
- **Contact** : module dédié `MadameAiguille_Contact`, nom, email, produit prérempli via `?product=<sku>`, objet, message limité à 1 000 caractères, case de consentement. Photo optionnelle disponible sur desktop et mobile : JPG/PNG, 5 Mo, validation MIME serveur et stockage **hors `pub/`**. Durée de conservation et purge à définir avant l'étape contact.
- **Contenus** : textes génériques éditables dans le back-office et emplacements documentés ; aucune biographie, date de marché ou promesse de délai fictive présentée comme validée. Les pages légales restent « À rédiger » sans texte fourni.
- **404** : même direction artistique que les états vides avec un petit easter egg ludique. Proposition : bobine déroulée, « On a perdu le fil… », aiguille animée au clic, accessible au clavier et respectant la préférence de réduction des animations.

Ordre des étapes à valider séparément : **1)** lot 6a (validé) ; **2)** composant partagé de titre de section (validé) ; **3)** accueil (livré pour recette) ; **4)** pages CMS dont Nos tissus ; **5)** contact ; **6)** 404, panier vide et recette transversale. Chaque livraison inclut sa documentation, un commit atomique et un feu vert de Pierre avant l'étape suivante. La branche `lot-3-accueil-cms-contact` a été créée depuis le lot 6a validé (`7f46bb1`).

## Vue d'ensemble

| Lot | Titre | Dépend de | Effort | Risque |
|---|---|---|---|---|
| 2 | Catalogue : modèle de données, catégorie, fiche produit | 1 | **L** | faible |
| 3 | Accueil, pages CMS, formulaire de contact | 2 | **L** | moyen (contenu) |
| 4 | Panier et mini-panier | 2, config livraison du lot 5 | **M** | faible |
| 5 | Livraison et paiements (table rates, Mondial Relay, Stripe ou Mollie, virement, main propre) | 2, 6a | **XL** | **élevé** |
| 6 | Checkout Luma fallback : installation (6a) puis habillage (6b) | 5 | **L** | moyen |
| 7 | Compte client, emails transactionnels, statuts de commande | 6 | **M** | faible |
| 8 | Back-office, exploitation, mise en production | tous | **M** | moyen |

Ordre recommandé : **2 → 6a (installer le fallback, ½ journée) → 3 → 5 (spike Mondial Relay en premier) → 4 → 6b → 7 → 8**. Installer le checkout tôt permet de tester les modules de livraison/paiement du lot 5 dans le vrai tunnel. Le lot 3 peut démarrer en parallèle du spike du lot 5 : c'est celui qui dépend le plus du contenu de Céline, autant le lancer tôt.

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

## Lot 3 — Accueil, pages CMS, formulaire de contact

**Périmètre**

- Page d'accueil (`cms_index_index.xml` + blocs CMS + widgets) dans l'ordre validé : hero (visuel plafonné à 240 px sur mobile pour que « Nouveautés » soit sous le pli), **Nouveautés** (widget *New Products* natif basé sur `news_from_date`, produits épuisés exclus — plugin ou collection dédiée ; réutiliser la carte `product_list_item` et le ViewModel `LimitedSeries` du lot 2, dont le cron purge déjà les caches aux dates de nouveauté), Incontournables (widget produits sur sélection manuelle ou catégorie « Incontournables » non visible dans le menu), Histoire (bloc CMS + image atelier), Pourquoi choisir (4 arguments, bloc CMS), Instagram (galerie statique de 4 visuels, bloc CMS), Newsletter (`Magento_Newsletter`, sous réserve). Chaque bloc restylable par Céline sans code, dans la limite des classes déclarées en `@source inline`.
- Gabarit de mise en avant de section (titre display + filets ondulés + accroche + lien) réutilisé partout.
- Pages statiques : À propos, Livraison & retours, CGV, Mentions légales, Confidentialité — gabarit `1column` typographié (classe `prose` de hyva-modules, adaptée aux tokens), largeur de lecture ~65 caractères.
- **Formulaire de contact** — module custom léger dans `MadameAiguille_Theme` (ou module dédié `MadameAiguille_Contact`) : surcharge du contrôleur `contact/index/post` ou nouveau contrôleur ; champs nom, email, message, **pièce jointe** (JPG/PNG, 5 Mo, validation serveur du type MIME, stockage hors `pub/` ou envoi en pièce jointe d'email puis suppression), **produit concerné** pré-rempli depuis la fiche (`?product=`), reCAPTCHA natif, email à Céline + accusé de réception au visiteur. ViewModel `ContactForm`.
- Page 404 et page « panier vide » aux gabarits du Design System (états vides).

**Dépendances** : lot 2 pour les widgets produit (Nouveautés, Incontournables) et le lien depuis la fiche. Contenus (textes, photos) fournis par Céline — **principal facteur de délai**.

**Incertitudes**

- « Actualités » = uniquement des produits, ou aussi de l'information (marché, congés) ? → un bloc CMS éditorial en plus du widget suffirait.
- Newsletter au lancement ou non (RGPD, double opt-in, charge éditoriale).
- Page « Nos tissus » (spécification §2.2, recommandée) : dans ce lot ou plus tard ?
- Pièce jointe : envoyée par email (simple, pas de stockage) ou conservée sur le serveur (traçabilité, mais surface d'attaque et RGPD) ?

**Effort : L.** L'accueil est du gabarit ; le formulaire de contact est le seul développement PHP significatif du projet côté front.

---

## Lot 4 — Panier et mini-panier

**Périmètre**

- Tiroir mini-panier (Design System §6.9) : 380 px desktop / plein écran mobile, barre de progression vers le franco (si retenu), liste défilante au-delà de 4 articles, pied fixe. Comportement natif Hyvä conservé (Alpine, `private-content`).
- Page panier : lignes (image 4:5, quantité plafonnée au stock, suppression), récapitulatif, **estimation des frais de port** dès le panier (bloc natif « Estimate Shipping », code postal → tarifs par transporteur), message « plus que X € pour la livraison offerte ».
- Messages d'ajout au panier (alerte succès avec le reste pour le franco).
- Cas limites : produit épuisé entre l'ajout et le passage en caisse, dernière pièce prise par une autre cliente (messages natifs restylés et traduits).

**Dépendances** : lot 2 (produits, stock) ; lot 5 pour que l'estimation affiche de vrais montants (les gabarits peuvent être faits avant avec un tarif provisoire).

**Incertitudes**

- Franco de port : montant (49 € dans les maquettes) à confirmer par Céline — conditionne la barre de progression et le bandeau.
- Code promo : champ masqué en v1 ou visible mais inactif ?

**Effort : M.**

---

## Lot 5 — Livraison et paiements

C'est le lot le plus risqué du projet : il combine configuration métier à cadrer avec Céline, la **seule dépendance tierce structurante** (Mondial Relay) et les prestataires de paiement.

**Périmètre**

- **Frais de port au poids** : trois grilles *table rates* (`Weight vs. Destination`, France métropolitaine), une par transporteur — Colissimo, Mondial Relay, Chronopost — importées par CSV et **versionnées dans `docs/` ou dans le module** (script d'import pour rejouer en staging/prod). Paliers calés sur les grilles publiques + poids d'emballage.
- Contrôle « poids renseigné » : commande CLI `madameaiguille:catalog:check-weight` (ou requête SQL documentée) listant les produits publiés sans poids — à passer avant chaque mise en prod (plan de tests §2).
- **Remise en main propre** : méthode d'expédition à 0 € (natif *Free Shipping* ou table rate dédiée), restreinte par code postal si Céline le souhaite ; libellés et texte d'aide.
- **Mondial Relay — sélecteur de point relais** : *spike* de 1 à 2 jours **en tout début de lot** : identifier les modules candidats, vérifier la compatibilité Hyvä **et** la compatibilité avec le checkout retenu (lot 6), tester en sandbox. Sortie du spike : module retenu ou décision de repli (sélection du point relais par email après commande, ou saisie libre du point relais dans un champ d'adresse).
- **Paiements** : Stripe (module officiel, à vérifier : compatibilité Hyvä + checkout retenu), virement bancaire natif (`Magento_OfflinePayments` — RIB et référence affichés après validation et dans l'email, jamais sur une page indexable), remise en main propre = paiement hors ligne (*Cash on delivery* renommé, restreint au mode de retrait).
- **TVA** : paramétrage selon le statut fiscal (franchise en base probable → prix TTC = HT, mention « TVA non applicable, art. 293 B du CGI » sur factures et footer).
- **Expiration des commandes en attente de virement** : cron d'annulation + relibération du stock après N jours (à trancher), email de relance à J-2.

**Dépendances** : lot 2 (poids sur les produits) ; lot 6a (checkout Luma fallback installé) pour tester les modules dans le vrai tunnel. Critère de compatibilité des modules Stripe/Mollie et Mondial Relay : le **checkout natif Magento (Knockout)** — le cas standard de l'écosystème, donc large choix. La compatibilité Hyvä ne compte que pour ce qui s'affiche hors tunnel (panier, mini-panier, fiche produit, compte).

**Incertitudes (fortes)**

- Aucun module Mondial Relay compatible Hyvä n'est encore identifié. C'est le point à lever en premier.
- **Constat du lot 1** : Mollie (`mollie/magento2` + `mollie/magento2-hyva-compatibility`) est déjà installé dans `vendor/`. Mollie couvre carte bancaire **et** virement SEPA. Stripe ou Mollie : les deux fonctionnent avec le checkout Luma ; **Pierre se renseigne et tranche** avant la création du compte marchand — l'un des deux est à retirer du projet. Points de comparaison : frais par transaction sur des paniers de 10-20 €, gestion du virement (SEPA par le prestataire vs virement natif rapproché à la main).
- Paliers de poids, franco, périmètre de la remise en main propre, délai d'expiration du virement : quatre décisions de Céline.
- Statut fiscal de Céline : à connaître avant la première vente.

**Effort : XL.** Configuration lourde, deux intégrations tierces, beaucoup de recette (trois paniers de poids différents par transporteur, plan de tests §6).

---

## Lot 6 — Checkout (Luma fallback)

**Décision prise (05/09/2026)** : Hyvä Checkout payant écarté ; **Luma Fallback Checkout** officiel retenu. **Installé le 10/09/2026** : `hyva-themes/magento2-luma-checkout` 1.1.7 et `hyva-themes/magento2-theme-fallback` 1.0.4, OSL-3.0. Le message « No Checkout module installed » est remplacé par le checkout natif Magento (Knockout / RequireJS). **Correction après lecture des README et du code installé : toute la page bascule vers Luma**, avec le layout checkout simplifié de Luma ; le header/footer Hyvä ne sont pas conservés automatiquement. L'habillage du lot 6b utilisera un thème enfant Luma distinct.

**6a — Installation (½ journée, à faire juste après le lot 2)**

Installation et vérifications techniques terminées, **validées par Pierre après une commande**. `setup:upgrade --keep-generated` et `cache:flush` réussis ; invité activé, formulaire testé à 1440 et 390 px après ajout d'un produit depuis sa fiche. RequireJS/Knockout absents des pages accueil, catégorie et panier contrôlées, présents uniquement sur le checkout parmi ces pages. Dix tests du module existant passent. Détails, limites de recette et chemins de surcharge : `documentation-theme.md` §14.

- `composer require hyva-themes/magento2-luma-checkout`, `setup:upgrade`, vérification de `/checkout` avec un produit de test, guest checkout activé.
- Vérifier que les scripts RequireJS ne se chargent **que** sur les pages du tunnel (performance).
- Documenter dans `documentation-theme.md` où vivent les surcharges Luma (`Magento_Checkout/web/template/*.html`, `web/css/source/*.less` dans un thème Luma enfant ou via le mécanisme du fallback — à lire dans le README du module).

**6b — Habillage (après le lot 5)**

- Tunnel en trois temps (spécification §4) : livraison (adresse, mode, point relais), paiement, récapitulatif + CGV.
- Branding **aux couleurs** plutôt qu'au pixel du Design System : palette, fontes (les mêmes `.woff2`), boutons 48 px, champs, messages d'erreur — par LESS/CSS et quelques templates Knockout ciblés. On retemplate le natif, on ne réécrit pas.
- Pages de confirmation (Hyvä, déjà retemplatables en `.phtml`) : succès (RIB pour le virement, instructions de retrait), échec de paiement (alerte erreur, panier conservé).
- reCAPTCHA sur la création de compte au checkout.

**Dépendances** : lot 5 (modes de livraison et paiements), lot 4 (panier).

**Incertitudes**

- Degré de fidélité au Design System accepté pour le tunnel Luma (recommandation : couleurs, fontes, tailles de cibles ; pas de refonte de la structure).
- Le module Mondial Relay retenu au lot 5 doit injecter son sélecteur dans le checkout Knockout : à valider pendant le spike.
- Fusion de panier à la connexion, session expirée en tunnel : comportements natifs à recetter, pas à développer.

**Effort : L** (6a S, 6b M-L).

---

## Lot 7 — Compte client, emails, statuts de commande

**Périmètre**

- Pages compte (`customer_account_*`, `sales_order_*`) : navigation latérale restylée, tableau des commandes avec statuts en badges, adresses, informations personnelles, mot de passe oublié. Formulaires au gabarit du Design System.
- Statuts de commande : *en attente de paiement*, *paiement reçu*, *en préparation*, *expédiée*, *prête pour retrait*, *livrée* — statuts et états Magento (data patch), libellés français.
- Emails transactionnels : en-tête / pied aux couleurs de la marque (module `hyva-themes/magento2-email-module` présent), variantes virement (RIB + référence), paiement reçu, expédition, prêt pour retrait, accusé de réception du formulaire de contact, bienvenue, réinitialisation.
- Textes français : dictionnaire `i18n/fr_FR.csv` du thème pour les chaînes Hyvä encore en anglais (« Sign In », « Create an Account », « We can't find products… », etc.).

**Dépendances** : lot 6 (statuts liés aux paiements), lot 3 (accusé de réception contact).

**Incertitudes**

- Délivrabilité : SPF / DKIM / DMARC dépendent du nom de domaine, pas encore réservé.
- Emails HTML : le module email Hyvä impose-t-il ses gabarits ou laisse-t-il surcharger les templates natifs ?

**Effort : M.**

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

## Décisions à prendre avec Céline avant les lots 3 et 5

1. ~~Produits épuisés : visibles ou masqués (lot 2)~~ — **visibles** (Pierre, 05/09/2026), à confirmer par Céline.
1 bis. Page « Boutique » globale (catégorie ancrée regroupant tout) ou accueil comme vitrine ? Conditionne « Voir toute la boutique » et le fil d'Ariane (lot 3).
2. « Actualités » : produits seulement, ou aussi de l'information (lot 3).
3. Newsletter au lancement ? Page « Nos tissus » ? (lot 3)
4. Franco de port : montant (lots 4-5).
5. Paliers de poids et grilles transporteurs ; poids d'emballage (lot 5).
6. Remise en main propre : lieu, créneaux, restriction géographique (lot 5).
7. Virement : délai d'annulation, acceptation du suivi manuel (lot 5).
8. Statut fiscal / TVA (lot 5).
9. Comptes réseaux sociaux réels (configuration déjà en place).
10. Nom de domaine (lot 8).

## Décisions techniques à prendre (Pierre)

1. ~~Hyvä Checkout ou checkout Luma~~ — **tranché : Luma Fallback Checkout** (`hyva-themes/magento2-luma-checkout`), lot 6a.
2. **Stripe ou Mollie** — Pierre se renseigne et tranche avant la création du compte marchand ; les deux sont compatibles avec le checkout Luma.
3. Module Mondial Relay — spike en début de lot 5.
4. Formulaire de contact : module dédié `MadameAiguille_Contact` ou dans `MadameAiguille_Theme` (recommandé : dédié, pour isoler l'upload).
5. ~~Catégories de test « Sneakers » et « T-Shirts » à supprimer~~ — fait au lot 2.
6. **WebP** : reporté au lot 8 ou module tiers gratuit (compatibilité Hyvä à vérifier avant installation).
7. Fraîcheur des caches : valider en conditions réelles au lot 4 ; s'assurer que le cron Magento tourne (lot 8).
