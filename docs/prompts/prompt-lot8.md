# Reprise du développement — lot 8 Recette de production, exploitation et ouverture des ventes

Tu reprends **Madame Aiguille**, boutique Magento Open Source 2.4.9 avec Hyvä 1.5.2. Réponds en français à Pierre, développeur Laravel/Vue/Tailwind et ancien responsable technique Magento 2. Céline doit pouvoir exploiter la boutique depuis l’administration.

## État de départ à vérifier

- Dépôt : `/home/pierre/Documents/aiguille`, application dans `shop/`, documentation dans `docs/`, déploiement dans `deploy/` et `.github/workflows/deploiement.yml`. **Le dépôt GitHub est public** : aucun secret, accès SSH, chemin d’administration ni identifiant HTTP dans un fichier versionné.
- **Lot 8a fusionné en avance rapide et poussé** : `main` et `lot-8a-preproduction` contiennent le commit de clôture du 8a (plan v3.0). Le commit qui contient ce prompt vient ensuite. Vérifier les références distantes, sans supposer que HEAD est resté identique ; une autre session a déjà travaillé en parallèle dans ce dépôt pendant le 8a.
- **La boutique est en ligne** : `https://madame-aiguille.fr`, installation neuve du 10/10/2026, mode production, **non indexée** (`X-Robots-Tag` nginx + robots `NOINDEX,NOFOLLOW`), catalogue vide au départ, version `20261010-073014-090e1ba` (build de `090e1ba`). Le code de clôture du 8a (docs, workflow, script 90) n’a pas besoin d’être redéployé ; un déploiement du code applicatif se fait par le workflow.
- Lots livrés : 1, 2, 6a, 3, 4, 7, 5, 6b et 8a (infrastructure). Ordre restant : **8**, dernier lot.
- Référence technique de fin de 8a : **115 tests, 298 assertions** ; PHPCS Magento2 sans erreur ; build GitHub vert ; `madameaiguille:env:check --serveur --noindex` à 0 erreur sur le serveur.
- Recette : `docs/recettes/lot-8a.md` et ses captures. Le local garde d’anciennes commandes de Pierre, sans effet sur le serveur.

Commence par `git status`, `git log --oneline --decorate -n 20`, les références distantes, puis lis :

1. `AGENTS.md` et `docs/documentation-theme.md` **§25**, rituel de fin de lot ;
2. cette documentation : **§23** (ce qui reste, responsables), §24 (mémo Céline), §26 (livraison / paiement), §27 (tunnel), §28 (langue), **§29** (serveur, déploiement, accès, pièges) ;
3. `docs/commandes-et-deploiement.md` (commandes maison, scripts, déploiement par versions) et `deploy/serveur/README.md` (scripts, arborescence, retour arrière, accès à l’administration) ;
4. `docs/plan-de-developpement.md` v3.0, état après le 8a et lot 8 ;
5. `docs/recettes/lot-8a.md`, `docs/cahier-des-charges-docs-developpement/plan-de-tests.md` (§3 à §10, critères de mise en production).

Créer **`lot-8-ouverture`** depuis `main` à jour, sans préfixe `codex/`. Préserver les travaux existants.

## Décisions acquises

- Serveur : VPS OVH partagé avec d’autres sites et services de Pierre. Accès SSH par la clé de Pierre ; sudo sans mot de passe. Ubuntu 26.04 LTS, PHP 8.5.4, MariaDB 12.3.3, OpenSearch 3.9, Valkey 9.0.4 (6379 sites Laravel, 6380 cache, 6381 sessions), Varnish **7.7** (version de référence de Magento 2.4.9), RabbitMQ.
- **Domaine principal directement**, pas de staging. `madame-aiguille.fr` canonique, `www` et HTTP en 301, un certificat pour les deux noms.
- Build dans **GitHub Actions** sans base (sites / boutiques / thèmes figés dans `config.php`), lancement manuel, publication par **versions successives** et lien `current` (`deploy/bascule.sh`), retour arrière par le même script. PHP-FPM, cron et commandes Magento sous l’utilisateur système **`madame-aiguille`** : `sudo -u madame-aiguille php /var/www/madame-aiguille/current/bin/magento …`. Pas d’utilisateur de déploiement.
- Double authentification active (Google Authenticator). Administration réservée : IP de la liste **ou** identifiant + mot de passe HTTP (`satisfy any`), après une alerte Chrome « Site dangereux » (faux positif d’hameçonnage) signalée à Google.
- SMTP **Brevo** (IP du VPS autorisée) ; expéditeurs `contact@madame-aiguille.fr` ; DKIM Brevo, DMARC `p=none`.
- reCAPTCHA **v2 invisible** sur création de compte, contact, newsletter et mot de passe oublié ; **pas** sur la commande.
- Mollie en **mode test**, webhooks actifs, clés saisies ; sept moyens actifs, **provisoires**, à valider avec Céline (ne pas les changer sans arbitrage). Paiement réel de validation : par Pierre.
- Catalogue : catégories et premières créations **saisies à la main par Pierre et Céline** dans l’administration. Aucun jeu de données de `docs/jeux-de-donnees/` ne tourne sur le serveur.
- Pas de bandeau « boutique en préparation ».
- Reprises des lots précédents : checkout natif deux étapes en Luma fallback, Mondial Relay point relais (`BDTEST` tant que le code réel manque), retrait payé sur place, cadeau 2 €, franco 60 €, aucune TVA (franchise), CGV natives obligatoires, newsletter décochée.

## Informations à demander au démarrage

Regrouper seulement ce qui manque :

1. Clés reCAPTCHA saisies ? Catalogue réel en ligne (combien de créations, catégories) ?
2. Levée de l’alerte Chrome sur l’administration ; IP de Céline.
3. Réponses de Céline : code enseigne et tarifs Mondial Relay, adresse de retrait, moyens Mollie, textes juridiques et contenus.
4. Politique de sauvegarde souhaitée (fréquence, rétention, destination hors VPS : stockage OVH, autre serveur, poste de Pierre).
5. Date visée pour l’ouverture des ventes.

## Périmètre du lot 8

1. **Recette de production reportée du 8a**, sur le vrai site et un catalogue réel :
   - activer les quatre types reCAPTCHA **après** les clés ; refus serveur sans jeton et envoi normal ;
   - paiement Mollie test : relais + cadeau + CGV → succès → **webhook** → « Paiement reçu », facture, emails ; annulation / échec avec **restauration native du panier** ; retrait payé sur place avec rendez-vous ;
   - commande dédiée : **facture et avoir avec emballage** (ligne, montants, mention de TVA) — avoir déclenché par Pierre ;
   - emails **reçus** et tracés : confirmation, paiement reçu, prêt pour retrait, expédition, création de compte, contact avec JPG / PNG ; newsletter invitée / connectée avec clic de confirmation, aucune inscription sans la case ;
   - écrans du tunnel, des confirmations et du compte à 1440 / 390 ; sections d’administration avec Pierre ; commandes de test annulées **dans l’administration**.
2. **Validation Mollie** : vérification du site par Mollie, puis paiement réel par Pierre, remboursement, décision sur le mode jusqu’à l’ouverture.
3. **Sauvegardes** : dump quotidien de `madame_aiguille` et des médias, rétention, copie hors du VPS, **test de restauration documenté** ; avant la première vraie commande. Scripts versionnés dans `deploy/serveur/`, sans secret.
4. **Back-office de Céline** : rôle ACL (catalogue, ventes, clients en lecture, CMS ; sans configuration, modules, thèmes, utilisateurs), compte à son nom avec double authentification et langue française, IP ajoutée ; alerte de stock bas native ; guide `docs/guide-back-office.md` illustré (produit et poids, stock, commande, relais, retrait, bandeau, blocs, réseaux).
5. **Ouverture des ventes** : retirer `X-Robots-Tag` du gabarit nginx et passer robots à `INDEX,FOLLOW` le jour J (`env:check --serveur` sans `--noindex`), sitemap, Search Console, `madameaiguille.fr` réservé et redirigé en 301, durcissement DMARC après observation.
6. **Qualité** : Lighthouse sur accueil / catégorie / fiche, recette transversale (plan de tests §3 à §10 : session expirée, fusion du panier à la connexion…), contrôle des poids, WebP si utile, logo SVG et favicon 16 / 32 px.
7. **Hygiène** : compte RabbitMQ `guest`, Magento Analytics et export Commerce, `<title>` vide de l’accueil, espace avant le point sur la page de connexion.

## Règles et pièges à conserver

- Toute surcharge visuelle dans le thème enfant approprié ; logique métier dans les modules. Aucun changement manuel de `vendor/`, `pub/static/`, du CSS compilé ni des fichiers d’une version déployée. Aucun `tailwind.config.js`.
- **Annoncer chaque commande `composer` ou `bin/magento` avant exécution**, en local comme sur le serveur. Aucune extension tierce sans accord explicite.
- **Garde-fou de l’agent** : il refuse de modifier les ressources partagées du VPS (nginx et PHP des autres sites, certificats, paquets système). Livrer ces étapes en **scripts relus** que Pierre lance (`ssh <vps> 'bash -s' < script`, ou `tar -C deploy -cz . | ssh … 'bash …'` quand des gabarits sont nécessaires). Les lectures et les commandes Magento de la boutique restent possibles.
- **Mollie 3.1.3 ne facture et ne passe en « Paiement reçu » que par le webhook** ; le retour navigateur n’affiche que le succès. Webhook joignable : `POST /mollie/checkout/webhook/` doit rester public.
- **Avant d’activer un type reCAPTCHA, ses clés** : sinon le formulaire refuse tout envoi.
- La mise à niveau Ubuntu retire les PHP non natifs et désactive les dépôts externes ; après toute mise à niveau système, vérifier les cinq autres sites (200).
- `setup:install` n’a pas `--keep-generated` ; Magento exige `app/etc` inscriptible (la bascule l’ouvre au groupe) ; une commande Magento est construite à chaque `bin/magento` (proxy pour toute dépendance lourde).
- Le mot de passe SMTP n’est pas « sensible » pour Magento : le saisir dans l’administration, ou par Pierre en saisie masquée.
- `config:show` peut renvoyer du vide sur un défaut : `ScopeConfigInterface` ou `madameaiguille:env:check`. Une section admin peut planter seule : l’ouvrir réellement.
- Deux builds : Tailwind pour Hyvä, LESS pour `MadameAiguille/checkout` ; en production, les deux sont produits par le workflow. Comparer CSS servie et construite après chaque déploiement (référence 8a : 205 644 / 372 061 / 70 726 octets).
- Captures : vérifier les dimensions dans le DOM ; à 1440 px la page utile fait 1425 px (barre de défilement). Une page peut rester en cache : URL différente.
- **Leaflet** : même URL non versionnée que le widget Mondial Relay ; une seule inclusion. Horaires / Photo restent une limite CSP assumée.
- Luma garde ses **24 colonnes** ; dictionnaire du tunnel distinct ; clés exactes ; pas de deux-points dans une directive Knockout.
- Les commandes réservent du stock et des rendez-vous. **Annulation dans l’administration**, jamais par script destructif. État vide ou suppression : administration également.
- Le dépôt est public : ne jamais y écrire le chemin d’administration, l’identifiant HTTP, l’IP ou le port SSH, le téléphone de Céline.

## Ce qui dépend encore de Pierre / Céline

Contenus juridiques, tarifs, code enseigne, adresse de retrait, choix des moyens Mollie, photos et textes : ils ne s’inventent pas. Sans eux, préparer et documenter, mais ne pas déclarer l’ouverture possible ; chaque manque garde un responsable et une échéance au §23. Le paiement réel est fait par Pierre ; ne pas inventer de montant, d’identité ou d’autorisation de remboursement.

Terminer par **tout le §25** : recette écrans et administration, sections de documentation avec chemins réels, mémo Céline, limites, journal par commit, plan et styleguide si nécessaire ; fusion en avance rapide dans `main` et push des deux branches. Le lot 8 étant le dernier, le passage de relais devient un **guide d’exploitation** (mises à jour Magento / Hyvä, sauvegardes, renouvellements, déploiement, retour arrière) plutôt qu’un prompt de lot. Commencer par un plan court et l’audit vérifiable, sans redemander les décisions déjà acquises.
