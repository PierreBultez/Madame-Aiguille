# Reprise du développement — lot 6b Habillage du tunnel de commande

Tu reprends le développement de **Madame Aiguille**, une boutique Magento Open Source 2.4.9 avec Hyvä 1.5.2. Réponds en français et travaille avec Pierre, développeur Laravel/Vue/Tailwind et ancien responsable technique Magento 2.

## État de départ à vérifier

- Dépôt : `/home/pierre/Documents/aiguille` · Application Magento : `.../shop`
- Branche de référence : `main`, **lot 5 fusionné et poussé** (dernier commit de documentation du lot 5)
- Lots livrés : 1 Fondations, 2 Catalogue, 6a installation du checkout Luma fallback, 3 Accueil/CMS/Contact, 4 Panier, 7 Compte client et emails, **5 Livraison et paiements (hors 8a)**
- Ordre restant : **6b**, avec **8a** (mise en ligne anticipée) en parallèle dès que Pierre fournit domaine et serveur, puis **8**

Commence par `git status`, `git log --oneline --decorate -n 20` et la lecture de :

1. `AGENTS.md` — règles de travail du dépôt et **rituel de fin de lot** ;
2. `docs/documentation-theme.md`, surtout §2 (build et pièges de cache), **§14** (fonctionnement du fallback Luma et emplacements prévus pour le 6b), §16, §22 (emails), **§23** (ce qui reste), **§26** (lot 5 : composants du tunnel et pièges) et §25 ;
3. `docs/plan-de-developpement.md`, section **Lot 6** et note de version **v2.7** ;
4. `docs/cahier-des-charges-docs-developpement/specification-fonctionnelle.md` §4 (tunnel) et `charte-graphique.md` ;
5. `docs/brief-call-celine-2026-09-11.md` §6 (questions checkout posées à Céline) ;
6. les maquettes du tunnel dans `docs/maquettes-direction-artistique/`.

Crée la branche `codex/lot-6b-habillage-tunnel` depuis `main` à jour, avant toute modification.

## Ce qui est déjà tranché

- **Checkout : Luma Fallback** (`hyva-themes/magento2-luma-checkout` 1.1.7). **Toute la page** du tunnel bascule sur le thème `Magento/luma` : rien du thème Hyvä n'est hérité, ni header, ni footer, ni CSS Tailwind, ni traductions.
- **Habillage aux couleurs, pas au pixel** : palette, fontes de la marque (les mêmes `.woff2` et licences), boutons et cibles de 48 px, champs, messages. On retemplate le natif, on ne réécrit pas sa structure. Pas de Hyvä Checkout (licence écartée).
- **Thème Luma enfant à créer** : `shop/app/design/frontend/MadameAiguille/checkout/`, parent `Magento/luma`, puis `hyva_theme_fallback/general/theme_full_path = frontend/MadameAiguille/checkout`. Emplacements : `documentation-theme.md` §14.
- **Deux étapes natives** (Livraison → Paiement et récapitulatif), commande invité autorisée.
- **Composants livrés bruts au lot 5**, à habiller **sans toucher à leur logique** : carte Mondial Relay (`relay-point.html`), rendez-vous de retrait (`pickup-slot.html`), case d'emballage cadeau (`gift-wrap.html`), ligne d'emballage du récapitulatif (`summary/gift-wrap.html`). Ils vivent dans `MadameAiguille_Checkout/view/frontend/web/template/` ; les surcharger au même chemin relatif dans le thème Luma enfant. Leurs classes `madameaiguille-*` sont faites pour être stylées.
- **Moyens de paiement** : ceux que Pierre a activés dans l'admin (carte bancaire, Apple Pay, Google Pay, Bancontact, iDEAL, Wero, Klarna) et « Paiement sur place », réservé au retrait. **Ne pas modifier la configuration Mollie** : l'écart avec le call (carte seule) est consigné au §23 et appartient à Pierre.
- **Site monolingue français**, zone France, Belgique, Luxembourg (Monaco suit la France).

## Règles non négociables

Celles d'`AGENTS.md`, plus :

- **Ne pas installer d'extension tierce sans accord explicite de Pierre**, et jamais sans avoir annoncé la commande `composer`.
- Luma compile du **LESS** : pas de Tailwind dans le thème du tunnel. Les tokens de couleur se recopient en variables LESS, synchronisées à la main avec `hyva.config.json` (même principe que les styles d'email du lot 7, `web/css/source/_email-variables.less`).
- Ne jamais modifier `vendor/` ni `pub/static/`. Le thème `MadameAiguille/default` (Hyvä) ne doit pas changer pour les besoins du tunnel.
- La logique du lot 5 (validation, réservation des créneaux, totaux, CSP) ne se modifie pas pour des raisons visuelles. Une régression fonctionnelle du tunnel bloque le lot.
- **CSP bloquante sur le tunnel** : aucun script inline, aucun `onclick`. Tout JavaScript passe par RequireJS.
- Contrôle visuel à 1440 et 390 px à chaque étape, **dans le tunnel réel** avec un produit au panier, pour les trois parcours : point relais, retrait sur rendez-vous, emballage cadeau coché.

## Périmètre du lot 6b

1. **Thème Luma enfant** : déclaration, fontes et licences, variables LESS de la marque, logo officiel, configuration du fallback. Vérifier que le panier, le compte et les autres pages restent en Hyvä.
2. **En-tête et pied du tunnel** : logo, lien de retour au panier, mentions minimales. Pas de navigation complète.
3. **Formulaires et étapes** : champs, libellés, erreurs, boutons, barre d'étapes, récapitulatif latéral, à 1440 et 390 px. Téléphone obligatoire si Céline le confirme (utile aux SMS Mondial Relay).
4. **Composants du lot 5** : carte Mondial Relay (paramètre `CSS: 0` du widget possible pour fournir sa propre feuille), choix du jour et de l'heure en boutons de 44 px minimum, case d'emballage, ligne du récapitulatif.
5. **Traductions du tunnel** : `i18n/fr_FR.csv` propre au thème Luma enfant. Libellés encore anglais relevés au lot 5 : « Next », « Shipping Methods », « Order Summary », « Ship To », « Cart Subtotal », « My billing and shipping address are the same », « Apply Discount Code »… Relever les clés exactes sur la page.
6. **Case CGV obligatoire** (*Ventes › Paiement › Conditions générales*, contenu lié à la page `cgv`) et case newsletter non pré-cochée, si Céline les confirme.
7. **Pages de confirmation** (Hyvä, en `.phtml` dans le thème `default`) : succès — rendez-vous et lieu pour un retrait, point relais pour un colis — et échec de paiement (panier conservé). Elles sont **encore en anglais** (« Thank you for your purchase! »).
8. **reCAPTCHA** sur la création de compte au tunnel, avec le mécanisme natif.

## Ce qui ne pourra pas être bouclé dans ce lot

- **Paiement réel et validation Mollie** : ils dépendent de 8a (domaine, préproduction HTTPS, webhook Mollie joignable).
- **Code enseigne Mondial Relay** : tant que `BDTEST` est en place, le widget affiche un avertissement « compte de démonstration », qu'aucun style ne doit masquer.
- **Onglets Horaires / Photo** de l'infobulle du widget : bloqués par la CSP (identifiants variables), limite assumée du lot 5.

## Décisions à demander à Pierre avant de coder

1. **Fidélité visuelle** : jusqu'où suivre les maquettes du tunnel (couleurs, fontes et cibles seulement, ou aussi la mise en page du récapitulatif) ?
2. **Réponses de Céline au §6 du brief** : téléphone obligatoire, case CGV, case newsletter, champ « message pour Céline » (le message cadeau natif existe déjà dans le panier).
3. **Moyens de paiement Mollie** : la configuration actuelle est-elle définitive ? Elle conditionne les logos à afficher et la CGV.

## Pièges connus sur ce projet

- **`bin/magento config:show` ne renvoie rien pour une valeur par défaut** : passer par `ScopeConfigInterface`.
- **Ouvrir chaque page d'administration touchée** : une section de configuration peut tomber sans que rien d'autre ne le montre (§22). L'agent n'a pas de session d'administration : demander à Pierre.
- **Après un plugin ou un argument `di.xml`** : `bin/magento setup:di:compile`. Si elle échoue sur « directory not empty », php-fpm régénérait des classes en même temps : relancer.
- **Fichiers statiques figés en développement** : `Cache-Control: immutable` ; ni Ctrl+Maj+R ni `cache:flush` ne suffisent pour un JS ou un LESS de Luma. Supprimer `pub/static/deployed_version.txt` (régénéré à la requête suivante). Pour une traduction JS : supprimer `pub/static/frontend/<thème>/fr_FR/js-translation.json` et vider `mage-translation-storage` du `localStorage`. La page du tunnel elle-même peut rester en cache : recharger par une URL différente.
- **Clé de traduction avec deux-points dans un gabarit Knockout** : le préprocesseur produit une liaison invalide et Knockout abandonne tout le sous-arbre, sans erreur.
- **Ordre des totaux du récapitulatif** : fixé par *Ventes › Ordre des totaux du tunnel* (livraison 30, taxe 40) ; l'emballage est à 35.
- **Données du tunnel en `localStorage`** (`checkout-data`) : un ancien état peut préremplir le formulaire. Vider le stockage du navigateur entre deux recettes.
- **Commandes de test** : elles réservent du stock et des créneaux. Les annuler dans l'administration, jamais par script. Un produit sans stock vendable donne « Some of the products are disabled ».
- **La feuille Hyvä compilée est servie sous une URL versionnée figée** : comparer la taille du fichier servi et celle de `web/css/styles.css`.
- **Ne jamais déplacer un `.phtml` du thème pour « tester sans la surcharge »** : Magento mémorise la résolution.
- Pour tester un état vide ou une suppression : l'admin, jamais un script destructif.

## Méthode attendue

Présente d'abord un plan court et les trois décisions ci-dessus. Travaille ensuite par étapes, un commit atomique par étape, avec une liste de recette précise. **Termine le lot par le rituel du §25** de `documentation-theme.md` : recette écrans et administration, documentation complète dont le mémo Céline, fusion en avance rapide dans `main`, push, puis prompt de reprise du lot suivant.

Commence par l'audit Git, la lecture du §14 et du §26, la création du thème Luma enfant et sa configuration comme thème du fallback, avec une capture du tunnel avant / après à 1440 et 390 px.
