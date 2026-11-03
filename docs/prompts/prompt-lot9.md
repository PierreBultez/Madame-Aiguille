# Reprise du développement — lot 9 Mesure d'audience (Matomo auto-hébergé)

Tu reprends **Madame Aiguille**, boutique Magento Open Source 2.4.9 avec Hyvä 1.5.2, **en ligne et ouverte aux ventes**
sur `https://madame-aiguille.fr`. Réponds en français à Pierre, développeur Laravel/Vue/Tailwind et ancien responsable
technique Magento 2. Céline gère la boutique depuis l'administration, sans toucher au code.

Le développement par lots s'est clos avec le lot 8 (02/11/2026). Le lot 9 est un **lot ponctuel** ouvert ensuite : ajouter
une mesure d'audience simple, **Matomo auto-hébergé**, sans bannière de consentement. Tu travailles **sur une boutique
en production** : rien de ce lot ne doit pouvoir casser la vente.

## État de départ à vérifier

- Dépôt : `/home/pierre/Documents/aiguille`, application dans `shop/`, documentation dans `docs/`, déploiement dans
  `deploy/` et `.github/workflows/deploiement.yml`, site de documentation dans `docs-site/`.
- **Le dépôt GitHub est public.** Aucun fichier versionné ne doit contenir :
  - de secret ni de mot de passe ;
  - le chemin d'administration ;
  - l'identifiant HTTP ;
  - l'IP ou le port SSH ;
  - le téléphone de Céline.
- `main` contient le lot 8 (fusion de la PR #16), puis le commit qui ouvre le lot 9 : plan v3.4 et ce prompt. Vérifie les
  références distantes plutôt que de supposer un HEAD.
- Référence technique en fin de lot 8 :
  - **129 tests unitaires** ;
  - PHPCS Magento2 sans erreur sur les fichiers du lot ;
  - `madameaiguille:env:check --serveur` à **0 erreur, 0 avertissement** ;
  - Lighthouse de **99 à 100** sur accueil, catégorie et fiche (`docs/audits/lighthouse.json`).
- Serveur, relevé du 03/11/2026 :
  - VPS OVH partagé avec d'autres sites de Pierre, sous Ubuntu 26.04 ;
  - 45 Go de RAM dont 31 disponibles, 12 cœurs, 248 Go de disque libres ;
  - PHP **8.4 et 8.5** installés, MariaDB 12.3.3, nginx, Varnish 7.7, Let's Encrypt.
- Alias SSH : `madame-aiguille`. Sur le serveur, `mage` désigne
  `sudo -u madame-aiguille php /var/www/madame-aiguille/current/bin/magento`.

Commence par `git status`, `git log --oneline --decorate -n 15`, les références distantes, puis lis :

1. `AGENTS.md` et `docs/documentation-theme.md` **§25**, le rituel de fin de lot ;
2. `docs/plan-de-developpement.md`, section **« Lot 9 — Mesure d'audience »**, qui est le cahier des charges ;
3. `docs/guide-exploitation.md` (tout : c'est lui que le lot complète à la fin) ;
4. `docs/documentation-theme.md` :
   - §6 (footer) ;
   - §14 et §27 (tunnel Luma, thème `MadameAiguille/checkout`) ;
   - §24 (mémo Céline) ;
   - §26 (CSP du widget Mondial Relay) ;
   - §29 (serveur, déploiement, `env:check`, pièges) ;
5. `docs/commandes-et-deploiement.md` et `deploy/serveur/README.md` : la façon d'écrire et de livrer un script serveur ;
   `96-guide-magento.sh` et `98-cache-guide.sh` sont les modèles les plus proches ;
6. `docs/cahier-des-charges-docs-developpement/guide-bonnes-pratiques-hyva.md` ;
7. comme exemples de code : `shop/app/code/MadameAiguille/Checkout/etc/csp_whitelist.xml`,
   `shop/app/code/MadameAiguille/Theme/Setup/Patch/Data/FillLegalPages.php` et `UseWebpHomeImages.php` (remplacement
   d'une chaîne exacte dans un contenu CMS), la commande `env:check` (`Theme/Console/Command/CheckEnvironment.php`).

Travaille sur la branche **`lot-9-mesure-audience`**. Elle existe déjà sur GitHub avec le commit d'ouverture :
reprends-la, ou recrée-la depuis `main` à jour si elle a été fusionnée entre-temps. Préserve les travaux existants. Le
dossier `docs/photos-produits/`, non suivi, appartient à Pierre : n'y touche pas.

## Décisions acquises

- **Outil : Matomo On-Premise, auto-hébergé, gratuit.** Ne pas rouvrir le choix : Pierre a écarté Google Analytics,
  Open Analytics, Plausible, Umami et GoatCounter.
- Besoin : **le trafic de base**, rien de plus :
  - visites, pages vues, pages d'entrée et de sortie ;
  - sources, appareils, pays ;
  - recherche interne, pages 404.
- **Pas de bannière de consentement** : Matomo en mode de **conformité CNIL**, sans cookie (`disableCookies`), IP
  anonymisée, sans User ID, sans fonction qui sortirait du cadre de l'exemption.
- **Adresse proposée : `stats.madame-aiguille.fr`.** Le même domaine que la boutique rend les requêtes « first-party »
  et la CSP simple. Le DNS est ajouté par Pierre chez OVH.
- **Installation native sur le VPS**, à côté de la boutique : utilisateur système, pool PHP-FPM, base et utilisateur
  MariaDB dédiés, hôte nginx et certificat propres. Pas de Docker.
- Côté Magento, un **module `MadameAiguille_Analytics`** :
  - réglages en administration, vides par défaut ;
  - un **script externe sans inline** pour la CSP ;
  - suivi de la vitrine Hyvä **et** du tunnel Luma.
- La boutique ne doit **jamais dépendre** de Matomo : si `stats.` ne répond pas, le site fonctionne à l'identique.

## Informations à demander au démarrage

Regroupe en une seule question ce qui manque :

1. L'enregistrement DNS A `stats.madame-aiguille.fr` existe-t-il ? Ou une autre adresse est-elle préférée ?
2. **Mesure du commerce électronique** (commandes, chiffre d'affaires, paniers) : oui ou non pour ce lot ? Elle n'est
   pas dans le « trafic de base ». Si oui, vérifier qu'elle reste dans l'exemption CNIL avant de l'activer.
3. Rapport mensuel par email à Céline : oui ou non ?
4. Durée de conservation souhaitée, dans la limite de la grille CNIL.
5. Lien « Statistiques » dans le menu de l'administration Magento : oui ou non ?

## Périmètre du lot 9

Le détail est dans le plan, section « Lot 9 ». Ordre conseillé :

1. **Vérifier la compatibilité.** Dernière version stable de Matomo et versions de PHP officiellement prises en
   charge. PHP 8.5 si c'est le cas, sinon le pool de Matomo tourne en **PHP 8.4**, déjà installé. Note la source et la
   date.
2. **Script serveur `deploy/serveur/99-matomo.sh`**, relu par Pierre puis lancé par lui, avec la même forme que
   `96-guide-magento.sh` :
   - le script vérifie d'abord le DNS ;
   - il télécharge le paquet officiel et vérifie son intégrité ;
   - il crée l'utilisateur système, le pool PHP-FPM et la base MariaDB, avec un mot de passe généré et rangé hors du
     dépôt, en `0600` ;
   - il pose le gabarit nginx `deploy/serveur/nginx/matomo.conf` et obtient le certificat avec `certbot --nginx` ;
   - il programme le cron `core:archive` ;
   - il est rejouable, sauvegarde ce qu'il remplace et s'arrête net en cas d'échec de `nginx -t`.

   Durcissement nginx, selon la documentation de Matomo :
   - seuls `index.php`, `matomo.php`, `piwik.php` et les ressources statiques sont servis ;
   - `config/`, `tmp/`, `lang/` et les fichiers `.ini` sont refusés.

   Ensuite, Pierre fait l'assistant web et crée son super-utilisateur.
3. **Configuration de Matomo**, faite par Pierre ou guidée pas à pas :
   - conformité CNIL en un clic ;
   - anonymisation de l'IP ;
   - base DB-IP Lite ;
   - archivage navigateur coupé ;
   - exclusions d'IP et des robots ;
   - compte de Céline en lecture seule ;
   - double authentification de Pierre ;
   - SMTP Brevo si le rapport par email est retenu.

   Remplis la **grille d'auto-évaluation CNIL** dans `docs/conformite/matomo-cnil.md`.
4. **Module `MadameAiguille_Analytics`** :
   - **Configuration** : `etc/adminhtml/system.xml`, `etc/config.xml` et `etc/acl.xml`, sous *Stores › Configuration*,
     section *Mesure d'audience* : activation, URL, identifiant du site.
   - **Rendu** : un ViewModel et un bloc dans `head.additional` pour la vitrine et le tunnel. Le bloc rend une seule
     balise `<script src="…/tracker.js" data-url data-site-id async>`, et `tracker.js` du module pose `_paq` puis charge
     `matomo.js`.
   - **Signaux de page** :
     - `disableCookies` ;
     - `trackPageView` ;
     - recherche interne (`trackSiteSearch` sur la page de résultats, avec le mot-clé et le nombre de résultats) ;
     - 404, avec un titre de page dédié.
   - **CSP** : `csp_whitelist.xml` (`script-src`, `connect-src`, `img-src`) pour l'hôte configuré.
   - **Opposition** : le module d'opposition de Matomo sur la page *Confidentialité*, sans script inline.
   - **Page Confidentialité** : un data patch qui remplace la phrase exacte entre crochets « [Compléter cette section si un
     outil de mesure d’audience…] » posée par `FillLegalPages`. Elle contient des apostrophes typographiques : copie-la
     depuis le patch. S'il ne la trouve pas, il ne fait rien et le journal le dit.
   - **`env:check --serveur`** : erreur si la mesure est activée sans URL ou sans identifiant ; si elle est désactivée,
     un avertissement seulement après la mise en service.
   - **Lien d'administration** si Pierre l'a retenu : entrée de menu et ressource ACL. L'ajout au rôle « Atelier » est
     fait par Pierre dans l'administration.
   - Tests unitaires du ViewModel, du data patch et du contrôle d'environnement.
5. **Déploiement** par le workflow GitHub, puis saisie des réglages sur le serveur :
   - avec `config:set`, annoncé à Pierre avant exécution ;
   - aucune valeur du serveur dans `config.php` ni dans `env.php` versionnés.
6. **Recette en production** :
   - visite visible dans le *Temps réel* de Matomo, depuis la vitrine et depuis le tunnel jusqu'à la page de succès ;
   - **aucun cookie `_pk_*`** ni autre cookie Matomo ;
   - aucune erreur CSP dans la console, sur l'accueil, une catégorie, une fiche, la recherche, une 404, le panier et le
     tunnel, à 1440 et 390 px ;
   - opposition effective ;
   - deux visiteurs reçoivent le même HTML : Varnish intact ;
   - `stats.` coupé, la boutique reste identique ;
   - Lighthouse rejoué sur accueil, catégorie et fiche, et mis à jour sur la page Lighthouse du site de documentation ;
   - section d'administration **ouverte réellement** ;
   - `env:check --serveur` à 0 erreur.

   Une commande de test, si elle est nécessaire, est **annulée dans l'administration**.

## Règles et pièges à conserver

- **Annoncer chaque commande `composer` ou `bin/magento` avant exécution**, en local comme sur le serveur. Aucune
  extension Magento tierce sans accord explicite : un module maison suffit.
- **Ressources partagées du VPS** (nginx, PHP-FPM, MariaDB globale, certificats, paquets, cron système) : tu ne les
  modifies pas toi-même. Tu livres un **script relu** que Pierre lance :
  - `ssh <vps> 'bash -s' < script` ;
  - ou, avec des gabarits, `tar -C deploy -cz . | ssh … 'bash …'`.

  Les lectures et les commandes Magento de la boutique restent possibles.
- **Tu ne crées pas de compte ni ne saisis de mot de passe** sur Matomo en production : Pierre le fait. Les
  identifiants restent dans son gestionnaire de mots de passe, jamais dans le dépôt ni dans le chat.
- **Ressources du `<head>`** : un `<script src>` ou un `<link>` ajouté par `default_head_blocks.xml` est regroupé par
  Magento avec les autres ressources. Au lot 8, un favicon y a hérité de `as="font"` et a fait sauter un préchargement de
  fonte. Passe par un **bloc dans `head.additional`**, comme `Magento_Theme/templates/html/head/icons.phtml`.
- **Deux thèmes** : la vitrine `MadameAiguille/default` (Hyvä, Tailwind) et le tunnel `MadameAiguille/checkout` (Luma,
  LESS). Un layout de **module** (`view/frontend/layout/default.xml`) s'applique aux deux. Vérifie les deux pages. La
  CSP du tunnel est bloquante depuis Magento 2.4.7.
- **Rien de propre au visiteur dans le HTML** : Varnish et le cache de pages servent la même page à tous. Après un
  `config:set`, lancer `mage cache:flush`, qui vide aussi Varnish.
- **nginx** : un `add_header` d'un bloc `location` annule tous ceux du niveau `server` (piège vu au lot 8 avec
  `98-cache-guide.sh`). Répète les en-têtes de sécurité dans chaque `location` qui en pose.
- `config:show` peut renvoyer du vide sur une valeur par défaut. Utilise `ScopeConfigInterface` ou `env:check`. Une
  section d'administration peut planter seule : ouvre-la réellement.
- Tailwind : une classe nouvelle n'existe qu'après `npm run build` dans `web/tailwind`. Ne jamais modifier
  `pub/static/`, le CSS compilé ni `vendor/`, et ne pas créer de `tailwind.config.js`.
- Site de documentation : les pages générées par `generer-pages.mjs` sont ignorées par git. Publier avec
  `docs-site/publier.sh` après la mise à jour des documents.
- Git : commits atomiques en français. **Pas de `push --force`** : corrige par un nouveau commit. Depuis le lot 8,
  Pierre fusionne dans `main` **par pull request GitHub**, avec commit de fusion. Demande-lui avant de fusionner
  autrement.
- La base Matomo s'ajoute au périmètre des **sauvegardes, qui n'existent toujours pas** (guide d'exploitation §5).
  Écris-le dans le guide, sans les mettre en place dans ce lot, sauf demande de Pierre.

## Ce qui dépend de Pierre

- le DNS ;
- le lancement du script serveur ;
- l'assistant web de Matomo ;
- les comptes et la double authentification ;
- les exclusions d'IP ;
- les décisions de la section « Informations à demander ».

Sans elles, prépare et documente, mais ne déclare pas la mesure en service.

## Fin du lot

Déroule **tout le §25** :

- **Recette** : écrans **et** administration, tests, PHPCS.
- **Documentation** :
  - une nouvelle section **§30 « Mesure d'audience (lot 9) »** dans `documentation-theme.md`, avec les chemins réels ;
  - le mémo Céline §24, avec la ligne « Consulter les statistiques » ;
  - le journal §17 et `plan-de-developpement.md` (v3.5, lot 9 marqué livré) ;
  - une page illustrée « Consulter les statistiques » dans `guide-back-office.md` ;
  - `commandes-et-deploiement.md` et `deploy/serveur/README.md`.
- **Guide d'exploitation**, au lieu d'un prompt suivant :
  - la ligne Matomo de « La boutique en une page » ;
  - le compte au §2 ;
  - la surveillance mensuelle ;
  - les mises à jour de Matomo au §8 (sauvegarde de la base, puis mise à jour) ;
  - la sauvegarde de la base Matomo au §5 ;
  - les incidents ;
  - le retrait de la ligne « lot 9 » du §10 ;
  - un passage en v1.1.
- **Publication** : publier le site de documentation, pousser la branche, ouvrir la pull request.

Ce qui ne peut pas être tenu est dit et consigné comme limite, jamais sauté en silence.

Commence par un plan court et l'audit vérifiable, sans redemander les décisions acquises.
