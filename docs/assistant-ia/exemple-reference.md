# Exemple de référence de l'assistant IA

> **Statut : définitif depuis le 07/11/2026.** L'exemple est la description longue du **cabas Adèle (ADE-FU)**,
> retravaillée par Pierre et Céline et saisie en production. Son HTML nettoyé est dans
> `shop/app/code/MadameAiguille/AiAssistant/prompt/exemple-reference.html`, ajouté tel quel à la consigne.
> Le brouillon précédent (« Le coffret tendresse », 06/11/2026) est abandonné.

## Ce qui a changé par rapport à la fiche en ligne

- **La voix** : à la demande de Pierre, Céline parle à la première personne. Les tournures « Contactez-nous »,
  « nous vous dirons », « Madame Aiguille confectionne… », « coupé, assemblé et fini par Madame Aiguille » sont
  passées au « je » (« Écrivez-moi », « je coupe, j'assemble et je finis chaque cabas moi-même »).
- **Le HTML** : attributs de l'éditeur (`data-lfile-container`, `data-pm-slice`, `colspan`, `rowspan`), `colgroup`
  et paragraphes dans les cellules du tableau retirés. Le reste est identique.

## Ce que l'exemple fixe

La longueur (environ 1 100 mots), l'ordre des sections et leur forme : accroche, récit en `h3`, sections `h2`
(coloris, encadré de série limitée, atouts, usage, cadeau, tableau des caractéristiques, entretien, questions
fréquentes, atelier), invitation finale en gras. La consigne (`prompt/consigne.md`) interdit au modèle d'en reprendre
une phrase **ou un fait** : coloris, dimensions et entretien sont propres à ce cabas.

## Point à vérifier sur la fiche ADE-FU

La fiche s'appelle « Le cabas Adèle **fuchsia** », mais sa description liste trois coloris (Terracotta, Framboise,
Vert d'eau). Si chaque coloris est une fiche à part, la section « Les coloris disponibles » est à revoir.
