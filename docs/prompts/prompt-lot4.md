# Reprise du développement — lot 4 Panier et mini-panier

Tu reprends le développement de **Madame Aiguille**, une boutique Magento Open Source 2.4.9 avec Hyvä 1.5.2. Réponds en français et travaille avec Pierre, développeur Laravel/Vue/Tailwind et ancien responsable technique Magento 2.

## État de départ à vérifier

- Dépôt : `/home/pierre/Documents/aiguille`
- Application Magento : `/home/pierre/Documents/aiguille/shop`
- Branche livrée : `lot-3-accueil-cms-contact`
- Derniers commits connus : `9c48987` pour les logos officiels, `d599846` pour la clôture documentaire, puis un éventuel correctif du prompt à repérer avec `git log`
- Lots validés : 1 Fondations, 2 Catalogue, 6a installation du checkout Luma fallback, 3 Accueil/CMS/Contact
- L'état **panier vide** a déjà été livré au lot 3. Le lot 4 traite le panier contenant des articles et le mini-panier.
- Le lot 5 Livraison/Paiements n'est pas encore réalisé. L'estimateur doit utiliser les méthodes Magento actuellement configurées et rester compatible avec les vrais tarifs qui seront ajoutés au lot 5. Ne simule pas Colissimo, Mondial Relay ou Chronopost.

Commence par `git status`, `git log --oneline --decorate -n 20` et la lecture de :

1. `docs/documentation-theme.md`, surtout §2, §8 à §16 et §20 ;
2. `docs/plan-de-developpement.md`, surtout Lot 4 ;
3. `docs/cahier-des-charges-docs-developpement/specification-fonctionnelle.md`, sections panier et stock ;
4. `docs/cahier-des-charges-docs-developpement/guide-bonnes-pratiques-hyva.md` ;
5. `docs/cahier-des-charges-docs-developpement/plan-de-tests.md`, partie panier ;
6. `docs/maquettes-direction-artistique/Panier.dc.html` et les composants panier/mini-panier de `Design System.dc.html`.

Si le lot 3 a été fusionné, pars de la branche cible à jour. Sinon, pars directement de `lot-3-accueil-cms-contact`. Crée la nouvelle branche `codex/lot-4-panier` avant toute modification.

## Règles non négociables

- **Toutes les surcharges visuelles vivent dans le thème enfant** `shop/app/design/frontend/MadameAiguille/default/` : layouts, `.phtml`, Tailwind/CSS, assets et JavaScript Alpine. Les modules `app/code` ne portent que la logique métier, les services, ViewModels et configurations.
- Ne jamais modifier `vendor/`, `pub/static/` ni `web/css/styles.css`. Ne pas ajouter de `tailwind.config.js`.
- Préserver les composants natifs Hyvä : Alpine, sections privées, customer-data, formulaires Magento, totaux et estimateur de livraison. Retemplater seulement ce qui est nécessaire.
- Zéro logique métier dans les `.phtml` ; utiliser un ViewModel ou un bloc pour les calculs. Escaping systématique et `__()` pour les textes.
- Toutes les couleurs passent par les tokens existants. Cibles tactiles de 44 px, focus visible et WCAG 2.1 AA.
- Annoncer explicitement chaque commande `composer` ou `bin/magento` avant de l'exécuter.
- Commits atomiques en français avec les trailers utilisés dans le dépôt. Vérification visuelle à 1440 et 390 px à chaque étape.
- Ne pas installer d'extension tierce et ne pas commencer le lot 5.

## Périmètre du lot 4

1. **Mini-panier Hyvä**
   - tiroir de 380 px sur desktop et plein écran sur mobile ;
   - liste défilante au-delà de quatre articles, total et actions toujours accessibles ;
   - modification de quantité plafonnée au stock disponible, suppression et messages natifs ;
   - image produit au ratio 4:5, variantes lisibles et compteur synchronisé ;
   - fermeture à la souris, au clavier et par clic extérieur, avec gestion correcte du focus.

2. **Page panier avec articles**
   - lignes produit, image, nom, variante, prix, quantité, sous-total et suppression ;
   - quantité bornée par le stock comme sur la fiche produit ;
   - récapitulatif et passage au checkout natif ;
   - conserver l'état vide déjà livré et ses recommandations automatiques.

3. **Estimation de livraison**
   - conserver le calcul Magento natif par pays/code postal ;
   - afficher uniquement les méthodes réellement retournées par Magento ;
   - structurer le rendu pour recevoir sans refonte les transporteurs du lot 5 ;
   - ne pas afficher de faux prix ni de faux noms de transporteur.

4. **Franco et code promo**
   - demander à Pierre si le seuil de 49 € de la maquette est confirmé ou si la barre doit rester masquée ;
   - demander si le champ code promo doit être visible en v1 ;
   - si le franco est confirmé, rendre son montant configurable et calculer la progression côté serveur ou depuis les données de panier fiables, jamais depuis un montant du DOM.

5. **Concurrence et erreurs de stock**
   - recetter la dernière pièce prise entre l'ajout et le checkout, la quantité devenue indisponible et le produit désactivé ;
   - conserver les validations natives Magento et harmoniser seulement leur rendu et leurs traductions.

6. **Styleguide et documentation**
   - ajouter les états utiles du mini-panier et du panier rempli au `/styleguide` sans dupliquer la logique métier ;
   - documenter pour Céline les réglages disponibles, et pour Pierre les templates/layouts/ViewModels modifiés ;
   - mettre à jour le journal des livraisons et les limites qui seront résolues au lot 5.

## Méthode attendue

Présente d'abord un plan court et les deux décisions nécessaires : seuil de franco et visibilité du code promo. Travaille ensuite par étapes avec un commit atomique par étape et une liste de recette précise.

Teste au minimum : panier vide, un produit simple, un configurable, quantité maximale, suppression du dernier article, plusieurs lignes, rafraîchissement de page, mini-panier mobile/desktop, estimateur avec et sans code postal, accès au checkout et absence de RequireJS/Knockout sur les pages Hyvä.

Commence maintenant par l'audit Git et des implémentations natives/surchargées du panier. Ne configure aucun transporteur du lot 5.
