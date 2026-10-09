# Correction de la largeur du hero — 09/10/2026

Signalement de Pierre après le lot 6b : le hero de l’accueil s’étire sur toute la largeur d’un grand écran.

## Cause et correction

Le conteneur `.home-sections` utilise `100vw` pour les fonds des sections de l’accueil. Le hero n’avait aucun plafond propre : à 2560 px, il mesurait 2545 px (hors barre de défilement).

Le commit `8316318` ajoute le centrage et `max-inline-size: var(--breakpoint-2xl)` à `.home-hero` dans `shop/app/design/frontend/MadameAiguille/default/web/tailwind/theme/page-home.css`. Le plafond réutilise le token de 1440 px du thème. Contenu CMS, configuration admin et autres sections inchangés ; aucun nouveau réglage pour Céline.

## Vérification

Vrai accueil local, dimensions lues dans le navigateur intégré :

| Fenêtre | Hero | Résultat |
|---|---|---|
| 2560 px | 1440 px, position x = 552,5 px | Centré comme le conteneur principal, avec marges latérales |
| 1440 px | 1425 px hors barre de défilement | Deux colonnes, image haute de 480 px, aucun débordement |
| 390 px | 375 px hors barre de défilement | Empilement conservé, image haute de 240 px, aucun débordement |

Captures : `correction-hero/accueil-2560.jpg`, `accueil-1440.jpg`, `accueil-390.jpg`.

- Build Tailwind réussi ; CSS compilé et servi identiques (`cmp`), **205 644 octets**.
- Version d’assets renouvelée par le service de stockage Magento, caches `full_page` / `block_html` nettoyés ; aucun fichier généré édité manuellement.
- `git diff --check` propre. Correction CSS seule : aucun changement PHP ou écran d’administration, pas de nouveau test unitaire nécessaire.
- Le lot suivant reste **8a** ; son prompt de reprise reste applicable.
