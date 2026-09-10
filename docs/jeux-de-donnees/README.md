# Jeux de données de test

Scripts **hors code applicatif** : ils ne sont ni des data patches ni déployés. Ils servent à peupler un environnement de développement ou de staging pour la recette. Rien ici ne tourne en production.

| Script | Rôle | Rejouable |
|---|---|---|
| `categories.php` | Arborescence des catégories issue des maquettes (Trousses de toilette > Grandes / Petites trousses, Pochettes à livre, Cotons démaquillants, Petits sacs), actives, dans le menu, ancrées | oui (repérage par clé d'URL et parent ; ne supprime jamais rien, vérifie le chemin enregistré) |
| `produits-test.php` | Douze produits du catalogue Madame Aiguille (photos `maquettes-direction-artistique/brand/p-*.png`) couvrant tous les états du Design System, produits liés, suppression des données d'essai Sneakers / T-Shirts / Jordan | oui (upsert par SKU ; les images ne sont ajoutées qu'à la création) |
| `accueil.php` | Coche quatre produits existants comme Incontournables pour la recette de l'accueil | oui (mise à jour par SKU, aucune création ni suppression) |

Prérequis : `bin/magento setup:upgrade` passé (attribute set « Création »). Ordre : catégories, puis produits.

```bash
php docs/jeux-de-donnees/categories.php
php docs/jeux-de-donnees/produits-test.php
php docs/jeux-de-donnees/accueil.php
cd shop && bin/magento indexer:reindex && bin/magento cache:flush
```

**Règle** : aucun script ne supprime de catégorie. Le 10/09/2026, une catégorie de test enregistrée avec un chemin erroné (`1/2` au lieu de `1/2/<id>`) a été supprimée par script et a emporté toute l'arborescence (Magento supprime tout ce qui commence par le chemin de la catégorie). Pour tester un état vide : désactiver temporairement des produits dans l'admin, ou créer une catégorie dans l'admin et la supprimer depuis l'admin.

## Produits créés

| SKU | Produit | Prix | Stock | État attendu |
|---|---|---|---|---|
| `MA-TRO-ROM` (+ `-P`, `-G`) | Trousse Romantique | 29 € / 34 € | Petit 4 · Grand 0 | configurable, série de 8, taille Grand barrée |
| `MA-TRO-LIN` | Trousse Lin & Aiguille | 26 € | 7 | série de 7 |
| `MA-TRO-PER` | Trousse Lin Perle | 22 € | 0 | **épuisé** |
| `MA-POC-CEL` | Pochette à Livre Céleste | 24 € | 8 | **nouveauté**, série de 8 |
| `MA-POC-ISA` | Pochette à Livre Isabelle | 24 € | 4 | série de 6, 3 photos |
| `MA-POC-ROS` | Pochette Bouton de Rose | 19 € | 0 | **épuisé** |
| `MA-POC-NOM` | Pochettes Nomades | 18 € | 10 | **nouveauté**, série de 10 |
| `MA-COT-CIN` | Cotons Démaquillants | 12 € | 2 | **plus que 2** (série de 10) |
| `MA-COT-DIX` | Cotons Démaquillants — lot de dix | 20 € | 15 | hors série limitée, aucun badge |
| `MA-SAC-AUR` | Sac Aurora Mini | 32 € | 5 | série de 5, nouveauté expirée |
| `MA-SAC-VER` | Sac Aurora Verveine | 34 € | 6 | série de 6 |

La catégorie **Grandes trousses** ne contient que la Trousse Romantique ; **Petits sacs** en contient trois. Pour tester l'état vide, désactiver temporairement un produit ou créer une catégorie sans produit.

Limite connue : les photos font 327 à 566 px de large, loin des 2 000 px recommandés par la charte. Le zoom de la fiche produit sera flou sur ce jeu de test.
