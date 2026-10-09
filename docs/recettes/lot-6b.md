# Recette du lot 6b — tunnel de commande

Date : 09/10/2026. Branche : `lot-6b-habillage-tunnel`, créée depuis `main` (`8ee229c`).

## Décisions de Pierre

- Reprendre les couleurs, les fontes et aussi la mise en page du récapitulatif des maquettes.
- Téléphone obligatoire (déjà réglé sur `req`), CGV obligatoires, newsletter facultative décochée.
- Moyens Mollie conservés dans leur configuration actuelle, encore à valider avec Céline.
- Aucun champ supplémentaire « Message pour Céline » : conserver le message cadeau natif du panier.
- Génération des assets par Magento autorisée explicitement ; aucune édition manuelle de `pub/static`.

## État de départ

Arbre propre sur `traduction-fr` (`6690881`). Trois commits locaux après `main`, laissés sur cette branche.
Le paquet de langue français est présent dans `vendor` en local, mais sa dépendance Composer n'est pas
encore fusionnée dans `main` : les traductions observées en local ne prouvent pas sa reproductibilité.

Produit de recette : Pochettes Nomades (`MA-POC-NOM`), 18 €, une unité ajoutée depuis la fiche produit.
Aucune commande créée à l'étape du socle.

## Socle du thème

- Thème `MadameAiguille/checkout`, parent `Magento/luma` ; fallback versionné dans `Theme/etc/config.xml`.
- Fontes Sentient originales copiées localement ; WOFF2 ignorés par Git, licences conservées.
- Logo officiel, retour au panier, liens CGV / confidentialité / contact, copyright configuré.
- Captures avant et socle dans `docs/recettes/lot-6b/`, à 1440 et 390 px.
- Enregistrement du thème et compilation LESS réussis ; PHPCS des premiers PHP/PHTML : aucune erreur.

### Cache à surveiller

`setup:static-content:deploy` ignore les CSS déjà publiés : son succès ne prouve pas leur fraîcheur.
Purger les assets générés du **seul** thème checkout avec le service Magento `DeployStaticFile`, ainsi que
son cache LESS dans `var/view_preprocessed`, avant de régénérer. Utiliser ensuite une URL de recette
différente : le HTML du tunnel peut aussi rester en cache. Contrôler les règles et les dimensions dans
le navigateur. Conserver les 24 colonnes de Luma (`@total-columns`), sinon sa colonne de 16 déborde.

## Recette à terminer

- Livraison / paiement à 1440 et 390 px : point relais, retrait sur rendez-vous, emballage coché.
- Champs requis, erreurs, clavier, focus, récapitulatif mobile, changement de pays et de mode.
- CGV : blocage sans consentement côté navigateur et serveur ; ouverture du lien vers `cgv`.
- Newsletter : absence de précochage, absence de désinscription implicite, double opt-in natif.
- Succès : rendez-vous et lieu / point relais. Échec : message, retour au panier conservé.
- Panier et compte toujours Hyvä, absence de RequireJS / Knockout sur ces pages.
- Administration : Theme Fallback, Conditions générales, options du tunnel, abonnés newsletter,
  reCAPTCHA, écrans du lot 5. Session admin demandée à Pierre.
- Tests unitaires du module, PHPCS, documentation, styleguide, fusion linéaire et push.

## Limites connues

- Paiement réel et webhook : dépendance 8a (domaine et HTTPS).
- `BDTEST` : avertissement de démonstration à conserver visible.
- Onglets Horaires / Photo du widget bloqués par sa CSP : limite du lot 5 conservée.
- Clés reCAPTCHA de production et validation de leurs domaines : Pierre, avant ouverture des ventes.
