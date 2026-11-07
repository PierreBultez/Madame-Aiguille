# Reprise du développement — lot 11 Corrections et contenus éditables

Tu reprends **Madame Aiguille**, boutique Magento Open Source 2.4.9 avec Hyvä 1.5.2, **en ligne et ouverte aux ventes**
sur `https://madame-aiguille.fr`. Réponds en français à Pierre, développeur Laravel/Vue/Tailwind et ancien responsable
technique Magento 2. Céline gère la boutique depuis l'administration, sans toucher au code.

Le développement par lots s'est clos avec le lot 8 (02/11/2026). Les lots 9 (Matomo) et 10 (assistant IA des fiches
produits) ont été ouverts et livrés ensuite. Le lot 11 traite **la liste de bugs et de questions relevés par Pierre**
le 07/11/2026 :
- **A** : sept corrections (tunnel, panier, catégorie, compte, recherche, accueil) ;
- **B** : refaire l'option cadeau en une seule fonction payante ;
- **C** : rendre éditables les contenus de l'accueil, après décision de Pierre.

Tu travailles **sur une boutique en production** : la partie B touche le tunnel et les totaux de commande, rien ne
doit pouvoir casser la vente.

## État de départ à vérifier

- Dépôt : `/home/pierre/Documents/aiguille`, application dans `shop/`, documentation dans `docs/`, déploiement dans
  `deploy/` et `.github/workflows/deploiement.yml`, site de documentation dans `docs-site/`.
- **Le dépôt GitHub est public.** Aucun fichier versionné ne doit contenir :
  - de secret, de clé d'API ni de mot de passe ;
  - le chemin d'administration ;
  - l'identifiant HTTP ;
  - l'IP ou le port SSH ;
  - le téléphone de Céline.
- `main` contient le lot 10 et ses suites (PR #20 à #25), puis le commit qui ouvre le lot 11 : plan v3.8 et ce prompt.
  Vérifie les références distantes plutôt que de supposer un HEAD.
- Référence technique en fin de lot 10 :
  - **213 tests unitaires** (`vendor/bin/phpunit -c dev/tests/unit/phpunit.xml.dist app/code/MadameAiguille`) ;
  - PHPCS Magento2 sans erreur sur les fichiers des lots ;
  - `madameaiguille:env:check --serveur` à **0 erreur, 0 avertissement** ;
  - Lighthouse de **98 à 100** sur accueil, catégorie et fiche (`docs/audits/lighthouse.json`), sauf les fiches aux
    photos PNG trop lourdes (limite du §31).
- Relevé du 07/11/2026, à confirmer :
  - `tax/defaults/region` = `219` (Indre-et-Loire), posé par `ConfigureSalesFoundations` ; la région n'est pas
    obligatoire en France (`general/region/state_required` ne contient pas `FR`) ;
  - le bloc « Récemment commandé » est la barre latérale de réassort de `Magento_Sales`, que le thème enfant ne
    surcharge pas ;
  - le formulaire de recherche et les options cadeau du panier sont les gabarits de Hyvä, non surchargés ;
  - `sales/gift_options/allow_order` = `1` : le message cadeau natif se saisit dans le panier.
- Le poste local tourne en **mode développeur avec DI compilée** : un plugin ou une préférence ne s'applique qu'après
  `setup:di:compile`. Un nouveau `requirejs-config.js` ne se recrée qu'après `setup:upgrade`.
- Alias SSH : `madame-aiguille`. Sur le serveur, `mage` désigne
  `sudo -u madame-aiguille php /var/www/madame-aiguille/current/bin/magento`.

Commence par `git status`, `git log --oneline --decorate -n 15`, les références distantes, puis lis :

1. `AGENTS.md` et `docs/documentation-theme.md` **§25**, le rituel de fin de lot ;
2. `docs/plan-de-developpement.md`, section **« Lot 11 — Corrections et contenus éditables »**, qui est le cahier
   des charges ;
3. `docs/guide-exploitation.md` (tout : le lot le complète à la fin) ;
4. `docs/documentation-theme.md` :
   - §7 (pages et blocs CMS, accueil) ;
   - §14, §26 et §27 (tunnel Luma fallback, module `MadameAiguille_Checkout`, emballage cadeau, habillage du tunnel) ;
   - §20 et §21 (panier, compte client) ;
   - §22 (emails, message cadeau dans la commande) ;
   - §24 (mémo Céline), §29 (serveur, déploiement, `env:check`) ;
   - §31 (assistant IA : modèle si C2 l'étend aux contenus CMS) ;
5. `docs/guide-back-office.md` (ce que Céline sait faire aujourd'hui) ;
6. `docs/cahier-des-charges-docs-developpement/guide-bonnes-pratiques-hyva.md` ;
7. comme exemples de code :
   - `shop/app/code/MadameAiguille/Checkout/` (total maison de l'emballage, composants Knockout du tunnel, tests) ;
   - `shop/app/design/frontend/MadameAiguille/checkout/` (thème du tunnel, LESS et mixins) ;
   - `shop/app/code/MadameAiguille/Theme/Setup/Patch/Data/CreateHomeBlocks.php` (blocs de l'accueil, jamais
     réécrits après leur création) ;
   - `shop/app/code/MadameAiguille/AiAssistant/` (si C2 étend l'assistant).

Travaille sur des branches courtes depuis `main` à jour, **une par partie** : `lot-11-corrections` (A),
`lot-11-option-cadeau` (B), puis une branche par point de C retenu. Chaque branche se termine par sa pull request,
pour déployer et revenir en arrière par morceaux. Le dossier `docs/photos-produits/`, non suivi, appartient à Pierre :
n'y touche pas, et ne fais jamais `git add docs` sans le vérifier.

## Décisions acquises

Décisions de Pierre du 07/11/2026, à ne pas rouvrir :

- **Option cadeau unifiée** : une seule option « Emballage et message cadeau », payante (2 € aujourd'hui, prix
  réglable). La cocher demande **De la part de**, **Pour** et le **message** ; Céline emballe et écrit le message à la
  main. Plus de message gratuit sans emballage, plus d'emballage sans message.
- **Lien Instagram de l'accueil** : ouverture dans un nouvel onglet.
- **Recherche** : apparition douce de la fenêtre.
- **Galerie produit** : un WebP envoyé est converti en JPEG (livré le 07/11/2026, `documentation-theme.md` §10).
  **Servir du WebP aux visiteurs est hors périmètre** : Pierre y réfléchit.
- **Assistant IA** : modèle, charte au « je » et exemple ADE-FU tels que livrés au lot 10 (§31).

## Informations à demander au démarrage

Regroupe en une seule question, chacune avec ta recommandation :

1. **A4, « Récemment commandé »** : restyler la barre latérale sur la page catégorie, ou la retirer (elle prend de la
   place à côté des filtres pour un intérêt faible sur une boutique de pièces uniques).
2. **A5, région** : la déduire du code postal pour la France (département = deux premiers chiffres, Corse 2A/2B,
   outre-mer exclu de la zone de vente), ou simplement ne plus la présélectionner. Rappelle que tu ne corriges pas
   les adresses existantes en base : tu en donnes la liste à Pierre.
3. **B, option cadeau** :
   - longueur maximale du message (proposition : 250 caractères, la taille d'une carte) ;
   - lieu de saisie : à l'étape Livraison du tunnel seulement (proposition), ou dès le panier ;
   - libellé exact et texte d'aide, à faire valider par Céline.
4. **C1 à C4** : présenter les options chiffrées du cahier des charges, puis attendre la décision de Pierre pour
   chacune. Pour C1, demander si le compte Instagram de Céline est un compte **professionnel** (créateur ou
   entreprise).

## Périmètre du lot 11

Le détail est dans le plan, section « Lot 11 ». Ordre conseillé :

1. **Audit reproductible** de chaque point de la partie A, en local et en production, à 1440 et 390 px, invité et
   client connecté : capture avant, cause trouvée dans le code (gabarit, LESS, layout, configuration). Note ce qui ne
   se reproduit pas.
2. **Partie A** (branche `lot-11-corrections`) :
   - surcharges dans le **thème enfant** (vitrine Hyvä) ou dans le **thème du tunnel** (Luma), jamais dans `vendor/` ;
   - A2 : le lien « Se connecter » du tunnel doit ouvrir la connexion et revenir au tunnel une fois connecté ;
   - A5 : corriger le formulaire d'adresse du compte **et** celui du tunnel, puis lister à Pierre les adresses
     existantes dont la région ne correspond pas au code postal, par une requête en lecture seule ;
   - A6 : transition Alpine, sans animation si `prefers-reduced-motion`, focus placé dans le champ à l'ouverture ;
   - A7 : vérifier aussi les liens sociaux du pied de page.
   Captures après correction, tests si de la logique est ajoutée, PR.
3. **Partie B** (branche `lot-11-option-cadeau`) :
   - concevoir avant de coder : où se saisit l'option, comment le message natif (`gift_message` : expéditeur,
     destinataire, message) est lié à l'emballage, ce que deviennent le bouton du panier et `allow_order` ;
     **présenter la conception à Pierre** avant de l'écrire ;
   - validation côté serveur : pas d'emballage facturé sans message complet, pas de message enregistré sans
     emballage ; un panier existant avec un ancien message gratuit ne doit pas bloquer la commande ;
   - commande, email, facture, avoir, PDF et administration affichent l'emballage **et** le message ;
   - tests unitaires (total, validation, report sur la commande) ;
   - recette locale complète, puis en production avec **Stripe en mode test**. Les commandes de test sont annulées
     dans l'administration.
4. **Partie C** : propositions chiffrées, décision de Pierre, puis une branche et une PR par point retenu. Pour C1,
   aucune requête du navigateur du visiteur vers Instagram, aucun cookie, images copiées sur le serveur, CSP inchangée.
   Le jeton éventuel est **saisi par Pierre** en configuration chiffrée, jamais dans le dépôt ni dans le chat.
5. **Déploiement** par le workflow GitHub après chaque PR fusionnée, puis vérification sur le serveur : version en
   ligne, `env:check --serveur`, parcours touchés à 1440 et 390 px.

## Règles et pièges à conserver

- **Annoncer chaque commande `composer` ou `bin/magento` avant exécution**, en local comme sur le serveur. Aucune
  extension Magento tierce sans accord explicite.
- **Deux thèmes** : la vitrine est en Hyvä (Tailwind, Alpine) dans `MadameAiguille/default` ; le tunnel est en
  **Luma fallback** (LESS, Knockout, RequireJS) dans `MadameAiguille/checkout`. Une correction du tunnel se fait dans
  le thème du tunnel.
- **Rien de propre au visiteur dans le HTML de la vitrine** : Varnish et le cache de pages servent la même page à
  tous. Ce qui dépend du client passe par les sections de données client (`customer-data`). Après un `config:set`,
  lancer `mage cache:flush`, qui vide aussi Varnish.
- **Blocs CMS** : `CreateHomeBlocks` ne réécrit jamais un bloc déjà créé ; Céline a pu les modifier. Toute évolution
  de contenu respecte ce qu'elle a saisi (patch idempotent qui ne remplace que le texte d'origine exact, comme
  `UseWebpHomeImages`).
- **Données en base** : pour tester un état ou corriger une donnée, **l'administration, jamais un script
  destructif**. Les adresses de A5 ne sont pas modifiées par toi.
- **Ressources partagées du VPS** (nginx, PHP-FPM, paquets, cron système) : tu ne les modifies pas toi-même. Tu livres
  un **script relu** que Pierre lance. Un cron Magento (`crontab.xml`) reste de ton ressort.
- Accessibilité : cibles de 44 px, focus visible, WCAG 2.1 AA ; tous les textes passent par `__()`, traduits dans
  `i18n/fr_FR.csv` du bon thème.
- Tailwind : une classe nouvelle n'existe qu'après `npm run build` dans `web/tailwind`. Ne jamais modifier
  `pub/static/`, le CSS compilé ni `vendor/`, et ne pas créer de `tailwind.config.js`.
- Site de documentation : les pages générées par `generer-pages.mjs` sont ignorées par git. Publier avec
  `docs-site/publier.sh` après la mise à jour des documents ; une nouvelle section de `documentation-theme.md` doit
  être rangée dans un groupe du générateur.
- Git : commits atomiques en français. **Pas de `push --force`** : corrige par un nouveau commit. Pierre fusionne dans
  `main` **par pull request GitHub**, avec commit de fusion, puis déploie.

## Ce qui dépend de Pierre et de Céline

- les décisions de la section « Informations à demander » ;
- la validation par Céline du libellé et du texte de l'option cadeau ;
- pour C1 : le compte Instagram professionnel, l'application Meta et le jeton, que Pierre crée et saisit ;
- la correction des adresses existantes dont la région est fausse ;
- la fusion et le déploiement de chaque PR.

Sans elles, prépare et documente, mais ne déclare pas la partie concernée livrée.

## Fin du lot

Déroule **tout le §25** :

- **Recette** : écrans **et** administration, tests, PHPCS, `env:check --serveur`.
- **Documentation** :
  - une nouvelle section **§32 « Corrections et contenus éditables (lot 11) »** dans `documentation-theme.md`, avec
    les chemins réels et les décisions ; mise à jour des sections touchées (§7, §20, §21, §26, §27…) ;
  - le mémo Céline §24, le journal §17 et `plan-de-developpement.md` (v3.9, lot 11 marqué livré) ;
  - `guide-back-office.md` : l'option cadeau telle que Céline la voit dans une commande, et chaque contenu de
    l'accueil devenu éditable, avec captures ;
  - `commandes-et-deploiement.md`, et `deploy/serveur/README.md` si un script serveur est livré.
- **Guide d'exploitation** : ce que le lot ajoute à surveiller ou à renouveler (jeton Instagram par exemple), les
  incidents possibles, le retrait de la ligne « lot 11 » du §10 s'il y en a une, et un passage en v1.3.
- **Publication** : publier le site de documentation, pousser la branche, ouvrir la pull request.

Ce qui ne peut pas être tenu est dit et consigné comme limite, jamais sauté en silence.

Commence par un plan court et l'audit vérifiable de la partie A, sans redemander les décisions acquises.
