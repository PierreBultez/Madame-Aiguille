# Commandes, scripts et déploiement — aide-mémoire de Pierre

Tout ce qui a été écrit pour ce projet en dehors du code de la boutique : les commandes `bin/magento` maison, les tâches
cron, les scripts de déploiement et de serveur, et le fonctionnement du déploiement par versions. Le détail technique
reste dans `docs/documentation-theme.md` (§26 livraison, §29 serveur) et `deploy/serveur/README.md`.

> **`<vps>`** remplace l'accès SSH du serveur (`-p <port> pierre@<ip>`), qui ne doit pas apparaître dans ce dépôt
> public. Le plus simple est un alias dans `~/.ssh/config` sur votre poste :
>
> ```
> Host madame-aiguille
>     HostName <ip du VPS>
>     Port <port SSH>
>     User pierre
> ```
>
> Toutes les commandes ci-dessous deviennent alors `ssh madame-aiguille …`.

## 1. Où tourne quoi

| Où | Ce qu'on y fait | Comment appeler Magento |
|---|---|---|
| **Poste local** (`shop/`) | Développement, tests, captures | `bin/magento …` depuis `shop/` |
| **GitHub Actions** | Construction de chaque version (jamais de base de données) | — |
| **Serveur** (`/var/www/madame-aiguille`) | Boutique en ligne | `sudo -u madame-aiguille php /var/www/madame-aiguille/current/bin/magento …` |

Sur le serveur, Magento tourne sous l'utilisateur système **`madame-aiguille`** (PHP-FPM, cron, commandes). Lancer une
commande sous `pierre` ou `root` créerait dans `var/` des fichiers que la boutique ne pourrait plus écrire : toujours
passer par `sudo -u madame-aiguille`. Pour alléger :

```bash
ssh <vps>
alias mage='sudo -u madame-aiguille php /var/www/madame-aiguille/current/bin/magento'
mage cache:flush
```

## 2. Le déploiement par versions, expliqué

### L'idée

Oui, c'est bien cela, avec une précision : **nginx sert toujours le même chemin**, `/var/www/madame-aiguille/current`.
`current` n'est pas un dossier, c'est un **lien symbolique** qui pointe vers une des versions rangées dans `releases/`.
« Choisir la version servie », c'est changer la cible de ce lien ; nginx et PHP ne sont jamais reconfigurés pour ça.

```
/var/www/madame-aiguille/
├── releases/
│   ├── 20261010-073014-090e1ba/      ← une version complète : code, vendor, generated, pub/static
│   └── 20261012-091500-3f2a7c1/      ← la suivante
├── current -> releases/20261012-091500-3f2a7c1      ← ce que nginx, le cron et les commandes utilisent
└── shared/                            ← ce qui doit survivre d'une version à l'autre
    ├── app/etc/env.php                   secrets du serveur (base, Valkey, clé de chiffrement…)
    ├── var/                              journaux, caches fichiers, sessions de secours
    ├── pub/media/                        photos des produits, logo, médias CMS
    └── sauvegardes/                      dump pris avant chaque mise à jour de base
```

Chaque version contient des **liens** vers `shared/` pour `env.php`, `var/` et `pub/media/`. Les données vivent donc
une seule fois, quelle que soit la version servie. Le nom d'une version donne sa date (UTC) et le commit dont elle
est issue : `20261010-073014-090e1ba` = construite le 10/10/2026 à 07:30:14 depuis `090e1ba`.

### Ce qui se passe quand vous déployez

1. **GitHub construit la version** (*Actions › Déploiement › Run workflow*) : `composer install` avec les accès Hyvä
   et Magento, téléchargement et vérification des fontes, feuille Tailwind, compilation DI, fichiers statiques de la
   vitrine, du tunnel et de l'administration. Aucune base n'est nécessaire.
2. **Si « Publier » est coché**, GitHub envoie la version par `rsync` dans `releases/<version>/`. Les fichiers
   identiques à la version en ligne ne sont pas recopiés mais liés (liens physiques) : l'envoi est rapide et ne double
   pas l'espace disque.
3. **La bascule** (`deploy/bascule.sh`, lancée par GitHub sur le serveur) :
   - branche `env.php`, `var/` et `pub/media/` partagés dans la nouvelle version ;
   - demande à Magento si la base ou la configuration doivent évoluer (`setup:db:status`, `app:config:status`) ;
   - **seulement si oui** : mode maintenance, dump de la base dans `shared/sauvegardes/avant-<version>.sql.gz`,
     `setup:upgrade --keep-generated` ;
   - **change le lien `current`** en une opération atomique : il n'existe aucun instant où le site est « entre deux » ;
   - recharge PHP-FPM (OPcache garde le code en mémoire et doit l'oublier) et nginx ;
   - vide les caches de Magento et de Varnish, sort de maintenance si elle était active ;
   - lance `madameaiguille:env:check --serveur --noindex` et affiche le résultat ;
   - supprime les versions au-delà des **cinq plus récentes**, jamais celle en ligne.

Sans patch de données ni changement de schéma, **il n'y a aucune coupure** : l'ancienne version sert les visiteurs
jusqu'à l'instant du changement de lien. Avec une mise à jour de base, la maintenance dure le temps de `setup:upgrade`.

### Revenir en arrière

Relancer la bascule avec le nom d'une version encore présente dans `releases/` :

```bash
ssh <vps> 'ls -1 /var/www/madame-aiguille/releases/; readlink /var/www/madame-aiguille/current'
ssh <vps> 'bash -s -- 20261010-073014-090e1ba' < deploy/bascule.sh
```

Le code revient instantanément. **La base, elle, ne revient pas seule** : si la version abandonnée avait lancé
`setup:upgrade` (nouveau patch, nouvelle colonne), restaurer aussi `shared/sauvegardes/avant-<version abandonnée>.sql.gz`
avant la bascule. Les commandes passées depuis seraient alors perdues : ne le faire qu'en connaissance de cause.

### Si quelque chose échoue

- **Le build GitHub échoue** : rien n'est envoyé, le site ne bouge pas.
- **L'envoi échoue** : une version incomplète reste dans `releases/`, jamais servie ; la suivante la remplacera.
- **`setup:upgrade` échoue** : le site reste **en maintenance sur l'ancienne version** (le lien n'a pas bougé).
  Corriger, puis relancer la bascule ; ou restaurer le dump et faire `mage maintenance:disable`.

## 3. Commandes `bin/magento` maison

Trois commandes, toutes dans le module `MadameAiguille_Theme` (`shop/app/code/MadameAiguille/Theme/Console/Command/`).

### `madameaiguille:env:check` — contrôle d'environnement

```bash
bin/magento madameaiguille:env:check [--serveur] [--noindex]
```

| Option | Effet |
|---|---|
| *(aucune)* | Profil poste local : HTTP, mode développeur et webhook coupé sont tolérés |
| `--serveur` | Exigences d'un serveur exposé : mode production, HTTPS forcé, webhook Mollie actif, cron de moins de 10 min |
| `--noindex` | Le site ne doit pas être indexé : robots `NOINDEX,NOFOLLOW` exigé (à retirer le jour de l'ouverture) |

Lit la configuration **réellement appliquée** (fusion `env.php` + base + valeurs par défaut des modules), jamais un
secret : seule la présence d'une clé est vérifiée. Contrôle : mode, URLs, indexation, cache / sessions / Varnish,
OpenSearch, cron, double authentification, SMTP et expéditeurs, clé Mollie du mode courant et webhooks, reCAPTCHA des
quatre formulaires, code enseigne Mondial Relay, franco, poids des produits, fontes des deux thèmes.
Sortie : tableau OK / ATTENTION / ERREUR ; **code de retour 1 s'il y a au moins une erreur**. Lancée automatiquement à
la fin de chaque bascule.

```bash
ssh <vps> 'sudo -u madame-aiguille php /var/www/madame-aiguille/current/bin/magento madameaiguille:env:check --serveur --noindex'
```

### `madameaiguille:shipping:import-rates` — grille Mondial Relay

```bash
bin/magento madameaiguille:shipping:import-rates [--dry-run] [--file=<chemin.csv>] [--website=base]
```

| Option | Effet |
|---|---|
| `--dry-run` | Valide le fichier sans rien écrire |
| `--file` | Autre CSV que la grille versionnée `Theme/data/tablerates-mondial-relay.csv` |
| `--website` | Code du site web (par défaut `base`, le seul) |

**Remplace** la grille au poids du transporteur *Table Rates* par le CSV, dans une transaction : un fichier invalide
laisse la grille en place (code de retour 1). Contrôles en plus de l'import natif : pays de vente uniquement, chaque pays
commence à 0 kg. Affiche le nombre de paliers par pays. Rejouable à volonté. Procédure quand Céline change ses tarifs :
modifier le CSV, commit, déployer, puis lancer la commande sur le serveur.

### `madameaiguille:catalog:check-weight` — produits sans poids

```bash
bin/magento madameaiguille:catalog:check-weight
```

Liste les produits **simples activés** sans poids ou avec un poids nul (SKU, nom) : sans poids, la grille Mondial Relay
calcule un port faux sans aucune erreur visible. **Code de retour 1** tant qu'il en reste. À passer après une saisie de
produits et avant l'ouverture ; `env:check` le compte aussi.

## 4. Tâches cron maison

Exécutées par le cron Magento (`cron:run` chaque minute, sous `madame-aiguille` sur le serveur) ; rien à lancer à la main.

| Tâche | Horaire | Rôle |
|---|---|---|
| `madameaiguille_refresh_novelty_badges` | 00:05 chaque nuit | Purge les caches des produits dont la période de nouveauté commence ou finit (veille ou jour même) : sans elle, le badge « Nouveauté » resterait figé, la date ne changeant rien en base |
| `madameaiguille_contact_purge_attachments` | 03:17 chaque nuit | Supprime les photos jointes au formulaire de contact au-delà de la durée réglée (30 jours par défaut, *Madame Aiguille › Formulaire de contact*) |

Forcer un passage : `mage cron:run --group=default`.

## 5. Le workflow GitHub *Déploiement*

Fichier : `.github/workflows/deploiement.yml`. Lancement **manuel uniquement** :

- *GitHub › Actions › Déploiement › Run workflow*, choisir la branche (normalement `main`) ;
- case **« Publier la version sur le serveur »** : décochée = build seul (valide une branche, rien n'est envoyé) ;
  cochée = build + envoi + bascule.

Ou depuis le terminal :

```bash
gh workflow run deploiement.yml -R PierreBultez/Madame-Aiguille --ref main                 # build seul
gh workflow run deploiement.yml -R PierreBultez/Madame-Aiguille --ref main -f deployer=true # build + publication
gh run watch -R PierreBultez/Madame-Aiguille                                                # suivre l'exécution
```

Secrets du dépôt (*Settings › Secrets and variables › Actions*), jamais affichés : `COMPOSER_AUTH` (votre `auth.json`
Hyvä + Magento), `SSH_PRIVATE_KEY` (votre clé), `SSH_KNOWN_HOSTS` (empreintes du serveur), `DEPLOY_HOST`,
`DEPLOY_PORT`, `DEPLOY_USER`. À mettre à jour si vous changez de clé SSH, de port ou de clés Composer.

## 6. Scripts du dossier `deploy/`

### `deploy/fontes.sh` — fontes originales

```bash
deploy/fontes.sh            # depuis la racine du dépôt
```

Télécharge Britney et Sentient sur Fontshare, vérifie l'empreinte SHA-256 de chaque WOFF2 (`deploy/fontes.sha256`) et
les copie dans les deux thèmes. La licence interdit de les mettre dans le dépôt : le build GitHub les récupère ainsi à
chaque version. Utile aussi pour un poste neuf.

### `deploy/bascule.sh` — mise en ligne d'une version

```bash
ssh <vps> 'bash -s -- <version>' < deploy/bascule.sh
```

Décrit au §2. Lancé par GitHub après chaque envoi ; à la main seulement pour un **retour arrière**.

### `deploy/serveur/` — provisionnement du VPS

Idempotents, sans secret, lancés depuis votre poste. Deux façons de les lancer :

```bash
# Scripts autonomes (10 à 60)
ssh <vps> 'bash -s' < deploy/serveur/<script>.sh

# Scripts qui ont besoin des gabarits voisins (70, 80, 90) : le dossier deploy/ est copié puis effacé
tar -C deploy -cz . | ssh <vps> 'D=$(mktemp -d) && tar -C "$D" -xz && bash "$D/serveur/<script>.sh" <arguments>; rm -rf "$D"'
```

| Script | Arguments | Ce qu'il fait | Quand le relancer |
|---|---|---|---|
| `10-depots.sh` | — | Dépôts APT MariaDB 12.3, OpenSearch 3.x, RabbitMQ au format deb822 | Après une mise à niveau d'Ubuntu (elle les désactive) |
| `20-mariadb.sh` | — | Dump complet de toutes les bases dans `~/sauvegardes/`, puis MariaDB 12.3 et `mariadb-upgrade` | Changement de version majeure de MariaDB |
| `30-php.sh` | — | PHP 8.5 et ses extensions, réglages repris de l'ancien 8.4 | Serveur neuf ou après une mise à niveau d'Ubuntu |
| `40-opensearch.sh` | — | OpenSearch nœud unique, local, 2 Go, sécurité interne coupée, certificats de démo retirés | Serveur neuf |
| `50-varnish.sh` | — | Varnish local (6081), 1 Go, en-têtes Magento | Serveur neuf |
| `60-valkey.sh` | — | Valkey à la place de Redis (6379 pour les sites Laravel) + cache (6380) et sessions (6381) de Magento | Serveur neuf |
| `70-hebergement.sh` | — | Utilisateur `madame-aiguille`, arborescence, pool PHP-FPM, réglages MariaDB, certificat `madame-aiguille.fr` + `www`, rotation des journaux | Serveur neuf |
| `80-installer-magento.sh` | `<version>` | **Installation neuve** dans une version déjà envoyée : base et RabbitMQ dédiés (mots de passe générés, jamais affichés), `setup:install`, mode production, réglages du serveur, VCL, cron, mise en ligne, activation nginx | **Une seule fois** ; refuse de tourner si `env.php` existe |
| `90-acces-admin.sh` | `<ip> [<ip>…]` | Administration réservée : IP listées ou identifiant + mot de passe HTTP | Pour **ajouter ou retirer une IP** (ex. celle de Céline) |

Gabarits utilisés : `deploy/serveur/nginx/madame-aiguille.conf` (TLS → Varnish → backend, redirections, `noindex`) et
`deploy/serveur/php-fpm/madame-aiguille.conf` (pool dédié).

## 7. Commandes ponctuelles

### Accès à l'administration

```bash
# Créer ou changer l'identifiant et le mot de passe HTTP (saisie masquée, seul le hachage est stocké)
ssh -t <vps> 'read -rp "Identifiant HTTP : " U; read -rsp "Mot de passe HTTP : " P; echo; read -rsp "Confirmation : " C; echo; [ -n "$U" ] && [ -n "$P" ] && [ "$P" = "$C" ] || { echo "Identifiant vide ou mots de passe différents"; exit 1; }; printf "%s:%s\n" "$U" "$(printf %s "$P" | openssl passwd -6 -stdin)" | sudo tee /etc/nginx/madame-aiguille-admin.htpasswd >/dev/null && sudo chown root:www-data /etc/nginx/madame-aiguille-admin.htpasswd && sudo chmod 0640 /etc/nginx/madame-aiguille-admin.htpasswd && echo "Mot de passe enregistré"; unset U P C'

# Ajouter l'IP de Céline à la vôtre
tar -C deploy -cz . | ssh <vps> 'D=$(mktemp -d) && tar -C "$D" -xz && bash "$D/serveur/90-acces-admin.sh" <ip-pierre> <ip-céline>; rm -rf "$D"'

# Créer un compte administrateur Magento (questions posées une à une)
ssh -t <vps> 'sudo -u madame-aiguille php /var/www/madame-aiguille/current/bin/magento admin:user:create'

# Téléphone perdu : réinitialiser la double authentification d'un compte
ssh <vps> 'sudo -u madame-aiguille php /var/www/madame-aiguille/current/bin/magento security:tfa:reset <identifiant> google'
```

### Secrets saisis en ligne de commande

```bash
# Mot de passe SMTP Brevo (si l'administration est inaccessible) — saisie masquée, chiffré par Magento
ssh -t <vps> 'read -rsp "Mot de passe SMTP Brevo : " P; echo; sudo -u madame-aiguille php /var/www/madame-aiguille/current/bin/magento config:set system/smtp/password "$P" && sudo -u madame-aiguille php /var/www/madame-aiguille/current/bin/magento cache:clean config; unset P'
```

### Surveiller

```bash
ssh <vps> 'readlink /var/www/madame-aiguille/current'                                   # version en ligne
ssh <vps> 'sudo tail -n 50 /var/www/madame-aiguille/shared/var/log/exception.log'       # erreurs Magento
ssh <vps> 'sudo tail -n 50 /var/www/madame-aiguille/shared/var/log/system.log'          # journal Magento (emails…)
ssh <vps> 'sudo tail -n 50 /var/log/nginx/madame-aiguille.error.log'                    # erreurs nginx
ssh <vps> 'sudo tail -n 50 /var/www/madame-aiguille/shared/var/log/php-fpm.log'         # erreurs PHP
ssh <vps> 'sudo crontab -u madame-aiguille -l'                                         # cron de la boutique
```

### Maintenance et caches

```bash
mage maintenance:status
mage maintenance:enable
mage maintenance:disable
mage cache:flush                     # tous les caches, Varnish compris
mage indexer:status                  # les 14 indexeurs sont en « planifié » : mis à jour par le cron
```

## 8. Jeux de données (poste local seulement)

`docs/jeux-de-donnees/` : `categories.php`, `produits-test.php`, `accueil.php`. Peuplent un environnement de
développement (catégories des maquettes, douze produits de test, incontournables). **Jamais sur le serveur** : le
catalogue réel y est saisi dans l'administration. Mode d'emploi : `docs/jeux-de-donnees/README.md`.

## 9. Cas courants

| Je veux… | Je fais |
|---|---|
| Mettre en ligne un changement | Commit + push sur `main`, puis *Run workflow* avec « Publier » coché ; lire le résultat de `env:check` en fin de journal |
| Vérifier une branche sans la publier | *Run workflow* sur la branche, « Publier » décoché |
| Revenir à la version précédente | `ls releases/`, puis `bascule.sh <version>` (§2) ; restaurer le dump si la base avait évolué |
| Changer les tarifs Mondial Relay | Modifier `Theme/data/tablerates-mondial-relay.csv`, déployer, puis `mage madameaiguille:shipping:import-rates --dry-run` et sans `--dry-run` |
| Vérifier la santé de la boutique | `mage madameaiguille:env:check --serveur --noindex` (sans `--noindex` après l'ouverture) |
| Donner l'administration à Céline | `90-acces-admin.sh <ip-pierre> <ip-céline>`, puis `admin:user:create` (rôle restreint au lot 8) |
| Après une mise à jour d'Ubuntu | Vérifier PHP (`php -v`) et que tous les sites répondent ; relancer `10-depots.sh` si des dépôts sont `.disabled` |
