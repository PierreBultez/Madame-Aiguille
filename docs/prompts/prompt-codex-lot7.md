# Reprise du développement — lot 7 Compte client, emails, statuts de commande

Tu reprends le développement de **Madame Aiguille**, une boutique Magento Open Source 2.4.9 avec Hyvä 1.5.2. Réponds en français et travaille avec Pierre, développeur Laravel/Vue/Tailwind et ancien responsable technique Magento 2.

## État de départ à vérifier

- Dépôt : `/home/pierre/Documents/aiguille`
- Application Magento : `/home/pierre/Documents/aiguille/shop`
- Branche livrée : `codex/lot-4-panier`, commits `3316965` → `38ac05c`
- Lots livrés : 1 Fondations, 2 Catalogue, 6a installation du checkout Luma fallback, 3 Accueil/CMS/Contact, 4 Panier et mini-panier
- **Les lots 5 (livraison et paiements) et 6b (habillage du checkout) sont en attente**, le temps que Pierre crée le compte marchand Mollie et fasse le brief livraison avec Céline. L'ordre est donc **4 → 7 → 5 → 6b → 8**.

Commence par `git status`, `git log --oneline --decorate -n 20` et la lecture de :

1. `docs/documentation-theme.md`, surtout §1 à §4, §16, §17, §21 et §22 ;
2. `docs/plan-de-developpement.md`, surtout Lot 7 et la note de version v2.1 ;
3. `docs/cahier-des-charges-docs-developpement/specification-fonctionnelle.md`, sections compte client, commandes et emails ;
4. `docs/cahier-des-charges-docs-developpement/guide-bonnes-pratiques-hyva.md` ;
5. `docs/cahier-des-charges-docs-developpement/plan-de-tests.md`, partie compte client ;
6. `docs/maquettes-direction-artistique/Compte client.dc.html` (artboards 10, 10b, 10c) et les composants de formulaire du `Design System.dc.html`.

Si le lot 4 a été fusionné, pars de la branche cible à jour. Sinon, pars directement de `codex/lot-4-panier`. Crée la nouvelle branche `codex/lot-7-compte-emails` avant toute modification.

## Ce qui est déjà tranché

- **Prestataire de paiement : Mollie**, pas Stripe (décision du 10/09/2026). `mollie/magento2` 3.1.3, `mollie/magento2-hyva-compatibility` et le bundle de thème Hyvä sont **déjà installés et activés**. Le compte marchand n'existe pas encore : `payment/mollie_general/enabled = 0`, mode `test`. **N'installe rien, ne configure aucune clé.**
- Commande invité autorisée (`checkout/options/guest_checkout = 1`).
- Le checkout bascule toute la page vers le thème Luma (module Theme Fallback). Les pages du compte client, elles, restent en Hyvä : c'est bien le thème enfant `MadameAiguille/default` qu'on habille.
- `Hyva_Email` est activé et sera la base des gabarits d'email.

## Règles non négociables

- **Toutes les surcharges visuelles vivent dans le thème enfant** `shop/app/design/frontend/MadameAiguille/default/` : layouts, `.phtml`, Tailwind/CSS, assets et JavaScript Alpine. Les modules `app/code` ne portent que la logique métier, les services, ViewModels, data patches et configurations.
- Ne jamais modifier `vendor/`, `pub/static/` ni `web/css/styles.css`. Ne pas ajouter de `tailwind.config.js`.
- Préserver les composants natifs Magento et Hyvä : formulaires de compte, validation, sections privées, customer-data, pagination des commandes. Retemplater seulement ce qui est nécessaire.
- Zéro logique métier dans les `.phtml` ; ViewModel ou bloc pour tout calcul. Escaping systématique et `__()` pour les textes.
- Toutes les couleurs passent par les tokens existants. Cibles tactiles de 44 px, focus visible et WCAG 2.1 AA.
- Annoncer explicitement chaque commande `composer` ou `bin/magento` avant de l'exécuter.
- Commits atomiques en français avec les trailers du dépôt. Vérification visuelle à 1440 et 390 px à chaque étape.
- Ne pas installer d'extension tierce, ne pas configurer Mollie, ne pas commencer les lots 5 ou 6b.

## Périmètre du lot 7

1. **Pages du compte client** (`customer_account_*`, `sales_order_*`)
   - navigation latérale restylée, tableau de bord, mes commandes, détail de commande, adresses, informations personnelles, mot de passe oublié et réinitialisation ;
   - la liste des commandes suit la maquette : une carte par commande avec **une phrase qui dit où en est la commande**, pas seulement un statut sec ;
   - le détail de commande affiche le suivi en étapes datées, les articles avec leur variante, les adresses, le mode de livraison et de paiement, les totaux ;
   - formulaires au gabarit du Design System, messages d'erreur natifs conservés et traduits.

2. **Statuts de commande**
   - états et statuts Magento par data patch du module : en attente de paiement, paiement reçu, en préparation, expédiée, prête pour retrait, livrée ;
   - libellés français côté client **et** côté back-office ; rejouer `setup:upgrade` ne doit jamais créer de doublon ;
   - un ViewModel expose au thème la phrase d'avancement et l'étape courante — jamais de `switch` sur un code de statut dans un `.phtml`.

3. **Emails transactionnels**
   - en-tête et pied aux couleurs de la marque, fontes du thème, logo officiel ;
   - variantes : confirmation de commande, paiement reçu, expédition, prête pour retrait, bienvenue, réinitialisation de mot de passe ;
   - l'accusé de réception du formulaire de contact (lot 3) doit adopter le même habillage.

4. **Textes français**
   - dictionnaire `i18n/fr_FR.csv` du thème pour les chaînes encore en anglais du compte et des commandes. Les paquets de langue Magento 2.4.9 sont **vides** : sans ces entrées, la boutique affiche « Sign In », « My Account », « Order # », etc.

5. **Styleguide et documentation**
   - ajouter au `/styleguide` les états utiles : carte de commande par statut, étapes de suivi, navigation du compte ;
   - compléter `documentation-theme.md` — une section pour les templates et ViewModels, et le **§22 Mémo Céline** pour ce qu'elle peut régler seule (textes d'emails, coordonnées bancaires, libellés de statuts) ;
   - mettre à jour le journal des livraisons et le plan de développement.

## Ce qui ne pourra pas être bouclé dans ce lot

À isoler dès le départ pour ne pas bloquer le reste, et à consigner comme limites :

- la recette de bout en bout d'une commande réellement payée qui parcourt tous les statuts : elle attend Mollie (lot 5) ;
- le contenu exact de l'email de virement — RIB, référence, délai d'annulation : construis la structure, les valeurs viennent du lot 5 ;
- l'email « prête pour retrait », qui dépend des modalités de la remise en main propre, à cadrer avec Céline ;
- la délivrabilité (SPF, DKIM, DMARC) et le SMTP, qui dépendent du domaine — lot 8.

## Décisions à demander à Pierre avant de coder

1. **Jeu de statuts** : garde-t-on « prête pour retrait » alors que la remise en main propre n'est pas encore confirmée par Céline, ou livre-t-on cinq statuts quitte à en ajouter un au lot 5 ?
2. **Gabarits d'email** : on part des templates natifs Magento habillés via `Hyva_Email`, ou on écrit des gabarits propres au thème ? Le premier est plus sûr pour les montées de version, le second plus fidèle à la maquette.

## Pièges connus sur ce projet

- **Après avoir ajouté un plugin, `setup:upgrade --keep-generated` ne suffit pas** : la liste des plugins reste figée dans `generated/metadata`. Il faut `bin/magento setup:di:compile`, sinon le plugin est ignoré silencieusement.
- **La feuille compilée est servie sous une URL versionnée figée** : après `npm run build`, le navigateur peut continuer à servir l'ancienne. Avant de conclure à un bug de CSS, comparer la taille du fichier servi et celle de `web/css/styles.css`.
- **Ne jamais déplacer un `.phtml` du thème pour « tester sans la surcharge »** : Magento mémorise la résolution et continue de servir le template du parent après restauration.
- Sur un `<dialog>` piloté par `x-htmldialog`, ne pas utiliser les attributs `x-transition` : ils retardent l'ouverture de plusieurs secondes et empêchent la fermeture. Animer en CSS.
- Pour tester un état vide ou une suppression : l'admin, jamais un script destructif.

## Méthode attendue

Présente d'abord un plan court et les deux décisions ci-dessus. Travaille ensuite par étapes, un commit atomique par étape, avec une liste de recette précise.

Teste au minimum : création de compte, connexion, déconnexion, mot de passe oublié, tableau de bord, liste de commandes vide puis remplie, détail d'une commande, ajout et modification d'adresse, changement de mot de passe, rendu des emails, comportement à 1440 et 390 px, et absence de RequireJS/Knockout sur les pages Hyvä du compte.

Commence maintenant par l'audit Git et par l'inventaire de ce qui est natif ou déjà surchargé sur les pages du compte client.
