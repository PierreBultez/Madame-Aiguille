---
title: Audit Lighthouse
outline: false
editLink: false
---

# Audit Lighthouse

Mesure de la vitesse, de l'accessibilité, des bonnes pratiques et du référencement de la boutique en ligne, page par page, sur mobile (connexion 4G lente simulée) et sur ordinateur. Chaque note va de 0 à 100 ; **90 et plus, c'est vert**.

<LighthouseRapport />

## Ce qui a changé le 2 novembre 2026

- **Images de l'accueil et de « Nos tissus » en WebP** : 3,6 Mo ramenés à 129 Ko. La photo principale de l'accueil pesait à elle seule 1,47 Mo ; sur mobile, elle s'affichait en 9,6 s, elle s'affiche maintenant en 1,8 s.
- **Logo vectoriel (SVG)** à la place du JPEG de 94 Ko : net sur tous les écrans, 21 Ko.
- **Méta-description des catégories** : composée automatiquement quand le champ est vide (SEO de 92 à 100). Céline peut la remplacer par un texte sur mesure dans *Catalogue › Catégories › Optimisation pour les moteurs de recherche*.

Les points encore relevés ne coûtent presque plus rien au score : le cache « page précédente » est désactivé par Magento sur les pages qui dépendent de la session, et la feuille de styles se charge avant le premier affichage, comme sur tout site Hyvä.

## Rejouer l'audit

Lighthouse tourne en local avec Chrome (le quota gratuit de PageSpeed Insights s'épuise vite). Une page, sur mobile puis sur ordinateur :

```bash
CHROME_PATH=/usr/bin/google-chrome npx -y lighthouse@13.5.0 https://madame-aiguille.fr/ --chrome-flags="--headless=new" --output=json --output-path=accueil-mobile.json
CHROME_PATH=/usr/bin/google-chrome npx -y lighthouse@13.5.0 https://madame-aiguille.fr/ --preset=desktop --chrome-flags="--headless=new" --output=json --output-path=accueil-desktop.json
```

Nommer les rapports `accueil-`, `categorie-` et `fiche-` suivis de `mobile` ou `desktop`, dans un dossier hors du dépôt, puis mettre cette page à jour (l'ancien « après » devient l'« avant ») et republier :

```bash
node docs-site/scripts/resumer-lighthouse.mjs --avant <dossier-precedent> --apres <dossier> --sortie docs/audits/lighthouse.json
```

```bash
docs-site/publier.sh
```

Les notes bougent de quelques points d'un passage à l'autre (réseau, charge du serveur) : seul un écart net et répété compte.
