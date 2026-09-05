# Plan de tests — Site e-commerce "Madame Aiguille"

Document interne — v0.2

> **Mise à jour v0.2 (05/09/2026)** — Ajout des scénarios liés au questionnaire de Céline : frais de port calculés au poids, virement bancaire, remise en main propre, section Actualités, formulaire de contact, newsletter. Les scénarios "pièce unique" sont remplacés par des scénarios "série limitée".

## 1. Objectif

Garantir qu'un client puisse, de bout en bout, découvrir un produit, l'ajouter au panier, passer commande et payer sans erreur — et que Madame Aiguille puisse gérer son catalogue et ses commandes sans blocage. Pour un projet à taille humaine porté par un seul développeur, l'enjeu principal n'est pas la couverture automatisée exhaustive mais la fiabilisation du tunnel d'achat, qui est la partie la plus critique (argent réel, données personnelles).

## 2. Niveaux de tests

| Niveau | Portée | Outillage envisagé | Priorité |
|---|---|---|---|
| Tests manuels de recette | Parcours utilisateur complets | Checklist manuelle (ce document) | **Haute** — indispensable avant chaque mise en prod |
| Tests fonctionnels automatisés | Parcours critiques (ajout panier, checkout, paiement) | Playwright (déjà utilisé dans l'environnement de dev) ou Cypress | Moyenne — à mettre en place progressivement, en priorité sur le tunnel de commande |
| **[NOUVEAU]** Contrôles de données catalogue | Poids et stock renseignés sur tous les produits publiés | Requête SQL ou petit script CLI Magento, à rejouer avant chaque mise en prod | **Haute** — un poids manquant casse le calcul des frais de port sans erreur visible |
| Tests unitaires | Développements spécifiques/sur-mesure uniquement (peu de code custom attendu, Magento/Hyvä gérant l'essentiel nativement) | PHPUnit (natif Magento) | Basse à moyenne, selon le volume de code custom réellement écrit |
| Tests de non-régression | Avant chaque montée de version Magento/Hyvä ou module | Checklist manuelle + tests automatisés existants | Haute au moment des montées de version |

## 3. Scénarios clés — Catalogue & navigation

- [ ] La page d'accueil s'affiche correctement sur mobile et desktop
- [ ] Navigation vers une catégorie, affichage correct des produits (image, prix, nom)
- [ ] Tri des produits (prix croissant/décroissant) fonctionne
- [ ] Recherche : une requête pertinente retourne les bons produits ; une requête sans résultat affiche un message clair
- [ ] Fil d'Ariane cohérent sur les pages catégorie et produit
- [ ] **[NOUVEAU]** La section "Nouveautés/Actualités" est visible en page d'accueil sans défilement excessif, sur mobile comme sur desktop
- [ ] **[NOUVEAU]** Le bloc Nouveautés se remplit automatiquement avec les derniers produits publiés, et **n'affiche pas** de produit épuisé
- [ ] **[NOUVEAU]** Le logo est bien centré dans le header et reste lisible en petite taille (mobile)
- [ ] **[NOUVEAU]** Les blocs "Histoire", "Pourquoi choisir", Instagram et newsletter s'affichent correctement et leurs liens fonctionnent

## 4. Scénarios clés — Fiche produit

- [ ] Affichage correct des photos, description, prix
- [ ] Zoom/galerie photo fonctionnel
- [ ] **[RÉVISÉ]** Sélection de la taille (sur les modèles déclinés en 2 formats) : met à jour le prix, la disponibilité et la photo
- [ ] Produit épuisé : impossible de l'ajouter au panier, message clair affiché
- [ ] **[NOUVEAU]** Série limitée : mention "Série limitée" affichée, et indication du stock restant lorsqu'il est faible ("Plus que 2 exemplaires")
- [ ] **[NOUVEAU]** La quantité commandable est plafonnée au stock réel : impossible de commander 6 exemplaires d'une série où il en reste 3
- [ ] **[NOUVEAU]** Le poids est bien renseigné sur chaque produit du catalogue — **contrôle à passer sur l'intégralité du catalogue avant mise en prod**, un produit sans poids faussant le calcul des frais de port
- [ ] **[NOUVEAU]** Le lien "Une question sur le tissu ou le motif ?" mène au formulaire de contact avec le produit concerné pré-rempli
- [ ] **[RÉVISÉ]** Formulaire de contact : soumission réussie, réception de l'email par Céline avec toutes les informations saisies (y compris la pièce jointe et le produit concerné), et accusé de réception côté visiteur

## 5. Scénarios clés — Panier

- [ ] Ajout, modification de quantité, suppression d'un article
- [ ] Le total du panier se met à jour correctement à chaque action
- [ ] Le panier persiste après rafraîchissement de la page (visiteur non connecté)
- [ ] Le panier d'un visiteur non connecté est bien récupéré/fusionné après connexion à un compte existant
- [ ] Le mini-panier (header) reste synchronisé avec la page panier complète
- [ ] **[NOUVEAU]** L'estimation des frais de port s'affiche dès la page panier et évolue avec le contenu du panier
- [ ] **[NOUVEAU]** Si un franco de port est activé : le message "plus que X € pour la livraison offerte" est correct, et disparaît au franchissement du seuil

## 6. Scénarios clés — Tunnel de commande

- [ ] Commande complète en tant qu'invité (si autorisé)
- [ ] Commande complète en tant que client connecté
- [ ] Sélection d'une adresse existante vs saisie d'une nouvelle adresse
- [ ] Sélection du mode de livraison (Colissimo / Mondial Relay / Chronopost), mise à jour du total avec les frais de port correspondants
- [ ] Sélection d'un point relais Mondial Relay fonctionnelle et adresse du point relais bien enregistrée sur la commande
- [ ] **[NOUVEAU] Frais de port au poids** : vérifier au moins **trois paniers de poids différents** par transporteur (un article léger, plusieurs articles, un panier lourd) et confronter le montant calculé à la grille tarifaire réelle du transporteur
- [ ] **[NOUVEAU]** Panier contenant un produit dont le poids n'est pas renseigné : le calcul ne doit **ni** échouer silencieusement **ni** afficher 0 €
- [ ] **[NOUVEAU] Remise en main propre** : le mode apparaît, les frais de port sont bien à 0 €, et la commande se crée avec le bon statut
- [ ] **[NOUVEAU]** Si la remise en main propre est restreinte géographiquement : elle n'est pas proposée hors de la zone définie
- [ ] Paiement carte en environnement de test (sandbox Stripe) : cas de succès
- [ ] Paiement refusé (carte simulée refusée en sandbox) : message d'erreur clair, commande non créée, panier conservé
- [ ] **[NOUVEAU] Virement bancaire** : la commande se crée en statut "en attente de paiement", le RIB et la référence à rappeler sont affichés à la validation **et** présents dans l'email
- [ ] **[NOUVEAU]** Le stock est bien réservé dès la création d'une commande en attente de virement (un autre visiteur ne peut pas acheter la dernière pièce)
- [ ] **[NOUVEAU]** Passage manuel de la commande en "payée" côté back-office : le client reçoit bien l'email de confirmation de paiement
- [ ] Abandon du tunnel en cours de route : le panier n'est pas perdu au retour sur le site
- [ ] Email de confirmation de commande bien reçu, avec le bon contenu (produits, montants, adresse)
- [ ] Commande visible côté back-office avec le bon statut immédiatement après paiement

## 7. Scénarios clés — Compte client

- [ ] Création de compte
- [ ] Connexion / déconnexion
- [ ] Mot de passe oublié : réception de l'email, réinitialisation fonctionnelle
- [ ] Consultation de l'historique de commandes
- [ ] Ajout/modification/suppression d'une adresse

## 8. Scénarios clés — Back-office (côté Madame Aiguille)

- [ ] Le compte restreint (ACL) de Madame Aiguille n'a accès qu'aux sections prévues (catalogue, ventes, clients en lecture)
- [ ] Création d'un nouveau produit de bout en bout (photos, description, prix, stock) — sans intervention de Pierre
- [ ] Passage d'une commande du statut "en préparation" à "expédiée" déclenche bien l'email correspondant (si prévu)
- [ ] Mise à jour d'un stock se répercute correctement côté site (produit passe en épuisé)
- [ ] **[NOUVEAU]** Céline peut renseigner le poids d'un produit sans aide, et le champ est bien obligatoire
- [ ] **[NOUVEAU]** Céline peut modifier le bloc Actualités et les visuels Instagram depuis le CMS, sans accès aux thèmes ni aux widgets de mise en page
- [ ] **[NOUVEAU]** L'alerte de stock bas est bien reçue par email lorsqu'un produit passe sous le seuil configuré
- [ ] **[NOUVEAU]** Traitement complet d'une commande payée par virement, de bout en bout, par Céline seule

## 9. Tests transverses

### Responsive / compatibilité navigateurs

- [ ] Parcours d'achat complet testé sur mobile (Chrome Android, Safari iOS a minima)
- [ ] Testé sur les navigateurs desktop principaux (Chrome, Firefox, Safari)

### Performance

- [ ] Mesure Lighthouse/PageSpeed sur la page d'accueil, une page catégorie et une page produit avant mise en prod (objectif indicatif : scores verts sur les Core Web Vitals, cohérent avec les gains attendus de Hyvä)
- [ ] Temps de réponse acceptable en conditions réelles (hors cache froid)

### Sécurité (niveau basique, hors audit approfondi)

- [ ] Aucune donnée bancaire ne transite en clair par le serveur applicatif (vérification que le paiement passe bien par le prestataire externe)
- [ ] HTTPS forcé sur tout le site
- [ ] Accès admin protégé (mot de passe fort, idéalement 2FA si disponible côté Magento)
- [ ] Formulaires (contact, compte) protégés contre le spam/les soumissions abusives (captcha natif Magento à activer)
- [ ] **[NOUVEAU]** Pièce jointe du formulaire de contact : types de fichiers et taille limités côté serveur, un fichier non autorisé est rejeté proprement
- [ ] **[NOUVEAU]** Le RIB n'est accessible sur aucune page publique indexable

### Emails (délivrabilité) **[NOUVEAU]**

- [ ] SPF, DKIM et DMARC configurés sur le domaine
- [ ] Les emails transactionnels arrivent en boîte de réception (et non en spam) sur Gmail, Outlook et un webmail français (Orange/Free/SFR)
- [ ] Les emails sont lisibles sur mobile et affichent correctement le logo et les couleurs de la marque

### Contenu et conformité **[NOUVEAU]**

- [ ] Toutes les images produit respectent le même ratio et le même cadrage
- [ ] Les images portent un attribut `alt` pertinent
- [ ] Mentions légales, CGV et politique de confidentialité en place et cohérentes avec le statut fiscal de la cliente
- [ ] Si la cliente est en franchise en base de TVA : les prix s'affichent sans TVA et la mention "TVA non applicable, art. 293 B du CGI" figure sur les factures
- [ ] Si la newsletter est activée : double opt-in fonctionnel et lien de désinscription présent

## 10. Critères de recette avant mise en production

La mise en ligne n'est déclenchée que si :

1. Tous les scénarios des sections 3 à 8 ci-dessus sont passés avec succès
2. Au moins un paiement réel de test (petit montant) a été effectué et remboursé, en conditions de production
3. Les emails transactionnels sont vérifiés en réception réelle (pas seulement en sandbox)
4. Madame Aiguille a testé elle-même la création d'un produit et le traitement d'une commande dans le back-office, sans assistance, et confirme que c'est utilisable pour elle
5. Les pages légales obligatoires (CGV, mentions légales, politique de confidentialité) sont en place
6. **[NOUVEAU]** Le paramétrage TVA correspond au statut fiscal réel de la cliente
7. **[NOUVEAU]** Un virement de test a été reçu et rapproché manuellement de bout en bout
8. **[NOUVEAU]** Tous les produits publiés ont un poids renseigné

## 11. Points ouverts

1. Mise en place ou non de tests automatisés (Playwright) sur le tunnel de commande, et à partir de quel moment du projet (dès le départ vs après le lancement)
2. Environnement de recette dédié pour dérouler cette checklist avant chaque mise en prod (cf. document d'architecture, §3)
3. **[NOUVEAU]** Comment tester les grilles de frais de port sans compte transporteur réel : reconstituer les grilles publiques dans un jeu de données de test
4. **[NOUVEAU]** Délai d'expiration d'une commande en attente de virement — à définir avant de pouvoir écrire le scénario de test correspondant
