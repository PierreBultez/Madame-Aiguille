# Provisionnement et déploiement sur le VPS

VPS OVH, Ubuntu 26.04 LTS, partagé avec d'autres sites de Pierre. `<vps>` désigne l'accès SSH du serveur (hôte, port et
utilisateur restent hors du dépôt, qui est public). Chaque script est idempotent, lisible avant exécution, sans aucun
secret, et se lance depuis le poste de Pierre.

Scripts autonomes (10 à 60) :

```bash
ssh <vps> 'bash -s' < deploy/serveur/<script>.sh
```

Scripts qui ont besoin des gabarits et de `deploy/bascule.sh` (70 et 80) : le dossier `deploy/` est envoyé dans un
répertoire temporaire du serveur, puis supprimé.

```bash
tar -C deploy -cz . | ssh <vps> 'D=$(mktemp -d) && tar -C "$D" -xz && bash "$D/serveur/70-hebergement.sh"; rm -rf "$D"'
```

| Script | Rôle | État |
|---|---|---|
| `10-depots.sh` | Dépôts APT MariaDB 12.3, OpenSearch 3.x, RabbitMQ (deb822) | appliqué le 10/10/2026 |
| `20-mariadb.sh` | Sauvegarde complète puis MariaDB 12.3 et `mariadb-upgrade` | appliqué le 10/10/2026 |
| `30-php.sh` | PHP 8.5 natif et ses extensions, réglages repris de 8.4 | appliqué le 10/10/2026 |
| `40-opensearch.sh` | OpenSearch 3, nœud unique local, tas 2 Go | appliqué le 09/10/2026 |
| `50-varnish.sh` | Varnish 7 local devant Magento, 1 Go | appliqué le 09/10/2026 |
| `60-valkey.sh` | Valkey à la place de Redis (sites Laravel, 6379) + cache (6380) et sessions (6381) Magento | appliqué le 10/10/2026 |
| `70-hebergement.sh` | Utilisateur `madame-aiguille`, arborescence, pool PHP-FPM, réglages MariaDB, certificat `madame-aiguille.fr` + `www`, rotation des journaux | à jouer |
| `80-installer-magento.sh <version>` | Installation neuve dans une version envoyée par GitHub Actions, réglages du serveur, VCL, cron, mise en ligne, activation nginx | à jouer une fois |

Ports locaux : MariaDB 3306, OpenSearch 9200, Valkey 6379 / 6380 / 6381, Varnish 6081 (admin 6082), nginx backend 8080,
RabbitMQ 5672. Aucun n'est ouvert par le pare-feu, qui n'autorise que SSH, 80, 443 et les services des autres sites.

## Arborescence de la boutique

```
/var/www/madame-aiguille/
├── releases/<AAAAMMJJ-HHMMSS>-<commit>/   versions construites par GitHub Actions (cinq conservées)
├── shared/app/etc/env.php                 secrets du serveur, 0600, utilisateur madame-aiguille
├── shared/var/                            journaux, caches fichiers, privé
├── shared/pub/media/                      médias
├── shared/sauvegardes/                    dumps pris avant chaque setup:upgrade
└── current -> releases/…                  version en ligne (nginx, cron)
```

Le code appartient à Pierre et n'est que lisible par le groupe `madame-aiguille` : PHP-FPM, le cron et les commandes
Magento tournent sous cet utilisateur système sans connexion (`sudo -u madame-aiguille php …/bin/magento`).

## Déployer, revenir en arrière

- **Déployer** : GitHub › Actions › *Déploiement* › *Run workflow*, case « Publier la version sur le serveur » cochée.
- **Revenir à une version précédente** : `ssh <vps> 'bash -s -- <version>' < deploy/bascule.sh`. Si la version
  récente avait lancé `setup:upgrade`, restaurer aussi `shared/sauvegardes/avant-<version>.sql.gz`.
- En cas d'échec pendant `setup:upgrade`, le site reste en maintenance sur l'ancienne version : corriger, puis
  relancer la bascule ou `maintenance:disable`.

Les sauvegardes faites par les scripts 20 et 70 à 80 restent sur le serveur (`/home/pierre/sauvegardes/`,
`shared/sauvegardes/`) : elles contiennent des données personnelles.
