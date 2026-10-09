# Spike Mondial Relay — 09/10/2026

**Décision de Pierre : intégration maison du widget officiel Mondial Relay** dans le checkout Knockout du fallback Luma. Ni Magentix, ni O'Pickup.

## Ce qui a été testé

Page isolée chargeant jQuery, Leaflet et le widget officiel (`widget.mondialrelay.com/parcelshop-picker/`, version servie : **4.0.11**), code enseigne de test `BDTEST  ` (deux espaces finales, obligatoires).

| Essai | Résultat |
|---|---|
| France, 37800 | 7 points relais réels autour de Saint-Épain (tabacs, lockers 24/7) |
| Belgique, 1000 | 7 points à Bruxelles |
| Luxembourg, 1611 · 2449 · 4011 · 9010 | 7 points à chaque fois. 1009 ne renvoie rien : code postal sans point, pas une erreur |
| Sélection d'un point | `Target` reçoit `FR-087807` ; le callback `OnParcelShopSelected` reçoit `ID`, `Nom`, `Adresse1`, `Adresse2`, `CP`, `Ville`, `Pays`, `Lat`, `Long`, `HoursHtmlTable` |
| Carte | Leaflet + tuiles OpenStreetMap par défaut, **aucune clé Google** |
| Bandeau | « Compte de démonstration » affiché tant que le code est `BDTEST` |

Hôtes contactés par la page : `widget.mondialrelay.com`, `www.mondialrelay.com` (recherche), `*.tile.openstreetmap.org`, plus jQuery et Leaflet.

## Ce qu'il faut de Céline

**Le code enseigne seulement.** La clé privée ne sert qu'aux appels d'API (création d'étiquettes), hors périmètre : Céline crée ses étiquettes à la main sur l'espace pro Mondial Relay. Le développement avance avec `BDTEST` ; le passage en production consiste à saisir le vrai code enseigne dans la configuration.

## Options écartées

| Option | Pourquoi |
|---|---|
| Module officiel Magentix | Prix non affiché ; compatibilité 2.4.9 et fallback Luma non documentée ; périmètre (étiquettes API, domicile 30 kg) très au-delà du besoin |
| O'Pickup d'Owebia (200 €) | Compatibilité non affichée ; syntaxe de configuration Owebia à maintenir, alors que le carrier `tablerate` natif suffit à une grille unique |
| Repli « point relais par email » | Gardé en réserve si le widget venait à casser |

## Risques assumés

- **Le script n'est pas versionnable** : Mondial Relay sert toujours la dernière version 4.x. Une régression de leur côté se verrait en production. Le chargeur fait un appel synchrone à `/version` puis charge `/js?v=…`.
- **Politique CSP** : les hôtes ci-dessus doivent être déclarés dans `csp_whitelist.xml`.
- **RGPD** : les tuiles OpenStreetMap et la recherche Mondial Relay sont chargées depuis des serveurs tiers au moment où la cliente choisit son point → à mentionner dans la page de confidentialité.

## Sources

- Documentation du widget v4.1 : https://storage.mondialrelay.fr/widget-v-411.pdf
- Module Magentix : https://mondialrelay.magentix.fr/fr/magento-2/
- O'Pickup : https://fr.store.owebia.com/magento2-module-opickup-mondial-relay.html
