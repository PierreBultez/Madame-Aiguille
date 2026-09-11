# Madame Aiguille — instructions de travail

Boutique **Magento Open Source 2.4.9** + **Hyvä 1.5.2**. Application dans `shop/`, documentation dans `docs/`.
Interlocuteur : **Pierre**, développeur Laravel/Vue/Tailwind et ancien responsable technique Magento 2. **Réponses en français.**
Cliente finale : **Céline**, qui gère la boutique depuis le back-office sans toucher au code.

## À lire avant de commencer

1. `docs/documentation-theme.md` — ce qui existe, où, et où modifier quoi. **§25 : le rituel de fin de lot.**
2. `docs/plan-de-developpement.md` — lots, état, décisions.
3. `docs/prompts/prompt-lot<N>.md` — le prompt de reprise du lot en cours.
4. `docs/cahier-des-charges-docs-developpement/guide-bonnes-pratiques-hyva.md` — conventions de code.

## Règles non négociables

- **Toute surcharge visuelle vit dans le thème enfant** `shop/app/design/frontend/MadameAiguille/default/` : layouts, `.phtml`, Tailwind/CSS, assets, JavaScript Alpine. `shop/app/code/MadameAiguille/` ne porte que la logique métier : services, ViewModels, data patches, configuration.
- Ne jamais modifier `vendor/`, `pub/static/` ni `web/css/styles.css`. Pas de `tailwind.config.js`.
- Préserver les composants natifs Magento et Hyvä. Retemplater seulement ce qui est nécessaire.
- **Zéro logique métier dans un `.phtml`** : ViewModel ou bloc pour tout calcul. Escaping systématique, `__()` sur tous les textes.
- Toutes les couleurs passent par les tokens. Cibles tactiles de 44 px, focus visible, WCAG 2.1 AA.
- **Annoncer explicitement chaque commande `composer` ou `bin/magento` avant de l'exécuter.**
- Commits atomiques en français. Contrôle visuel à 1440 et 390 px à chaque étape.
- Pour tester un état vide ou une suppression : **l'admin, jamais un script destructif**.

## Terminer un lot

Dérouler **intégralement** le §25 de `docs/documentation-theme.md`, sans qu'on ait à le redemander : recette (écrans **et** administration), documentation (sections du thème, mémo Céline, limites, journal, plan, styleguide), fusion en avance rapide dans `main` et push sur GitHub, puis écriture du prompt de reprise du lot suivant. Ce qui ne peut pas être tenu est dit et consigné comme limite — jamais sauté en silence.
