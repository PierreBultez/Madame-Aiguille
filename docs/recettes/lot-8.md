# Recette du lot 8 — recette de production, exploitation et ouverture

Branche : `lot-8-ouverture`, créée depuis `main` (`596ae3d`).
Journal tenu au fil du lot ; aucune valeur secrète n'y figure (ni chemin d'administration, ni identifiant HTTP, ni accès
SSH, ni clé Stripe, ni code enseigne).

## Décisions de Pierre

- 14/10/2026, avec Céline : carte bancaire, Apple Pay et Google Pay seulement (Bancontact, iDEAL, Wero, Klarna coupés).
- **30/10/2026 : changement de stratégie**, cinq moyens retenus : carte bancaire, Apple Pay, Google Pay, Bancontact et Klarna. Tableau de bord Stripe et administration réglés par Pierre : n'y rien changer.
- 30/10/2026 : le retrait à l'atelier se règle en ligne ou sur place (carte au TPE ou espèces).
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
| Relais + cadeau + CGV, carte 4242 | « Paiement reçu », facture avec emballage, email « Paiement reçu » **reçu** | 23/10 : commande `000000002`, point relais FR-017502, 19,95 + 4,90 + **2,00 de cadeau** = 26,85 € capturés (Visa), **Paiement reçu**, facture `000000002` payée avec l'emballage ; webhooks `payment_intent.succeeded` et `charge.succeeded` reçus (200, le second mis en file pendant la création puis traité) ; emails de commande et de facture marqués envoyés, aucun échec de notification journalisé. **Reçus par Pierre** : confirmation de commande, facture et « Paiement reçu » |
| Carte refusée (4000 0000 0000 0002) | Message dans le tunnel, panier conservé, aucune commande payée | 23/10 : **OK**, deux refus « Your card was declined » (18 h 19, 18 h 20 UTC), aucune commande, panier toujours actif, webhooks `payment_failed` reçus (200) |
| 3-D Secure (4000 0027 6000 3184) | Fenêtre d'authentification, puis « Paiement reçu » | 23/10 : **OK**, commande `000000003` : « Authentication Required » au premier passage, puis commande créée et payée 9 s plus tard sur le même paiement, **Paiement reçu**, facture `000000003` (sans cadeau, non choisi pour ce test) |
| Apple Pay / Google Pay | Proposés sur un appareil compatible, domaine déclaré dans Stripe | |
| Remboursement | Avoir en ligne depuis la facture → remboursement visible dans Stripe, emballage remboursé | 30/10 : **OK**, avoir `000000002` créé par Pierre depuis la facture (« Rembourser ») : 19,95 + 4,90 + **2,00 d'emballage** = 26,85 € remboursés **en ligne** (transaction `refund`), commande `closed` ; webhooks `charge.refunded` et `refund.updated` reçus (200), confirmation Stripe dans l'historique. Email d'avoir non envoyé (case « copie par email » non cochée) |
| Retrait à l'atelier | Au choix « Paiement sur place (carte ou espèces) » ou « Carte bancaire en ligne » (décision du 30/10) ; payé en ligne → « Paiement reçu » ; sur place, facturé par Céline → « Paiement reçu » ; email « Prête pour retrait » adapté au mode de règlement | |
| Commandes de test | Annulées ou remboursées dans l'administration | 30/10 : `000000002` et `000000003` remboursées en ligne par avoir (Pierre), `closed` ; email d'avoir de la `000000003` envoyé |

À noter : l'enregistrement de la section Stripe dans l'administration écrit tous ses champs en base. `receipt_emails` y vaut `1` (le défaut du module Checkout est `0`) : Stripe envoie alors son propre reçu en plus de l'email de commande de Magento. À trancher par Pierre.

Les paiements de test sur `madame-aiguille.fr` sont saisis par Pierre (domaine de production) ; l'agent vérifie ensuite commande, facture, webhooks et emails côté serveur.

Commande Mollie `000000001` du serveur : `closed` au 23/10/2026 (soldée par avoir).

Relevé en passant dans les journaux du serveur (hors Stripe) : avertissements PHP 8.5 « Using null as the key parameter for array_key_exists() » dans `magento/module-captcha`, journalisés en CRITICAL ; « The product with ID "1" does not exist » (produit supprimé encore référencé). À examiner.

Les lignes INFO « Could not retrieve checkout session: The resource ID cannot be null or whitespace » du module Stripe sont attendues : le flux intégré n'utilise pas de session Stripe Checkout.

## Retrait payé en ligne ou sur place (30/10/2026, local)

Décision de Pierre : le retrait à l'atelier se règle au choix en ligne (Stripe) ou sur place (carte au TPE ou espèces) ; le point relais reste payé en ligne.

| Contrôle | Résultat |
|---|---|
| Libellés | « Sur rendez-vous, en ligne ou sur place » (étape Livraison et récapitulatif), remplacé le jour même à la demande de Pierre par « Sur rendez-vous à l'atelier de Saint-Épain (37800) » ; « Paiement sur place (carte ou espèces) » et « Carte bancaire en ligne » à l'étape Paiement |
| Retrait, étape Paiement | Les deux moyens proposés ; point relais : carte en ligne seule (tests unitaires) |
| Retrait payé par carte de test 4242 | Commande `000000026`, **Paiement reçu**, 24 € facturés, créneau du 12/11 à 10 h réservé et rattaché, confirmation avec rendez-vous et itinéraire |
| Email « Prête pour retrait » (rendu sans envoi) | Payé en ligne : « Votre commande est déjà réglée en ligne. » ; sur place : « Le règlement se fait sur place, par carte ou en espèces. » ; rendez-vous et itinéraire présents |
| Défaut corrigé au passage | Le gabarit imbriquait `{{depend pickup_directions_url}}` dans `{{depend pickup_slot}}` : le rendu levait une `TypeError`, l'email « Prête pour retrait » avec rendez-vous ne pouvait pas partir (antérieur au lot 8). Remplacé par `{{if}}` |
| Pages juridiques locales | CGV §5 et §7, page Livraison et retours : « en ligne ou sur place » |
| Tests | 120 tests, 303 assertions ; PHPCS Magento2 sans erreur |

## Serveur après déploiement du 30/10/2026

Version `20261030-100325-328149f` (fusion de la PR #2 dans `main`).

| Contrôle | Résultat |
|---|---|
| Titres de paiement | « Carte bancaire en ligne » et « Paiement sur place (carte ou espèces) » en base |
| Pages juridiques | CGV §5 et §7, Livraison et retours : « en ligne ou sur place » |
| `env:check --serveur --noindex` | 0 erreur ; 1 avertissement « boutons express actifs » : Pierre avait activé l'emplacement « Checkout, within payment form » avec Apple Pay et Google Pay, **le bon réglage** (sans lui, ces portefeuilles sont masqués). Contrôle affiné : il n'avertit plus que pour les emplacements hors du formulaire, ou si Apple Pay / Google Pay sont masqués. Klarna coché dans ce réglage : conforme à la décision du 30/10/2026 (cinq moyens, Klarna compris) |
| Après le déploiement de la PR #3 (`20261030-101836-0b188f8`) | Libellé du retrait « Sur rendez-vous à l'atelier de Saint-Épain (37800) » en ligne ; `env:check --serveur --noindex` à **0 erreur, 0 avertissement** ; **Stripe en mode live** (clés live renseignées) : tout paiement par carte est désormais réel |
