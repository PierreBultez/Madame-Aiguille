# Recette du lot 8a — préproduction, emails et validation Mollie

Branche : `lot-8a-preproduction`, créée le 09/10/2026 depuis `main` (`73b3865`, langue française intégrée).
Journal tenu au fil du lot ; aucune valeur secrète n'y figure (ni chemin d'administration, ni identifiant HTTP, ni accès SSH).

## Décisions de Pierre (09 et 10/10/2026)

- Cible : le VPS OVH qui héberge déjà ses autres sites, accès SSH par sa clé. **Domaine principal directement**
  (`madame-aiguille.fr`), sans sous-domaine de préproduction, non indexé ; l'équipe Mollie doit pouvoir voir le site.
- **Installation neuve** (pas de copie de la base locale), 2 ou 3 vraies créations saisies ensuite.
- Pas de bandeau « boutique en préparation ».
- Déploiement par **versions successives** (`releases/` + lien `current`), **build dans GitHub Actions** ; la clé SSH
  personnelle de Pierre sert au workflow ; pas d'utilisateur de déploiement.
- `madame-aiguille.fr` canonique, `www` en 301, **un certificat pour les deux noms**.
- **Double authentification** de l'administration dès l'installation.
- reCAPTCHA **v2 invisible** sur création de compte, contact, newsletter et mot de passe oublié ; pas sur la commande.
- SMTP **Brevo** ; IP locale et IP du VPS autorisées chez Brevo par Pierre.
- Webhook Mollie volontairement coupé en local, actif sur le serveur.
- VPS aligné sur le poste : Ubuntu 26.04 LTS, PHP 8.5.4, MariaDB 12.3.3, OpenSearch 3.9, Valkey 9.0.4 ;
  Varnish 7.7 (version de référence de Magento 2.4.9).
- Céline : `BDTEST`, tarifs, lieu de retrait, contenus juridiques et moyens Mollie restent provisoires.

## Audit de départ (09/10/2026)

| Constat | Conséquence |
|---|---|
| Mollie 3.1.3 ne facture et ne passe une commande en « Paiement reçu » **que par le webhook** (`SuccessfulPayment` s'arrête si le type n'est pas `webhook`) ; le retour navigateur ne fait qu'afficher | Commande locale `000000023` payée en test chez Mollie mais restée « En attente de paiement » : attendu sans webhook. Le serveur doit l'avoir actif |
| Aucun email ne partait en local : port 587 + IP non autorisée chez Brevo (« 525 Unauthorized IP ») | Corrigé par Pierre dans Brevo ; le réglage `ssl` sur 587 est sans effet (STARTTLS automatique du transport Symfony) |
| Domaine chez OVH, Brevo vérifié, DKIM `brevo1` / `brevo2`, DMARC `p=none` ; SPF limité à OVH | Alignement DMARC par DKIM ; SPF à compléter si Brevo le demande |
| VPS Ubuntu 25.10 (fin de vie), PHP 8.4, Elasticsearch 9 inutilisé à 24,5 Go de RAM, Redis partagé par trois sites | Mise à niveau 26.04 par Pierre, Elasticsearch supprimé, OpenSearch installé, Redis remplacé par Valkey |
| Formulaires surchargés (newsletter, contact) : points d'accroche reCAPTCHA conservés ; compte, connexion et mot de passe oublié viennent du parent Hyvä | Activation par la seule configuration |
| `Magento_TwoFactorAuth` désactivé dans `config.php` | Activé (`04fd144`) |

## Serveur

| Étape | Résultat |
|---|---|
| Elasticsearch 9.5.3 | Supprimé (index internes seulement, aucun site client) ; 24 Go de RAM libérés |
| OpenSearch 3.9.0 | Nœud unique local, tas 2 Go, statut vert ; certificats de démonstration supprimés par Pierre |
| Varnish 7.7.3 | 127.0.0.1:6081, 1 Go, en-têtes Magento |
| Ubuntu 26.04.1 LTS | Mise à niveau par Pierre. **PHP 8.4 retiré sans remplaçant** : quatre sites en 502, rétablis en PHP 8.5.4 (script lancé par Pierre, garde-fou de l'agent sur les ressources partagées) |
| Dépôts, MariaDB 12.3.3, Valkey 9.0.4 | Scripts 10, 20, 60 lancés par Pierre ; dump complet avant MariaDB ; Redis supprimé ; cinq sites à 200 |
| Hébergement (script 70) | Utilisateur `madame-aiguille`, arborescence, pool dédié, MariaDB 2 Go, certificat `madame-aiguille.fr` + `www` (expire le 08/01/2027), rotation des journaux |
| Build GitHub | Build seul vert en 3 min (Composer avec accès, fontes Fontshare vérifiées, Tailwind, DI, statiques sans base) ; `main` avancé en avance rapide jusqu'à `090e1ba` avec l'accord de Pierre pour rendre le workflow lançable |
| Première publication | Version `20261010-073014-090e1ba` déposée (1,2 Go, droits conformes) ; bascule arrêtée volontairement faute d'installation |
| Installation (script 80) | Lancée par Pierre : mode production, HTTPS, NOINDEX, Valkey, Varnish, OpenSearch ; `env:check` à 0 erreur une fois le cron lancé. Purges Varnish en erreur **avant** l'installation du VCL (07:34:13 < 07:34:17), sans erreur ensuite. Erreur `CDE04-02` de l'export Commerce émise pendant `setup:install`, sans suite (14 indexeurs planifiés et prêts) |
| Compte et secrets | Compte administrateur créé par Pierre ; SMTP non secret posé par l'agent (`config:set`), mot de passe saisi par Pierre en saisie masquée ; double authentification associée ; clés Mollie saisies par Pierre |
| Alerte Chrome | « Site dangereux » sur la connexion de l'administration : audit sans trace de compromission (configuration, CMS, médias, comptes, fichiers) ; faux positif d'hameçonnage. Administration réservée par `90-acces-admin.sh` (IP de Pierre ou mot de passe HTTP), contrôle 401 hors liste et en 4G par Pierre ; signalement envoyé à Google ; Search Console sans problème de sécurité |

## Local

- `madameaiguille:env:check` : 0 erreur, 3 avertissements attendus (HTTP, `BDTEST`) ; profil `--serveur --noindex` en
  erreur sur le poste, comme attendu.
- Patches d'installation appliqués sans ajouter ni réécrire de ligne de configuration (322 avant, 322 après).
- Double authentification active ; email du compte `admin` local corrigé en `pierre.bultez@proton.me` à la demande
  de Pierre (modèle utilisateur Magento, validations natives).
- Tests : 115 tests, 298 assertions. PHPCS Magento2 : 0 erreur sur les fichiers PHP ajoutés.

## Recette de production (10/10/2026)

Captures dans `docs/recettes/lot-8a/`, prises par Chrome sans interface sur le vrai site, puis dimensions contrôlées dans le DOM du navigateur intégré.

| Écran | 1440 | 390 | Constat |
|---|---|---|---|
| Accueil | `accueil-1440.png` | `accueil-390.png` | Hero, nouvelles, histoire, réassurance, bandeau 60 € ; menu vide (aucune catégorie, attendu) |
| Contact | `contact-1440.png` | `contact-390.png` | Formulaire, pièce jointe, consentement, encarts |
| CGV | `cgv-1440.png` | `cgv-390.png` | Brouillon générique et son encadré, visibles tant que Céline ne les a pas validés |
| Panier vide | `panier-vide-1440.png` | `panier-vide-390.png` | État vide natif habillé |
| Connexion | `connexion-1440.png` | `connexion-390.png` | Espace parasite avant le point dans « sans créer de compte . » (préexistant) |
| 404 | `404-1440.png` | `404-390.png` | Page « On a perdu le fil… » |

- DOM à 390 px : largeur et défilement 390 (aucun débordement), Britney et Sentient 400 / 500 / italique chargées, aucune ressource en erreur. À 1440 px : 1425 px utiles, la bande blanche des captures est la barre de défilement masquée.
- CSS servies = construites : Hyvä 205 644 o, tunnel 372 061 o (mobile) et 70 726 o (ordinateur), plus légères qu'en local (compilation de production).
- HTTP et `www` → `https://madame-aiguille.fr` en 301 ; `X-Robots-Tag: noindex, nofollow` et robots `NOINDEX,NOFOLLOW` ; Varnish MISS puis HIT (l'en-tête `X-Magento-Cache-Debug` vient du VCL officiel de Magento 2.4.9) ; administration à 401 hors liste, y compris par `/index.php/` ; webhook Mollie à 200 ; cinq autres sites du VPS à 200.
- `<title>` vide sur l'accueil, en local comme en production : préexistant, reporté.
- `/styleguide` : aucun état ajouté, le lot ne livre pas d'écran.

## Validation technique

- PHPUnit sur les modules maison : **115 tests, 298 assertions**.
- PHPCS Magento2 sur les 7 fichiers PHP du lot : **0 erreur** (avertissements de docblocks, comme le reste du projet).
- `setup:upgrade --keep-generated`, `setup:di:compile` en local ; build GitHub complet ; scripts shell vérifiés (`bash -n`) ; YAML du workflow valide ; `git diff --check` propre.

## Limites et suite (lot 8)

- **Non recetté, faute de catalogue réel et de clés reCAPTCHA** : paiement Mollie test jusqu'au webhook, annulation avec restauration native du panier, retrait payé sur place, facture et avoir avec emballage, emails effectivement reçus, newsletter, reCAPTCHA, tunnel et compte à 1440 / 390 sur le serveur. Rien n'a été simulé.
- **Administration du serveur** : non ouverte par l'agent (liste d'IP, double authentification, compte de Pierre). Contrôle visuel des sections touchées à faire avec Pierre au lot 8.
- **Validation Mollie et paiement réel** : après catalogue et pages juridiques réels.
- **Sauvegardes automatiques** : absentes, à poser avant la première vraie commande.
- **Garde-fou de l'agent** : les modifications des ressources partagées du VPS ont été livrées en scripts relus et lancés par Pierre.
- Les anciennes commandes locales (dont `000000023`, payée en test sans webhook) n'ont aucun effet sur le serveur, installé à neuf.

## Livraisons

- `276f162` — contrôle d'environnement `madameaiguille:env:check`.
- `fd1e19b` — scripts de provisionnement 10 à 60.
- `74bcfb9` — patches d'installation neuve, `config.php` (sites, boutiques, thèmes).
- `04fd144` — workflow, bascule, fontes, hébergement (70), installation (80), double authentification.
- `090e1ba` — documentation intermédiaire ; `main` avancé pour le premier déploiement.
- `dbb251f` — administration réservée aux IP connues ou au mot de passe HTTP (90).
- Clôture — workflow manuel seul et actions Node 24, documentation, plan v3.0, captures, prompt du lot 8.
