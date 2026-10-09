# Reprise du développement — lot 5 Livraison et paiements

Tu reprends le développement de **Madame Aiguille**, une boutique Magento Open Source 2.4.9 avec Hyvä 1.5.2. Réponds en français et travaille avec Pierre, développeur Laravel/Vue/Tailwind et ancien responsable technique Magento 2.

## État de départ à vérifier

- Dépôt : `/home/pierre/Documents/aiguille` · Application Magento : `.../shop`
- Branche de référence : `main`, lot 7 fusionné et poussé
- **Périmètre arrêté par le call Céline du 11/09/2026** : lire `docs/brief-call-celine-2026-09-11.md` **en premier** — sa section « Résultat du call » fait foi, et ses « Questions restées ouvertes » listent ce qui n'est pas tranché
- Lots livrés : 1 Fondations, 2 Catalogue, 6a installation du checkout Luma fallback, 3 Accueil/CMS/Contact, 4 Panier, **7 Compte client, emails et statuts de commande**
- Ordre restant : **5 + 8a → 6b → 8** — la validation du compte Mollie impose un site en ligne, donc la mise en ligne d'une préproduction fait partie de ce lot

Commence par `git status`, `git log --oneline --decorate -n 20` et la lecture de :

1. `AGENTS.md` — règles de travail du dépôt et **rituel de fin de lot** ;
2. `docs/documentation-theme.md`, surtout §2, §8, §16, §20 (panier et franco), §21 et §22 (statuts et emails), **§23** (ce qui reste, avec l'état de configuration relevé le 11/09/2026) et §25 ;
3. `docs/plan-de-developpement.md`, section **Lot 5** et note de version **v2.4** ;
4. `docs/cahier-des-charges-docs-developpement/specification-fonctionnelle.md`, sections livraison, paiement et checkout ;
5. `docs/cahier-des-charges-docs-developpement/plan-de-tests.md`, §2 (poids) et §6 (livraison et paiement) ;
6. `docs/documentation-theme.md` §14 pour le fonctionnement du checkout Luma fallback.

Crée la branche `codex/lot-5-livraison-paiements` depuis `main` à jour, avant toute modification.

## Ce qui est déjà tranché

- **Mollie, en carte bancaire uniquement.** `mollie/magento2` 3.1.3, `mollie/magento2-hyva-compatibility` et le bundle de thème Hyvä sont **déjà installés et activés**. Le compte marchand **est créé**. Les 38 méthodes exposées par défaut sont à restreindre à la CB. **Pas de virement, pas de PayPal, pas de paiement fractionné.**
- **Mollie ne valide le compte qu'avec un site en ligne et un paiement réel effectué par nous-mêmes.** C'est une dépendance dure, pas une formalité de fin de projet.
- **Mondial Relay point relais, transporteur unique.** Colissimo et Chronopost sont écartés. Une seule grille → **le carrier `tablerate` natif suffit**, pas de carriers maison.
- **Click & collect payé sur place** (TPE ou espèces), créneaux jeudi 9 h-18 h et vendredi 9 h-11 h 30.
- **Entreprise individuelle en franchise de TVA** : BULTEZ CELINE, nom commercial MADAME AIGUILLE, SIRET 940 760 911 00013, 35 Grande Rue 37800 Saint-Épain, non inscrite au RCS. Prix TTC = prix encaissés, **aucune TVA facturée nulle part, ventes européennes comprises**, mention « TVA non applicable, art. 293 B du CGI ».
- **Zone de vente** : France plus les pays desservis par Mondial Relay — BE, LU, NL, DE, AT, IT, ES, PT à la grille actuelle, **à confirmer sur l'offre pro**. Pas de vente hors de cette liste.
- **Franco à 60 €** sur le point relais. Le seuil actuel est à 49 € : le porter à 60 € dans `madameaiguille/cart/free_shipping_threshold` **et** dans `carriers/freeshipping/free_shipping_subtotal`, les deux doivent rester d'accord.
- **Annulation d'un retrait non honoré : au rendez-vous manqué**, pas après N jours. Donc **aucun cron à écrire** — Céline annule la commande dans l'administration, ce qui relibère le stock. La procédure va dans le mémo Céline (§24).
- **Checkout : Luma Fallback** (`hyva-themes/magento2-luma-checkout` 1.1.7). Toute la page du tunnel bascule sur Luma ; son habillage est le **lot 6b**, pas celui-ci.
- Commande invité autorisée (`checkout/options/guest_checkout = 1`).
- Franco de port **affiché à 49 €**, configurable dans *Madame Aiguille › Panier*.
- Poids des produits en **kilogrammes**, attribut obligatoire depuis le lot 2.
- Les six statuts de commande, la frise de suivi et les emails « paiement reçu » / « prête pour retrait » existent déjà (lot 7). **Ce lot doit les faire vivre pour de vrai, pas les réécrire.** Les créneaux de retrait donnent enfin son contenu à l'email « Prête pour retrait ».

## État de la configuration — relevé le 11/09/2026, inchangé au 09/10/2026

Valeurs **effectives** (défauts de `config.xml` compris ; `bin/magento config:show` ne montre que ce qui est enregistré en base, il renvoie du vide sur un défaut — ne pas s'y fier seul).

| Chemin | Valeur | Commentaire |
|---|---|---|
| `general/country/default` | `FR` | corrigé |
| `general/locale/weight_unit` | `kgs` | conforme |
| `shipping/origin/country_id` | **`US`** | **à corriger** → FR, 35 Grande Rue, 37800 Saint-Épain (code postal actuel : `90034`) |
| `tax/defaults/country` | **`US`** | **à corriger** |
| `tax/calculation/price_includes_tax` | `0` | franchise en base confirmée → prix TTC = prix encaissés |
| `carriers/flatrate/active` | `1`, `type = I`, `5.00` | provisoire, **5 € par article** |
| `carriers/freeshipping/active` | `1`, seuil `49` | correspond bien à la barre affichée |
| `carriers/tablerate/active` | `0`, condition `package_weight` | à activer et alimenter — **Mondial Relay seul, une seule grille suffit** |
| `payment/mollie_general/enabled` | `0`, mode `test` | compte marchand **créé**, à activer avec les clés de test |
| `payment/checkmo/active` | `1` | chèque, **hors périmètre : à désactiver** |
| `payment/free/active` | `1` | à conserver pour les commandes à 0 € |
| Méthodes Mollie exposées | **38** | à restreindre à la **carte bancaire seule** |

## Règles non négociables

Celles d'`AGENTS.md`, plus :

- **Ne pas installer d'extension tierce sans accord explicite de Pierre**, et jamais sans avoir annoncé la commande `composer`.
- **Aucune clé API, aucun identifiant, aucun RIB dans le dépôt.** Les secrets se saisissent dans l'administration ou par `bin/magento config:set --lock-env`. Le RIB ne doit jamais apparaître sur une page indexable.
- La grille tarifaire est **versionnée en CSV** dans `docs/` ou dans le module, avec un script d'import rejouable en préproduction et en production.
- Ne pas commencer l'habillage du tunnel : c'est le lot 6b.

## Périmètre du lot 5

1. **Spike Mondial Relay — en tout premier, 1 à 2 jours maximum.** Identifier les modules de sélection de point relais, vérifier la compatibilité avec le **checkout natif Magento (Knockout)** — c'est lui qui tourne dans le fallback Luma —, tester en sandbox. Candidats relevés : module officiel Magentix (payant), O'Pickup d'Owebia (200 €), intégration maison du widget officiel. Tous demandent les identifiants **Offre Start** de Céline (compte à ouvrir ou transférer : le sien n'est pas pro). Repli assumé si rien ne convient : point relais choisi par email après commande. **Ne pas engager le reste du lot avant cette décision.**

2. **Grille de frais de port au poids** : carrier **`tablerate` natif**, `Weight vs. Destination`, sa condition est déjà `package_weight`. Grille versionnée en CSV avec un script d'import rejouable en préproduction et en production. Couper `carriers/flatrate`.

3. **Click & collect** : méthode à 0 €, restreinte géographiquement si Céline le souhaite, **payée sur place** → prévoir un mode de paiement hors ligne réservé à ce mode de livraison. Afficher les créneaux (jeudi 9 h-18 h, vendredi 9 h-11 h 30).

4. **Mollie** : clés de test, restriction à la carte bancaire, désactivation de `checkmo`. Clés de production une fois le compte validé.

5. **Cohérence avec le lot 7** : câbler les statuts sur les événements réels — paiement capté → « Paiement reçu », expédition → « Expédiée ». Vérifier que les deux notifications partent. **L'email de virement esquissé au lot 7 n'a plus d'objet : le retirer ou le neutraliser.**

6. **TVA et mentions** : `tax/defaults/country` → FR, `shipping/origin` → Saint-Épain 37800 FR, prix TTC = HT, mention « TVA non applicable, art. 293 B du CGI » sur les factures et en pied de site.

7. **Contrôle « poids renseigné »** : commande CLI `madameaiguille:catalog:check-weight` ou requête SQL documentée, listant les produits publiés sans poids.

8. **Aligner l'affichage sur le réel** : `product_reassurance`, `cart_reassurance`, le footer, la page *Livraison et retours* et la mention sous le prix ne doivent plus annoncer que ce qui existe.

9. **Réseaux sociaux** : retirer Pinterest, ajouter **TikTok** (champ de configuration, icône, pied de page).

## 8a — Mise en ligne anticipée, dans ce lot

Domaine, DNS, préproduction HTTPS, SMTP. Puis **un paiement réel** pour faire valider le compte Mollie. Si la préproduction est protégée par une authentification HTTP, **exclure la route du webhook Mollie** — sinon les paiements ne remontent jamais et le diagnostic est pénible.

## Ce qui ne pourra pas être bouclé dans ce lot

- L'**habillage du tunnel** : lot 6b.
- Les **clés de production** Mollie tant que le compte n'est pas validé — ce qui dépend de 8a.
- Les **grilles européennes par pays** tant que Céline n'a pas transmis ses tarifs pro Mondial Relay.
- **Emballage cadeau et carte personnalisée** : nouveau besoin, à spécifier. Le message cadeau natif de Magento est déjà affiché dans le détail de commande depuis le lot 7.

## Décisions à demander à Pierre avant de coder

Quatre des dix questions du compte rendu ont été tranchées le 09/10/2026 et figurent ci-dessus. Restent, par ordre d'impact sur le code :

1. **Prise de rendez-vous du retrait** : la cliente choisit-elle son créneau à la commande (sélecteur dans le tunnel, du développement) ou Céline confirme-t-elle l'heure ensuite par email (faisable avec l'existant) ? **C'est devenu structurant** : l'annulation est adossée au rendez-vous manqué, donc une commande dont le rendez-vous n'est jamais pris bloque le stock sans fin. Prévoir un garde-fou dans les deux cas.
2. **Carte du lieu de retrait** : adresse et plan statique, ou carte interactive ?
3. **Langue du site** : français seul alors qu'on vend dans huit pays ?
4. **Délai d'expédition annoncé** : la réalité d'aujourd'hui (4-5 jours) ou la cible (48 h) ?
5. **Emballage cadeau et carte personnalisée** : option gratuite ou payante, et dans quel lot ?

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

Commence par l'audit Git, la lecture du compte rendu du call, la vérification de l'état de configuration réel, puis le spike Mondial Relay.
