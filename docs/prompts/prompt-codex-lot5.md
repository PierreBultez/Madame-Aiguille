# Reprise du développement — lot 5 Livraison et paiements

Tu reprends le développement de **Madame Aiguille**, une boutique Magento Open Source 2.4.9 avec Hyvä 1.5.2. Réponds en français et travaille avec Pierre, développeur Laravel/Vue/Tailwind et ancien responsable technique Magento 2.

## État de départ à vérifier

- Dépôt : `/home/pierre/Documents/aiguille`
- Application Magento : `/home/pierre/Documents/aiguille/shop`
- Branche livrée : `lot-3-accueil-cms-contact`
- Dernier commit d'implémentation connu : `9c48987` (`Intègre l'identité visuelle officielle`), suivi d'un commit de clôture documentaire à repérer avec `git log`
- Lots validés : 1 Fondations, 2 Catalogue, 6a installation du checkout Luma fallback, 3 Accueil/CMS/Contact
- Checkout : `hyva-themes/magento2-luma-checkout` 1.1.7 et `hyva-themes/magento2-theme-fallback` 1.0.4. Le checkout bascule toute la page vers Luma et utilise Knockout/RequireJS.
- Prochain lot prévu par le plan : **lot 5**, avec un spike Mondial Relay en première étape, puis livraison et paiements. Le lot 4 vient ensuite afin de brancher le panier sur les vrais tarifs.

Commence par `git status`, `git log --oneline --decorate -n 20` et la lecture de :

1. `docs/documentation-theme.md`, surtout §14, §16, §20 ;
2. `docs/plan-de-developpement.md`, surtout Lot 5 et les décisions ouvertes ;
3. `docs/cahier-des-charges-docs-developpement/specification-fonctionnelle.md`, sections livraison, paiement et checkout ;
4. `docs/cahier-des-charges-docs-developpement/architecture-technique.md` ;
5. `docs/cahier-des-charges-docs-developpement/plan-de-tests.md`, partie livraison/paiement ;
6. la maquette `docs/maquettes-direction-artistique/Checkout.dc.html` pour les états attendus, sans reconstruire le checkout natif.

Si le lot 3 a été fusionné, pars de la branche cible à jour. Sinon, pars directement de `lot-3-accueil-cms-contact`, qui contient le lot 6a et le lot 3 validés. Crée une nouvelle branche préfixée `codex/`, proposée : `codex/lot-5-livraison-paiements`.

## Règles non négociables

- **Toutes les surcharges visuelles vivent dans un thème enfant.** Pour le storefront Hyvä : `shop/app/design/frontend/MadameAiguille/default/`. Pour les pages qui basculent sous Luma, préparer ou utiliser un thème enfant Luma dédié ; ne jamais placer une personnalisation visuelle dans `vendor/` ou dans le module métier.
- Les modules `app/code` portent la logique métier, les services, la configuration, les commandes CLI et les valeurs par défaut. Les layouts, `.phtml`, LESS/Tailwind, assets et templates Knockout personnalisés restent dans le thème enfant approprié.
- Ne jamais modifier `vendor/`, `pub/static/` ni un CSS généré. Ne pas ajouter de `tailwind.config.js`.
- Ne pas installer ni retirer une extension tierce sans présenter à Pierre la compatibilité, la maintenance, la licence, le coût, les dépendances et le plan de repli, puis obtenir son accord explicite.
- Toute information actuelle sur les modules, leurs versions, tarifs, API ou compatibilités doit être vérifiée sur les sources officielles au moment du travail.
- Annoncer explicitement chaque commande `composer` et `bin/magento` avant de l'exécuter.
- Ne jamais afficher ni recopier les secrets de `app/etc/env.php`, `auth.json` ou de la configuration Composer.
- Commits atomiques en français, avec les trailers utilisés dans le dépôt. Contrôler chaque écran à 1440 et 390 px. Documenter chaque réglage destiné à Céline dans `docs/documentation-theme.md`.
- Réutiliser les mécanismes natifs Magento quand ils répondent au besoin. Le checkout Luma est conservé ; le lot 5 configure et intègre les modes, il ne redessine pas encore tout le tunnel (habillage au lot 6b).

## Objectif du lot 5

Livrer des modes d'expédition et de paiement cohérents, rejouables en staging/production et testés dans le checkout Luma :

- frais au poids pour Colissimo, Mondial Relay et Chronopost, avec grilles CSV versionnées et poids d'emballage documenté ;
- contrôle des produits publiés sans poids, idéalement via une commande CLI dédiée ;
- remise en main propre gratuite, restreinte selon la décision métier ;
- sélection d'un point relais Mondial Relay dans le checkout ;
- paiement carte via **un seul** prestataire retenu, Stripe ou Mollie ;
- virement bancaire natif et paiement lors de la remise en main propre ;
- TVA, mentions légales et délai d'annulation des virements conformes aux décisions réelles ;
- tests avec plusieurs paniers/poids et vérification de la relibération du stock.

## Étape 1 obligatoire : spike Mondial Relay

Ne commence pas l'intégration complète. Fais d'abord une étude courte, actuelle et sourcée des modules Mondial Relay disponibles pour Magento 2.4.9/PHP 8.5 et le checkout natif Magento sous Luma fallback. Pour chaque candidat crédible, relève : éditeur et URL officielle, version/date de maintenance, licence/prix, compatibilité Magento/PHP, comportement dans Knockout checkout, gestion des points relais, API/identifiants requis, qualité de maintenance et procédure de test sandbox.

Inspecte aussi le code et les dépendances déjà présents dans `shop/composer.json`, `shop/composer.lock` et `shop/vendor` sans les modifier. Termine cette étape par :

- un tableau comparatif ;
- une recommandation argumentée ;
- un plan de test local et sandbox ;
- un repli concret si aucune extension n'est assez fiable ;
- la liste exacte des commandes envisagées, sans les lancer.

Présente ce résultat à Pierre et attends son choix avant toute installation.

## Décisions à obtenir avant l'implémentation

Regroupe les questions pour éviter les allers-retours :

1. Stripe ou Mollie pour la carte, en tenant compte des faibles paniers, remboursements, frais et du virement SEPA ; Mollie est déjà présent dans `vendor`, mais cela ne vaut pas validation métier.
2. Montant du franco (49 € seulement dans les maquettes), grilles/paliers des trois transporteurs et poids d'emballage.
3. Zone et modalités de remise en main propre : lieu, codes postaux, créneaux, paiement accepté.
4. Délai d'expiration d'un virement et relance éventuelle à J-2.
5. Statut fiscal/TVA et mentions à afficher.
6. Comptes, contrats et accès sandbox déjà disponibles pour Mondial Relay et le prestataire de paiement.

Ne simule aucune décision commerciale. Quand une valeur manque, prépare les fichiers et l'architecture avec une configuration explicite, mais garde l'activation production bloquée jusqu'à la valeur confirmée.

## Validation et documentation attendues

- Vérifier les méthodes dans le checkout invité et connecté, à 1440 et 390 px.
- Tester au minimum trois paniers de poids différents, un configurable, le franco, les adresses hors zone, le point relais absent/changé, l'échec et le retour de paiement, le virement et le retrait.
- Confirmer que les pages Hyvä hors checkout ne chargent toujours pas RequireJS/Knockout.
- Mettre à jour `docs/documentation-theme.md`, `docs/plan-de-developpement.md`, les grilles versionnées et le plan de tests avec les chemins admin précis pour Céline.
- Aligner seulement après activation réelle les mentions du footer, `product_reassurance`, la page Livraison et la mention sous le prix.

Commence par l'audit de l'état Git et des documents, puis propose un plan court du spike. N'installe rien avant la validation explicite de Pierre sur le candidat Mondial Relay.
