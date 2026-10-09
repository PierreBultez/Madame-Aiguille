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

## Recette réalisée

Navigateur intégré Codex : vrais écrans, contrôles successifs en viewports **1440 × 1000** et **390 × 844**, dimensions lues dans le DOM. Pour les dernières captures relais, le réglage du viewport du navigateur ne s’appliquait pas de façon fiable : contrôle dans un cadre de même origine affichant le **vrai tunnel Magento**, aux dimensions explicites 1440 × 1000 et 390 × 844 ; fichier de recette temporaire supprimé ensuite. Données fictives `Recette Tunnel`, `recette6b@example.invalid`, téléphone de recette ; aucun paiement financier exécuté.

| Contrôle | Résultat / preuve |
|---|---|
| Livraison retrait, cadeau coché | Contrôlé aux deux tailles ; jour et heure sélectionnables, confirmation du choix, retour livraison depuis paiement ; `retrait-1440.jpg`, `retrait-390.jpg` |
| Champs obligatoires / rendez-vous absent | Blocage et erreurs françaises ; téléphone requis (`customer/address/telephone_show=req`) |
| Point relais | Liste et carte réelles, point `FR-087807` (TABAC PRESSE, Les Ormes), sélection au clic et par **Entrée** ; `relais-1440.jpg`, `relais-390.jpg` |
| Widget | Sentient calculée à **16 px** sur un résultat, cible supérieure à 44 px ; avertissement BDTEST visible ; pas de débordement à 390 px |
| Paiement retrait | Paiement sur place seul, adresse native et CGV ; 18 € + cadeau 2 € = **20 €** ; `paiement-1440.jpg`, `paiement-390.jpg` |
| Paiement relais | Paiements en ligne, aucun paiement sur place ; carte / Bancontact / Google Pay / Klarna affichés selon disponibilité native ; total **24,90 €** ; `paiement-relais-390.jpg`, `paiement-relais-1440.jpg` |
| Récapitulatif mobile | Tiroir natif ouvert puis fermé ; articles avant totaux et cadeau présent ; `recapitulatif-390.jpg` |
| CGV | Case décochée : placement refusé dans le navigateur. Validateur serveur natif `isValid([])=false`, `isValid(requiredIds)=true`. Lien vers `/cgv`, accord actif / manuel / toutes vues dans l’admin |
| Newsletter | Décochée au chargement / rechargement ; les deux commandes réelles contiennent `false`, grille abonnés vide. Tests du refus invité, abonnement connecté, non-désinscription et panne newsletter |
| Succès retrait réel | Commandes invitées **000000021** (15 octobre, 10 h) et **000000022** (10 h 30), cadeau compris. Numéro, rendez-vous, lieu et itinéraire corrects ; `succes-390.jpg`, `succes-1440.jpg` |
| Création de compte après commande | Lien natif vers `checkout/account/delegateCreate`, arrivée au formulaire Hyvä prérempli ; aucun compte créé pendant la recette |
| Échec | Gabarit natif ouvert directement à 1440 / 390 px, titre français, référence et lien retour panier ; `echec-1440.jpg`, `echec-390.jpg`. Ce contrôle ne vaut **pas** validation d’un retour Mollie annulé |
| Succès relais / styleguide | Vrai `Confirmation` avec commande non persistée, même gabarit de carte ; retrait / relais / absence de données couverts par tests ; `confirmations-styleguide-1440.jpg` |
| Isolation Hyvä | Panier, création de compte et succès conservent Hyvä ; aucun script RequireJS / Knockout sur les pages contrôlées |
| Administration | Fallback, CGV, options commande, newsletter / abonnés, reCAPTCHA, groupes Madame Aiguille, retrait et tarifs, moyens de paiement : ouverts sans erreur, sans modifier Mollie. Commande de retrait et facture existante ouvertes ; avoirs : grille vide |

Le passage au paiement actualise nativement les montants de livraison et de cadeau. Les montants pendant l’étape Livraison restent estimatifs et peuvent refléter le précédent choix jusqu’à cet enregistrement ; la mention du récapitulatif le précise. Le thème n’ajoute aucun calcul.

### Nettoyage des données de recette

**000000021 et 000000022 annulées via le bouton Annuler de l’administration**, confirmation visible. Vérification en lecture seule : `state=canceled`, consentement newsletter `false`, **0 réservation restante pour chacune**. Aucun script de suppression ou d’annulation. Les anciennes commandes de Pierre restent intactes ; certaines sont payées / livrées, ne pas les traiter comme jetables automatiquement.

Captures admin : `admin-cgv.jpg`, `admin-newsletter.jpg`, `admin-abonnes.jpg`, `admin-commande-021.jpg`, `admin-commande-022-annulee.jpg`. Toutes les captures sont dans `docs/recettes/lot-6b/`.

## Validation technique finale

- `setup:upgrade --keep-generated` : thème et patch CGV enregistrés, succès.
- `setup:di:compile` : succès, y compris après ajout du contrôle newsletter / session client.
- `setup:static-content:deploy -f --theme MadameAiguille/checkout fr_FR` : succès, assets générés **par Magento**, conformément à l’autorisation de Pierre ; aucun fichier généré édité manuellement.
- Build Tailwind Hyvä : succès. CSS servi et compilé identiques (`cmp`) : **205 564 octets**.
- CSS Luma servis et générés identiques : **748 490 octets** (`styles-m.css`), **158 330 octets** (`styles-l.css`).
- PHPUnit sur tous les modules maison : **106 tests, 282 assertions**, aucune dépréciation de doubles de test.
- PHPCS Magento2 sur les PHP/PHTML touchés : **0 erreur**, avertissements de docblocks / longueur de ligne (dont le styleguide préexistant). 18 fichiers au total après le correctif newsletter.
- `git diff --check` : propre. Aucun changement manuel dans `vendor`, `pub/static` ou le CSS compilé.

## Limites et suite

- **8a / Pierre** : paiement Mollie complet, confirmation réelle d’une commande relais et retour annulé/échoué avec panier restauré. Le gabarit d’échec seul et les tests du ViewModel ne prouvent pas ces parcours externes.
- **8a / Pierre** : SMTP, réception et confirmation newsletter ; reCAPTCHA n’est pas actif en local (type de création de compte non configuré). Le mécanisme natif est conservé, il reste à activer avec les clés / domaines.
- **8a / Pierre** : facture / avoir avec emballage. Une facture existante est lisible, mais la grille des avoirs ne contient aucun exemple ; aucun remboursement n’a été déclenché pour produire un écran de recette.
- **8 / recette transversale** : panier fusionné à la connexion et session réellement expirée, conservés natifs, non rejoués.
- **Céline** : code enseigne Mondial Relay réel, tarifs, lieu de retrait exact, textes juridiques. Méthodes Mollie actuelles **provisoires**, à valider avec Céline.
- **Dépendances distantes** : le widget rechargeait Leaflet malgré le chargement versionné du lot 5, causant un module AMD anonyme et des coordonnées absentes au paiement. RequireJS utilise désormais la même URL que le widget pour éviter cette seconde injection. Leaflet suit donc cette URL non versionnée ; recetter les évolutions distantes.
- **Widget tiers** : onglets Horaires / Photo de l’infobulle toujours limités par CSP ; l’avertissement `BDTEST` reste visible.
- **Provisionnement** : fontes originales non versionnées ; branche `traduction-fr` distincte non fusionnée. Le dictionnaire checkout couvre ses textes propres, la traduction globale doit être rendue reproductible avant le serveur neuf. Les libellés d’adresse masqués natifs restent « Adresse: Line 1/2 » dans l’arbre accessible local malgré les clés de traduction composées ajoutées : à harmoniser lors de l’intégration du paquet de langue.

## Livraisons

- `3416d43` — socle Luma enfant et identité.
- `2a1db4f` — tunnel, récapitulatif, composants et consentements.
- `670dfdf` — confirmations et styleguide par vrais ViewModels.
- `342bc19` — respect des réglages newsletter invités / comptes.
- `2f82598` — précision des montants estimatifs et présentation finale.
- `f80ceaf` — chargement Leaflet unique, adresse et mode de livraison rétablis au paiement relais, aucune nouvelle erreur AMD sur la recette finale.
- `92a96f6` — documentation, captures finales, recette et mémo, fusion en avance rapide dans `main` puis publication des deux branches.
- Passage de relais : prompt `docs/prompts/prompt-lot8a.md` écrit après cette publication, puis versionné et publié sur les deux branches conformément au §25.
