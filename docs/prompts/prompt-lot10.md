# Reprise du développement — lot 10 Assistant IA des fiches produits

Tu reprends **Madame Aiguille**, boutique Magento Open Source 2.4.9 avec Hyvä 1.5.2, **en ligne et ouverte aux ventes**
sur `https://madame-aiguille.fr`. Réponds en français à Pierre, développeur Laravel/Vue/Tailwind et ancien responsable
technique Magento 2. Céline gère la boutique depuis l'administration, sans toucher au code.

Le développement par lots s'est clos avec le lot 8 (02/11/2026) ; le lot 9 (mesure d'audience Matomo) a été ouvert et
livré ensuite. Le lot 10 est un **lot ponctuel** : un **assistant IA dans la fiche produit de l'administration**, qui
questionne Céline puis rédige ses descriptions, ses champs de référencement et, en option, harmonise ses photos. Tu
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
jamais les modifier ni les versionner.

## Décisions acquises

- **Assistant dans l'administration seulement**, sur la page de création et d'édition d'un produit. La vitrine ne
  dépend jamais de l'IA.
- **Parcours** : *Analyser* (le modèle lit la fiche et les photos, puis pose les questions qui manquent) → *Répondre* →
  *Générer* → aperçu → *Insérer* dans les champs cochés. **Rien n'est enregistré sans le clic de Céline sur
  *Enregistrer*.**
- **Sorties** : description courte, description longue en HTML, titre et description méta, clé d'URL pour un produit
  neuf, textes alternatifs des photos, propositions pour `composition`, `dimensions` et `entretien` vides, et points à
  confirmer.
- **Texte : API Claude, SDK PHP officiel**, appel **côté serveur** depuis un contrôleur d'administration protégé par
  ACL. Modèle par défaut **`claude-opus-5-5`**, réglable. Sorties structurées en JSON imposé.
- **Clés chiffrées en configuration** (modèle `Encrypted`), saisies par Pierre. Jamais dans le dépôt ni dans le chat.
- **HTML limité** (`h3`, `p`, `ul`, `li`, `strong`, `em`, sans attribut), **nettoyé côté serveur** par liste blanche,
  stylé par la classe `prose` du thème.
- **Ne rien inventer** : ce que les réponses et la fiche ne disent pas part dans les points à confirmer.
- **Photos : fidélité d'abord.** La méthode principale détoure et pose sur un fond de la charte, sans jamais redessiner
  le produit. Le génératif n'est possible que pour une photo d'ambiance secondaire, et seulement si Pierre le retient.
- Données envoyées : le produit et les réponses de Céline, **jamais de donnée client**.

## Informations à demander au démarrage

Regroupe en une seule question ce qui manque :

1. **Page Builder sur la description** : le désactiver sur l'attribut et migrer les quatre descriptions
   (recommandé), ou le garder ?
2. **Modèle** : Opus 5.5 (≈ 0,15 $ par fiche, recommandé) ou un modèle moins cher (Sonnet 5.5, Haiku 5.5) ?
3. **Partie photos** : dans ce lot ou plus tard ? Si oui :
   - prestataire de détourage : API PhotoRoom ou `rembg` installé sur le VPS par script ;
   - fond : `brand-ivory` `#FAF0EB` ou `brand-paper` `#FFFCFA`, avec ou sans ombre ;
   - mises en situation génératives (OpenAI) : oui ou non ?
4. **Comptes** : le compte Anthropic (et PhotoRoom ou OpenAI) est-il créé, crédité, avec une limite de dépenses ? À quel
   nom sont les factures ?
5. **Charte** : quelles fiches existantes Céline veut-elle donner comme modèles ?
6. **Plafond d'appels par jour** souhaité (proposition : 50).

## Périmètre du lot 10

Le détail est dans le plan, section « Lot 10 ». Ordre conseillé :

1. **Vérifier la faisabilité** :
   - version stable du SDK PHP d'Anthropic, PHP pris en charge et dépendances. Teste d'abord
     `composer require anthropic-ai/sdk --dry-run`. **Annonce la commande à Pierre**, et demande avant toute
     installation réelle ;
   - désactivation de Page Builder sur un seul attribut en 2.4.9 ;
   - mécanisme d'insertion dans les champs du formulaire produit (composants UI, WYSIWYG classique).

   Note les sources et les dates.
2. **Squelette du module `MadameAiguille_AiAssistant`** :
   - `system.xml`, `config.xml` (tout vide par défaut), `acl.xml` avec deux ressources (texte, photos) ;
   - un service client fin autour du SDK, qu'on peut doubler en test ;
   - un journal minimal (date, utilisateur, modèle, jetons, durée, **sans contenu**) et un plafond d'appels par jour ;
   - `env:check --serveur` : erreur si l'assistant est activé sans clé.
3. **Page Builder et migration**, si Pierre le retient : un data patch idempotent qui coupe Page Builder sur
   `description` et désenveloppe les descriptions « Code HTML ». Il ne touche à rien d'autre et journalise ce qu'il a
   fait. Rendu en vitrine comparé avant et après.
4. **Rédaction guidée** :
   - un *modifier* de `product_form` qui ajoute le bouton et le panneau dans *Contenu*, visible seulement si l'assistant
     est configuré et que l'ACL le permet ;
   - deux actions de contrôleur, `analyser` et `generer` (retouches comprises), avec clé de formulaire et ACL ;
   - la consigne permanente et la charte dans le module (fichier versionné, mis en cache côté API), les schémas JSON
     des deux réponses ;
   - les photos de la galerie réduites côté serveur avant envoi ;
   - le nettoyeur HTML en liste blanche ;
   - le panneau : questions, réponses, attente lisible, aperçu, cases par champ, *Insérer*, retouches, erreurs claires.
5. **Référencement sans IA** : vérifier le rendu Open Graph de la fiche en Hyvä, puis ajouter le JSON-LD `Product` dans le
   thème enfant, sans rien de propre au visiteur. Valider avec le test des résultats enrichis de Google. Relancer
   Lighthouse sur la fiche.
6. **Photos**, si Pierre les retient :
   - **essai d'abord**, sur trois photos de `docs/photos-produits/`, montré à Pierre avant tout développement ;
   - ensuite, le bouton « Harmoniser » de la galerie : aperçu avant et après, puis remplacer ou ajouter, l'original
     restant dans la galerie ;
   - si l'essai ne convainc pas, la partie est consignée en limite et reportée.
7. **Déploiement** par le workflow GitHub, puis saisie des réglages sur le serveur par Pierre (clés) et par toi
   (`config:set`, annoncé), sans valeur du serveur dans les fichiers versionnés. Si les délais PHP-FPM ou nginx sont trop
   courts pour une génération, livre un **script relu** que Pierre lance.
8. **Recette en production** :
   - sur une **copie de fiche désactivée** puis sur un produit neuf, avec le compte de Céline (rôle « Atelier ») et un
     rôle sans la ressource ;
   - fiche vérifiée en vitrine à 1440 et 390 px ;
   - tous les cas d'erreur ;
   - `env:check --serveur` à 0 erreur.

   Le produit de test est désactivé ou supprimé dans l'administration à la fin.

## Règles et pièges à conserver

- **Annoncer chaque commande `composer` ou `bin/magento` avant exécution**, en local comme sur le serveur. Aucune
  extension Magento tierce sans accord explicite. Le SDK d'Anthropic est une bibliothèque PHP, pas une extension, mais
  son installation passe par l'accord de Pierre.
- **Tu ne crées pas de compte et ne saisis pas de clé** sur Anthropic, OpenAI ou PhotoRoom : Pierre le fait. En local,
  sans clé, tu travailles avec le service client doublé, et tu le dis.
- **Coût** : chaque appel réel coûte. Pendant le développement, limite les appels réels à ce qui est nécessaire et
  annonce-les.
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
- la validation de la charte de rédaction et des premiers textes par Céline ;
- l'ajout des ressources ACL au rôle « Atelier » ;
- le lancement d'un éventuel script serveur.

Sans elles, prépare et documente, mais ne déclare pas l'assistant en service.

## Fin du lot

Déroule **tout le §25** :

- **Recette** : écrans **et** administration, tests, PHPCS.
- **Documentation** :
  - une nouvelle section **§31 « Assistant IA des fiches produits (lot 10) »** dans `documentation-theme.md`, avec les
    chemins réels, la charte, les schémas et les coûts mesurés ;
  - le mémo Céline §24, le journal §17 et `plan-de-developpement.md` (v3.7, lot 10 marqué livré) ;
  - une page illustrée « Rédiger une fiche avec l'assistant » dans `guide-back-office.md`, avec ce que Céline doit
    toujours relire ;
  - `commandes-et-deploiement.md`, et `deploy/serveur/README.md` si un script serveur est livré.
- **Guide d'exploitation** :
  - les comptes d'API au §2 ;
  - le suivi de la consommation dans la surveillance mensuelle ;
  - le crédit et la rotation des clés dans les renouvellements ;
  - les mises à jour du SDK et le changement de modèle au §8 ;
  - les incidents (crédit épuisé, API injoignable, modèle retiré) ;
  - le retrait de la ligne « lot 10 » du §10 ;
  - un passage en v1.2.
- **Publication** : publier le site de documentation, pousser la branche, ouvrir la pull request.

Ce qui ne peut pas être tenu est dit et consigné comme limite, jamais sauté en silence.

Commence par un plan court et l'audit vérifiable, sans redemander les décisions acquises.
