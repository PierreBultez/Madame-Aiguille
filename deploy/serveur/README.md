# Provisionnement du VPS

VPS OVH, Ubuntu 26.04 LTS, partagé avec d'autres sites de Pierre. `<vps>` désigne l'accès SSH du serveur (hôte, port et
utilisateur restent hors du dépôt, qui est public).
Chaque script est idempotent, lisible avant exécution, sans aucun secret, et se lance depuis le poste de Pierre :

```bash
ssh <vps> 'bash -s' < deploy/serveur/<script>.sh
```

| Script | Rôle | État |
|---|---|---|
| `10-depots.sh` | Dépôts APT MariaDB 12.3, OpenSearch 3.x, RabbitMQ (deb822) | à jouer |
| `20-mariadb.sh` | Sauvegarde complète puis MariaDB 12.3 et `mariadb-upgrade` | à jouer |
| `30-php.sh` | PHP 8.5 natif et ses extensions, réglages repris de 8.4 | appliqué le 10/10/2026 |
| `40-opensearch.sh` | OpenSearch 3, nœud unique local, tas 2 Go | appliqué le 09/10/2026 |
| `50-varnish.sh` | Varnish local devant Magento, 1 Go | appliqué le 09/10/2026 |
| `60-valkey.sh` | Valkey à la place de Redis (sites Laravel, 6379) + cache (6380) et sessions (6381) Magento | à jouer |

Ports locaux après provisionnement : MariaDB 3306, OpenSearch 9200, Valkey 6379 / 6380 / 6381, Varnish 6081 (admin 6082),
RabbitMQ 5672. Aucun n'est ouvert par le pare-feu, qui n'autorise que SSH, 80, 443 et les services des autres sites.

Les sauvegardes faites par ces scripts vont dans `/home/pierre/sauvegardes/`, sur le serveur uniquement : elles contiennent
les données des autres sites.
