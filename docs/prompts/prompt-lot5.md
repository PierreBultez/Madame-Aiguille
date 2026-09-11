# Reprise du développement — lot 5 Livraison et paiements

Tu reprends le développement de **Madame Aiguille**, une boutique Magento Open Source 2.4.9 avec Hyvä 1.5.2. Réponds en français et travaille avec Pierre, développeur Laravel/Vue/Tailwind et ancien responsable technique Magento 2.

## État de départ à vérifier

- Dépôt : `/home/pierre/Documents/aiguille` · Application Magento : `.../shop`
- Branche de référence : `main`, lot 7 fusionné (`d889482` → `62d7647`)
- Lots livrés : 1 Fondations, 2 Catalogue, 6a installation du checkout Luma fallback, 3 Accueil/CMS/Contact, 4 Panier, **7 Compte client, emails et statuts de commande**
- Ordre restant : **5 → 6b → 8**

Commence par `git status`, `git log --oneline --decorate -n 20` et la lecture de :

1. `AGENTS.md` — règles de travail du dépôt et **rituel de fin de lot** ;
2. `docs/documentation-theme.md`, surtout §2, §8, §16, §20 (panier et franco), §21 et §22 (statuts et emails), **§23** (ce qui reste, avec l'état de configuration relevé le 11/09/2026) et §25 ;
3. `docs/plan-de-developpement.md`, section **Lot 5** et note de version v2.2 ;
4. `docs/cahier-des-charges-docs-developpement/specification-fonctionnelle.md`, sections livraison, paiement et checkout ;
5. `docs/cahier-des-charges-docs-developpement/plan-de-tests.md`, §2 (poids) et §6 (livraison et paiement) ;
6. `docs/documentation-theme.md` §14 pour le fonctionnement du checkout Luma fallback.

Crée la branche `codex/lot-5-livraison-paiements` depuis `main` à jour, avant toute modification.

## Ce qui est déjà tranché

- **Prestataire de paiement : Mollie** (décision du 10/09/2026), pas Stripe. `mollie/magento2` 3.1.3, `mollie/magento2-hyva-compatibility` et `hyva-themes/magento2-mollie-theme-bundle` sont **déjà installés et activés**. Mollie couvre carte bancaire **et** virement SEPA.
- **Checkout : Luma Fallback** (`hyva-themes/magento2-luma-checkout` 1.1.7). Toute la page du tunnel bascule sur Luma ; son habillage est le **lot 6b**, pas celui-ci.
- Commande invité autorisée (`checkout/options/guest_checkout = 1`).
- Franco de port **affiché à 49 €**, configurable dans *Madame Aiguille › Panier*.
- Poids des produits en **kilogrammes**, attribut obligatoire depuis le lot 2.
- Les six statuts de commande, la frise de suivi et les emails « paiement reçu » / « prête pour retrait » existent déjà (lot 7). **Ce lot doit les faire vivre pour de vrai, pas les réécrire.**

## État de la configuration relevé le 11/09/2026

Valeurs **effectives** (défauts de `config.xml` compris ; `bin/magento config:show` ne montre que ce qui est enregistré en base, il renvoie du vide sur un défaut — ne pas s'y fier seul).

| Chemin | Valeur | Commentaire |
|---|---|---|
| `general/country/default` | `FR` | corrigé |
| `general/locale/weight_unit` | `kgs` | conforme |
| `shipping/origin/country_id` | **`US`** | **à corriger**, code postal `90034` |
| `tax/defaults/country` | **`US`** | **à corriger** |
| `tax/calculation/price_includes_tax` | `0` | à trancher avec le statut fiscal |
| `carriers/flatrate/active` | `1`, `type = I`, `5.00` | provisoire, **5 € par article** |
| `carriers/freeshipping/active` | `1`, seuil `49` | correspond bien à la barre affichée |
| `carriers/tablerate/active` | `0`, condition `package_weight` | à activer et alimenter |
| `payment/mollie_general/enabled` | `0`, mode `test` | compte marchand à créer |
| `payment/checkmo/active` | `1` | chèque, **hors périmètre : à désactiver** |
| `payment/free/active` | `1` | à conserver pour les commandes à 0 € |
| Méthodes Mollie exposées | **38** | à restreindre à ce que Céline accepte |

## Règles non négociables

Celles d'`AGENTS.md`, plus :

- **Ne pas installer d'extension tierce sans accord explicite de Pierre**, et jamais sans avoir annoncé la commande `composer`.
- **Aucune clé API, aucun identifiant, aucun RIB dans le dépôt.** Les secrets se saisissent dans l'administration ou par `bin/magento config:set --lock-env`. Le RIB ne doit jamais apparaître sur une page indexable.
- Les grilles tarifaires sont **versionnées en CSV** dans `docs/` ou dans le module, avec un script d'import rejouable en staging et en production.
- Ne pas commencer l'habillage du tunnel : c'est le lot 6b.

## Périmètre du lot 5

1. **Spike Mondial Relay — à faire en tout premier, 1 à 2 jours maximum.** Identifier les modules de sélection de point relais, vérifier la compatibilité avec le **checkout natif Magento (Knockout)** — c'est lui qui tourne dans le fallback Luma —, tester en sandbox. Sortie du spike : module retenu, ou décision de repli assumée (point relais choisi par email après commande, ou champ libre dans l'adresse). **Ne pas engager le reste du lot avant cette décision.**

2. **Frais de port au poids** : trois grilles *table rates* `Weight vs. Destination` pour la France métropolitaine — Colissimo, Mondial Relay, Chronopost. Paliers calés sur les grilles publiques plus le poids d'emballage. Activer `carriers/tablerate`, retirer Flat Rate.

3. **Remise en main propre** : méthode à 0 €, restreinte par code postal si Céline le souhaite, libellés et texte d'aide en français. C'est elle qui donne son sens au statut « Prête pour retrait » du lot 7.

4. **Paiements** : activer Mollie avec les clés de test puis de production, restreindre les méthodes proposées, activer le virement bancaire natif (RIB et référence après validation et dans l'email), traiter le paiement hors ligne du retrait.

5. **Cohérence avec le lot 7** : câbler les statuts sur les événements réels — paiement capté → « Paiement reçu », expédition → « Expédiée ». Vérifier que les deux notifications partent bien, et **compléter l'email de virement** (RIB, référence, délai d'annulation) dont la structure existe déjà.

6. **TVA et mentions** : paramétrage selon le statut fiscal de Céline (franchise en base probable → prix TTC = HT, mention « TVA non applicable, art. 293 B du CGI »), pays de taxe sur la France.

7. **Expiration des commandes en attente de virement** : cron d'annulation et relibération du stock après N jours, relance à J-2.

8. **Contrôle « poids renseigné »** : commande CLI `madameaiguille:catalog:check-weight` ou requête SQL documentée, listant les produits publiés sans poids. À passer avant chaque mise en production.

9. **Aligner l'affichage sur le réel** : `product_reassurance`, `cart_reassurance`, le footer, la page *Livraison et retours* et la mention sous le prix ne doivent plus annoncer que des modes réellement activés.

## Ce qui ne pourra pas être bouclé dans ce lot

- L'**habillage du tunnel** : lot 6b.
- La **délivrabilité** (SPF, DKIM, DMARC) et le SMTP : lot 8.
- Les **clés de production** Mollie, tant que le compte marchand n'est pas validé par le prestataire.

## Décisions à demander à Pierre avant de coder

1. **Mondial Relay** : si aucun module gratuit n'est compatible, quelle solution de repli — point relais par email après commande, champ libre dans l'adresse, ou on renonce à ce transporteur pour la v1 ?
2. **Statut fiscal de Céline** : franchise en base (prix TTC = HT, pas de TVA sur les factures) ou assujettissement ? Tout le paramétrage TVA en dépend.
3. **Délai d'expiration d'une commande en attente de virement** : combien de jours avant annulation automatique et relibération du stock ?

Et à cadrer avec Céline : paliers de poids et tarifs par transporteur, périmètre géographique de la remise en main propre, méthodes de paiement Mollie acceptées.

## Pièges connus sur ce projet

- **`bin/magento config:show` ne renvoie rien pour une valeur par défaut.** Il ne lit que `core_config_data`. Pour vérifier une valeur effective, passer par `ScopeConfigInterface` — sinon on croit avoir contrôlé alors qu'on n'a rien lu.
- **Une section de configuration peut planter sans que rien d'autre ne le montre.** L'identifiant d'un gabarit d'email doit être le chemin du champ avec des underscores (§22). Ouvrir chaque page d'administration touchée par le lot, systématiquement.
- **Après avoir ajouté un plugin, `setup:upgrade --keep-generated` ne suffit pas** : il faut `bin/magento setup:di:compile`, sinon le plugin est ignoré silencieusement.
- **La feuille compilée est servie sous une URL versionnée figée** : comparer la taille du fichier servi et celle de `web/css/styles.css` avant de conclure à un bug de CSS.
- **Ne jamais déplacer un `.phtml` du thème pour « tester sans la surcharge »** : Magento mémorise la résolution.
- Sur un `<dialog>` piloté par `x-htmldialog`, pas d'attributs `x-transition` : animer en CSS.
- Pour tester un état vide ou une suppression : l'admin, jamais un script destructif.
- Retirer un bloc natif par un `.phtml` vide ne suffit pas quand son layout est chargé après : viser le nom du bloc.

## Méthode attendue

Présente d'abord un plan court et les trois décisions ci-dessus. Travaille ensuite par étapes, un commit atomique par étape, avec une liste de recette précise. **Termine le lot par le rituel du §25** de `documentation-theme.md` : recette écrans et administration, documentation complète dont le mémo Céline, fusion en avance rapide dans `main`, push, puis prompt de reprise du lot 6b.

Commence par l'audit Git, la lecture de l'état de configuration réel, et le spike Mondial Relay.
