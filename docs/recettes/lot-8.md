# Recette du lot 8 — recette de production, exploitation et ouverture

Branche : `lot-8-ouverture`, créée depuis `main` (`596ae3d`).
Journal tenu au fil du lot ; aucune valeur secrète n'y figure (ni chemin d'administration, ni identifiant HTTP, ni accès
SSH, ni clé Stripe, ni code enseigne).

## Décisions de Pierre

- 14/10/2026, avec Céline : carte bancaire, Apple Pay et Google Pay seulement (Bancontact, iDEAL, Wero, Klarna coupés).
- 23/10/2026 : compte Mondial Relay pro ouvert, code enseigne saisi en configuration (local et serveur), jamais versionné.
  Clé privée et code marque de l'API conservés par Pierre pour une automatisation future.
- 23/10/2026 : **Mollie abandonné** (compte fermé) au profit de **Stripe**, compte ouvert par Pierre en « tunnel de
  paiement personnalisé ». Les commandes de test Mollie ne sont pas supprimées : la commande de 2 € du serveur se solde
  par un avoir hors ligne (numérotation des factures).

## Stripe — recette locale (23/10/2026)

| Contrôle | Résultat |
|---|---|
| `env:check` local après saisie des clés de test | « Stripe : mode test, clés renseignées », 0 erreur |
| Webhooks automatiques en local | Refusés par Stripe (« URL must be publicly accessible ») : attendu sur `localhost` |
| Étape paiement, livraison en point relais | Seul « Carte bancaire » proposé ; formulaire Stripe intégré ; total 30,90 € (24 + 4,90 + 2 de cadeau) |
| Carte de test 4242, CGV cochées | Commande `000000025`, page de confirmation avec le point relais |
| Commande en base | `processing` / **Paiement reçu**, 30,90 € payés et facturés, facture `000000010` avec l'emballage, transaction `pi_…` |
| Console du tunnel | Aucune erreur Stripe une fois les clés saisies (Montserrat du widget Mondial Relay : limite connue du 6b) |
| Page de commande invité (Hyvä) | Totaux Hyvä conservés, ligne « Emballage cadeau 2,00 € » affichée |
| Commandes Mollie anciennes | Bloc « Paiement » lisible pour les 11 commandes locales (substitution native) |
| Tests | 117 tests, 300 assertions ; PHPCS Magento2 sans erreur |

## Stripe — recette serveur en mode test

À dérouler après le déploiement de `lot-8-ouverture` et la saisie des clés de **test** dans l'administration du serveur.
Déployée le 23/10/2026 : version `20261023-165441-1a22192` (fusion de la PR #1 dans `main`, contenu identique à `75ba4e2`).

| Contrôle | Attendu | Résultat |
|---|---|---|
| Bascule | `setup:upgrade` passé, `env:check --serveur --noindex` sans erreur Stripe | 23/10 : base à jour, `StripeIntegration_Payments` actif, Mollie absent. `env:check` : 1 erreur « Stripe : mode test sans clés » — **aucune ligne `payment/stripe*` en base**, clés à enregistrer dans l'administration du serveur. Après enregistrement par Pierre (portée par défaut, mode test) : **0 erreur, 0 avertissement** |
| Webhooks | Endpoint `https://madame-aiguille.fr/stripe/webhooks` créé dans le compte Stripe (sinon `stripe:webhooks:configure`) | 23/10 : URL publique servie par le module (GET 200 « accessible », POST 400 « no signing secrets configured ») ; endpoint pas encore créé, faute de clés. À l'enregistrement des clés : endpoint `we_…` **créé automatiquement** dans le compte de test, premier évènement signé reçu et vérifié (200 OK) |
| Pages juridiques | CGV et confidentialité citent Stripe, plus Mollie | 23/10 : **OK**, Stripe ×1 (CGV) et ×2 (confidentialité), plus aucun Mollie dans le contenu. Le menu affiche encore la catégorie « Catégorie test final Mollie » : à désactiver dans l'administration |
| Relais + cadeau + CGV, carte 4242 | « Paiement reçu », facture avec emballage, email « Paiement reçu » **reçu** | |
| Carte refusée (4000 0000 0000 0002) | Message dans le tunnel, panier conservé, aucune commande payée | |
| 3-D Secure (4000 0027 6000 3184) | Fenêtre d'authentification, puis « Paiement reçu » | |
| Apple Pay / Google Pay | Proposés sur un appareil compatible, domaine déclaré dans Stripe | |
| Remboursement | Avoir en ligne depuis la facture → remboursement visible dans Stripe, emballage remboursé | |
| Retrait à l'atelier | Seul « Paiement sur place » proposé ; facturé par Céline → « Paiement reçu » | |
| Commandes de test | Annulées ou remboursées dans l'administration | |

À noter : l'enregistrement de la section Stripe dans l'administration écrit tous ses champs en base. `receipt_emails` y vaut `1` (le défaut du module Checkout est `0`) : Stripe envoie alors son propre reçu en plus de l'email de commande de Magento. À trancher par Pierre.

Les paiements de test sur `madame-aiguille.fr` sont saisis par Pierre (domaine de production) ; l'agent vérifie ensuite commande, facture, webhooks et emails côté serveur.
