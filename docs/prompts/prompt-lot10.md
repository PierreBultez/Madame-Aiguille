# Reprise du développement — lot 10 Assistant IA des fiches produits

Tu reprends **Madame Aiguille**, boutique Magento Open Source 2.4.9 avec Hyvä 1.5.2, **en ligne et ouverte aux ventes**
sur `https://madame-aiguille.fr`. Réponds en français à Pierre, développeur Laravel/Vue/Tailwind et ancien responsable
technique Magento 2. Céline gère la boutique depuis l'administration, sans toucher au code.

Le développement par lots s'est clos avec le lot 8 (02/11/2026) ; le lot 9 (mesure d'audience Matomo) a été ouvert et
livré ensuite. Le lot 10 est un **lot ponctuel** : un **assistant IA dans la fiche produit de l'administration**, qui
questionne Céline puis rédige ses descriptions et ses champs de référencement, et qui met en scène ses photos
smartphone dans l'ambiance Madame Aiguille. Tu
travailles **sur une boutique en production** : rien de ce lot ne doit pouvoir casser la vente, et l'assistant ne vit
que dans l'administration.

## État de départ à vérifier

- Dépôt : `/home/pierre/Documents/aiguille`, application dans `shop/`, documentation dans `docs/`, déploiement dans
  `deploy/` et `.github/workflows/deploiement.yml`, site de documentation dans `docs-site/`.
- **Le dépôt GitHub est public.** Aucun fichier versionné ne doit contenir :
  - de secret, de clé d'API ni de mot de passe ;
  - le chemin d'administration ;
  - l'identifiant HTTP ;
  - l'IP ou le port SSH ;
  - le téléphone de Céline.
- `main` contient le lot 9 (PR #18 et #19), puis le commit qui ouvre le lot 10 : plan v3.6 et ce prompt. Vérifie les
  références distantes plutôt que de supposer un HEAD.
- Référence technique en fin de lot 9 :
  - **142 tests unitaires** ;
  - PHPCS Magento2 sans erreur sur les fichiers du lot ;
  - `madameaiguille:env:check --serveur` à **0 erreur, 0 avertissement** ;
  - Lighthouse de **98 à 100** sur accueil, catégorie et fiche (`docs/audits/lighthouse.json`).
- Relevé du 06/11/2026 :
  - attribut `description` avec **Page Builder actif** ; les quatre produits en ligne ont une description dans un seul
    élément « Code HTML » (`data-content-type="html"`, HTML échappé) ;
  - `short_description` en WYSIWYG classique ; `composition`, `dimensions`, `entretien` en zones de texte ;
  - champs méta vides ; pas de JSON-LD `Product` dans le thème ;
  - aucun SDK d'IA dans `composer.json` ; PHP 8.5 en local et sur le serveur.
- Alias SSH : `madame-aiguille`. Sur le serveur, `mage` désigne
  `sudo -u madame-aiguille php /var/www/madame-aiguille/current/bin/magento`.

Commence par `git status`, `git log --oneline --decorate -n 15`, les références distantes, puis lis :

1. `AGENTS.md` et `docs/documentation-theme.md` **§25**, le rituel de fin de lot ;
2. `docs/plan-de-developpement.md`, section **« Lot 10 — Assistant IA des fiches produits »**, qui est le cahier des
   charges ;
3. `docs/guide-exploitation.md` (tout : c'est lui que le lot complète à la fin) ;
4. `docs/documentation-theme.md` :
   - §8 à §10 (attributs, set « Création », photos 4:5, fiche produit et ses onglets) ;
   - §24 (mémo Céline) ;
   - §29 (serveur, déploiement, `env:check`, pièges) ;
   - §30 (le module du lot 9, modèle le plus récent de module, de configuration et d'ACL) ;
5. `docs/guide-back-office.md` §2 (comment Céline crée une fiche aujourd'hui) ;
6. `docs/cahier-des-charges-docs-developpement/guide-bonnes-pratiques-hyva.md` ;
7. comme exemples de code :
   - `shop/app/code/MadameAiguille/Analytics/` (configuration, ACL, menu d'administration, tests) ;
   - `shop/app/code/MadameAiguille/Theme/Setup/Patch/Data/UseWebpHomeImages.php` (modification idempotente d'un contenu
     existant) ;
   - la commande `env:check` (`Theme/Console/Command/CheckEnvironment.php`) ;
   - `Magento_Catalog/templates/product/view/details.phtml` du thème enfant (rendu `prose` de la description).
8. Pour l'API Claude : utilise la documentation officielle du **SDK PHP** `anthropic-ai/sdk` (sorties structurées,
   images, mise en cache du prompt, erreurs). N'invente aucune signature : vérifie-la dans la documentation ou le code
   du SDK.

Travaille sur la branche **`lot-10-assistant-ia`**. Elle existe déjà sur GitHub avec le commit d'ouverture : reprends-la,
ou recrée-la depuis `main` à jour si elle a été fusionnée entre-temps. Préserve les travaux existants. Le dossier
`docs/photos-produits/`, non suivi, appartient à Pierre : tu peux **lire** ses photos pour l'essai de la partie photos,
jamais les modifier ni les versionner. Les photos de référence de l'ambiance sont fournies par Pierre.

## Décisions acquises

Décisions de Pierre du 06/11/2026, à ne pas rouvrir :

- **Assistant dans l'administration seulement**, sur la page de création et d'édition d'un produit. La vitrine ne
  dépend jamais de l'IA.
- **Page Builder retiré** de l'attribut `description`, et **descriptions actuelles vidées** : on repart de zéro, sans
  migration.
- **Parcours texte** : *Analyser* (le modèle lit la fiche et les photos, puis pose les questions qui manquent) →
  *Répondre* → *Générer* → aperçu → *Insérer* dans les champs cochés. **Rien n'est enregistré sans le clic de Céline
  sur *Enregistrer*.**
- **Sorties texte** : description courte, description longue en HTML, titre et description méta, clé d'URL pour un
  produit neuf, textes alternatifs des photos, propositions pour `composition`, `dimensions` et `entretien` vides, et
  points à confirmer.
- **Texte : Claude Haiku 5.5** (`claude-haiku-5-5`), par le **SDK PHP officiel**, **fixé dans le code**. Aucun choix de
  modèle en configuration, pour éviter les surprises de facturation. Sorties structurées en JSON imposé.
- **Ton** : Céline écrit **en son nom**, « je vous propose », jamais « nous » ; lecteur vouvoyé. Descriptions longues
  bien mises en page et assez développées, mots clés travaillés **sans répétition ni contenu dupliqué**. **Ne rien
  inventer** : ce que les réponses et la fiche ne disent pas part dans les points à confirmer.
- **HTML limité** (`h3`, `p`, `ul`, `li`, `strong`, `em`, sans attribut), **nettoyé côté serveur** par liste blanche,
  stylé par la classe `prose` du thème.
- **Photos : API Images d'OpenAI** (édition, GPT Image courant, fixé dans le code). Céline l'utilise déjà et sa
  fidélité aux produits la satisfait. Elle dépose ses **photos smartphone** ; le modèle les **met en scène** dans
  l'ambiance définie par des **photos de référence** et une consigne de style : fond beige rosé uni, lumière douce,
  ombre légère, épis de blé séchés en accessoire, côté zen, bohème, nature. Les propositions s'**ajoutent** à la
  galerie, en 4:5, après le choix de Céline ; rien n'est remplacé automatiquement.
- **Clés chiffrées en configuration** (modèle `Encrypted`), saisies par Pierre. Jamais dans le dépôt ni dans le chat.
- Données envoyées : le produit et les réponses de Céline, **jamais de donnée client**.

## Informations à demander au démarrage

Regroupe en une seule question ce qui manque :

1. **Comptes** : les comptes API Anthropic et OpenAI sont-ils créés, crédités, avec une limite de dépenses ? Les clés
   sont-elles saisies ?
2. **Photos de référence** de l'ambiance : où Pierre les a-t-il déposées ?
3. **Signature « Madame Aiguille »** sur les photos mises en scène, apposée par le serveur depuis le logo : oui ou non,
   et dans quel coin ?
4. **Plafonds d'appels par jour** (proposition : 50 pour le texte, 20 pour les images).
5. Deux ou trois **créations à photographier au smartphone** pour la recette.

## Périmètre du lot 10

Le détail est dans le plan, section « Lot 10 ». Ordre conseillé :

1. **Vérifier la faisabilité** :
   - version stable du SDK PHP d'Anthropic, PHP pris en charge et dépendances. Teste d'abord
     `composer require anthropic-ai/sdk --dry-run`. **Annonce la commande à Pierre**, et demande avant toute
     installation réelle ;
   - Haiku 5.5 : images en entrée et sorties structurées ;
   - API Images d'OpenAI : modèle courant, point d'accès d'édition, nombre d'images d'entrée, tailles de sortie,
     définition maximale, délais, tarifs ; SDK PHP officiel ou appel HTTP direct ;
   - retrait de Page Builder sur un seul attribut en 2.4.9 ;
   - mécanisme d'insertion dans les champs du formulaire produit (composants UI, WYSIWYG classique) et ajout d'une
     image à la galerie depuis un panneau.

   Note les sources et les dates.
2. **Squelette du module `MadameAiguille_AiAssistant`** :
   - `system.xml`, `config.xml` (tout vide par défaut), `acl.xml` avec deux ressources (textes, photos) ;
   - les modèles en **constantes du code**, jamais en configuration ;
   - deux services clients fins (Anthropic, OpenAI), qu'on peut doubler en test ;
   - un journal minimal (date, utilisateur, modèle, jetons ou images, durée, **sans contenu**) et des plafonds d'appels
     par jour ;
   - `env:check --serveur` : erreur si une partie est activée sans clé.
3. **Page Builder retiré** : un data patch qui coupe Page Builder sur `description` et vide les descriptions de tous
   les produits, sur toutes les vues, en journalisant les SKU. Il ne touche à aucun autre attribut ni aux blocs CMS.
4. **Rédaction guidée** :
   - un *modifier* de `product_form` qui ajoute le bouton et le panneau dans *Contenu*, visible seulement si la partie
     est configurée et que l'ACL le permet ;
   - deux actions de contrôleur, `analyser` et `generer` (retouches comprises), avec clé de formulaire et ACL ;
   - la consigne permanente et la charte dans le module (fichier versionné, mis en cache côté API), les schémas JSON
     des deux réponses ;
   - **un exemple de référence** rédigé à partir de `docs/jeux-de-donnees/catalogue-celine.php`, montré à Pierre pour
     validation par Céline avant d'entrer dans la consigne ;
   - les photos de la galerie réduites côté serveur avant envoi ;
   - le nettoyeur HTML en liste blanche ;
   - le panneau : questions, réponses, attente lisible, aperçu, cases par champ, *Insérer*, retouches, erreurs claires.
5. **Référencement sans IA** : vérifier le rendu Open Graph de la fiche en Hyvä, puis ajouter le JSON-LD `Product` dans le
   thème enfant, sans rien de propre au visiteur. Valider avec le test des résultats enrichis de Google. Relancer
   Lighthouse sur la fiche.
6. **Photos mises en scène** :
   - **essai d'abord**, avant l'interface : deux ou trois photos smartphone mises en scène avec les photos de référence
     et la consigne de style, montrées à Pierre avec l'original à côté. On ajuste la consigne jusqu'à son accord.
     Annonce le coût de l'essai avant de le lancer ;
   - la consigne de style et la règle de fidélité, versionnées dans le module ;
   - les photos de référence réglables dans l'administration, avec un jeu initial livré par le module ;
   - le bouton « Mettre en scène » de la fiche : dépôt de photos ou choix dans la galerie, précision libre,
     propositions côte à côte avec l'original, choix de Céline, **ajout** à la galerie en 4:5 ;
   - la signature apposée par le serveur, si Pierre la retient ;
   - le délai de réponse : attente lisible, et file d'attente si une requête dépasse les délais PHP et nginx.
7. **Déploiement** par le workflow GitHub, puis saisie des réglages sur le serveur par Pierre (clés) et par toi
   (`config:set`, annoncé), sans valeur du serveur dans les fichiers versionnés. Si les délais PHP-FPM ou nginx sont trop
   courts pour une génération, livre un **script relu** que Pierre lance.
8. **Recette en production** :
   - sur une **copie de fiche désactivée** puis sur un produit neuf, avec le compte de Céline (rôle « Atelier ») et un
     rôle sans les ressources ;
   - trois créations mises en scène à partir de photos smartphone, comparées à l'original ;
   - fiche vérifiée en vitrine à 1440 et 390 px ;
   - tous les cas d'erreur ;
   - `env:check --serveur` à 0 erreur.

   Le produit de test est désactivé ou supprimé dans l'administration à la fin.

## Règles et pièges à conserver

- **Annoncer chaque commande `composer` ou `bin/magento` avant exécution**, en local comme sur le serveur. Aucune
  extension Magento tierce sans accord explicite. Le SDK d'Anthropic est une bibliothèque PHP, pas une extension, mais
  son installation passe par l'accord de Pierre.
- **Tu ne crées pas de compte et ne saisis pas de clé** sur Anthropic ou OpenAI : Pierre le fait. En local,
  sans clé, tu travailles avec le service client doublé, et tu le dis.
- **Coût** : chaque appel réel coûte, surtout les images (environ 0,30 $ chacune). Pendant le développement, limite les
  appels réels à ce qui est nécessaire et annonce-les.
- **Fidélité des photos** : la photo vendue doit rester conforme à la pièce. Ne présente jamais une mise en scène comme
  validée sans comparaison avec l'original.
- **Ressources partagées du VPS** (nginx, PHP-FPM, paquets, cron système) : tu ne les modifies pas toi-même. Tu livres un
  **script relu** que Pierre lance. Les lectures et les commandes Magento de la boutique restent possibles.
- **Formulaire produit** : c'est un formulaire de composants UI (Knockout), pas du Hyvä. Le panneau suit les conventions
  de l'administration Magento. Une section d'administration peut planter seule : ouvre-la réellement, en édition **et**
  en création de produit, simple **et** configurable.
- **Rien de propre au visiteur dans le HTML de la vitrine** (JSON-LD compris) : Varnish et le cache de pages servent la
  même page à tous. Après un `config:set`, lancer `mage cache:flush`, qui vide aussi Varnish.
- `config:show` peut renvoyer du vide sur une valeur par défaut. Utilise `ScopeConfigInterface` ou `env:check`.
- Tailwind : une classe nouvelle n'existe qu'après `npm run build` dans `web/tailwind`. Ne jamais modifier
  `pub/static/`, le CSS compilé ni `vendor/`, et ne pas créer de `tailwind.config.js`.
- Site de documentation : les pages générées par `generer-pages.mjs` sont ignorées par git. Publier avec
  `docs-site/publier.sh` après la mise à jour des documents.
- Git : commits atomiques en français. **Pas de `push --force`** : corrige par un nouveau commit. Pierre fusionne dans
  `main` **par pull request GitHub**, avec commit de fusion.

## Ce qui dépend de Pierre et de Céline

- les comptes, les crédits, les limites de dépenses et la saisie des clés ;
- les décisions de la section « Informations à demander » ;
- les photos de référence de l'ambiance et les photos smartphone de recette ;
- la validation de la charte de rédaction, de l'exemple de référence et des premières mises en scène par Céline ;
- l'ajout des ressources ACL au rôle « Atelier » ;
- le lancement d'un éventuel script serveur.

Sans elles, prépare et documente, mais ne déclare pas l'assistant en service.

## Fin du lot

Déroule **tout le §25** :

- **Recette** : écrans **et** administration, tests, PHPCS.
- **Documentation** :
  - une nouvelle section **§31 « Assistant IA des fiches produits (lot 10) »** dans `documentation-theme.md`, avec les
    chemins réels, la charte, la consigne de style, les schémas et les coûts mesurés ;
  - le mémo Céline §24, le journal §17 et `plan-de-developpement.md` (v3.7, lot 10 marqué livré) ;
  - deux pages illustrées dans `guide-back-office.md`, « Rédiger une fiche avec l'assistant » et « Mettre en scène
    une photo », avec ce que Céline doit toujours relire et comparer ;
  - `commandes-et-deploiement.md`, et `deploy/serveur/README.md` si un script serveur est livré.
- **Guide d'exploitation** :
  - les comptes d'API au §2 ;
  - le suivi de la consommation dans la surveillance mensuelle ;
  - le crédit et la rotation des clés dans les renouvellements ;
  - les mises à jour du SDK et le changement de modèle (par commit, après comparatif) au §8 ;
  - les incidents (crédit épuisé, API injoignable, modèle retiré) ;
  - le retrait de la ligne « lot 10 » du §10 ;
  - un passage en v1.2.
- **Publication** : publier le site de documentation, pousser la branche, ouvrir la pull request.

Ce qui ne peut pas être tenu est dit et consigné comme limite, jamais sauté en silence.

Commence par un plan court et l'audit vérifiable, sans redemander les décisions acquises.
