# Reprise du développement — lot 8a Préproduction, emails et validation Mollie

Tu reprends **Madame Aiguille**, boutique Magento Open Source 2.4.9 avec Hyvä 1.5.2. Réponds en français à Pierre, développeur Laravel/Vue/Tailwind et ancien responsable technique Magento 2. Céline doit pouvoir exploiter la boutique depuis l’administration.

## État de départ à vérifier

- Dépôt : `/home/pierre/Documents/aiguille`, application dans `shop/`, documentation dans `docs/`.
- **Lot 6b fusionné en avance rapide et poussé** : `main` et `lot-6b-habillage-tunnel` contiennent `92a96f6` (recette et documentation). Le commit qui contient ce prompt vient ensuite. Vérifier les références distantes, sans supposer que HEAD est resté identique.
- Lots livrés : 1, 2, 6a, 3, 4, 7, 5 et 6b. Ordre restant : **8a → 8**. 8a avait été différé faute de domaine et de serveur.
- Référence technique de fin de 6b : **106 tests, 282 assertions** ; PHPCS Magento2 sans erreur sur les 18 PHP/PHTML touchés ; DI et deux builds réussis.
- Recette locale : `docs/recettes/lot-6b.md` et ses captures. Commandes de retrait `000000021` / `000000022` annulées via l’administration, aucune réservation restante. Les anciennes commandes de Pierre ne sont pas toutes jetables.
- Branche distincte **`traduction-fr`**, laissée intacte (`6690881` au démarrage du 6b). Le paquet français est installé dans le `vendor` local, mais ses changements Composer ne sont pas dans `main`. Arbitrage requis avant installation sur serveur neuf.

Commence par `git status`, `git log --oneline --decorate -n 20`, les références distantes, puis lis :

1. `AGENTS.md` et `docs/documentation-theme.md` **§25**, rituel de fin de lot ;
2. cette documentation : §2 (build), §14 (fallback), §22 (emails), **§23** (limites et responsables), §24 (mémo), §26 (livraison/paiement), **§27** (tunnel et pièges du 6b) ;
3. `docs/plan-de-developpement.md`, v2.8, état après 6b et lot 8 ;
4. `docs/recettes/lot-6b.md`, `docs/brief-call-celine-2026-09-11.md`, les documents d’architecture et de tests dans `docs/cahier-des-charges-docs-developpement/` ;
5. `README.md` pour les fontes et les builds.

Créer **`lot-8a-preproduction` depuis `main` à jour**, sans préfixe `codex/`, conformément à la préférence de Pierre. Préserver les travaux existants.

## Décisions acquises

- Checkout natif à **deux étapes**, invité autorisé. Le fallback est `frontend/MadameAiguille/checkout`, parent Luma, LESS et RequireJS. Panier, compte, succès et échec restent dans le thème Hyvä `MadameAiguille/default`.
- Habillage livré, récapitulatif inspiré des maquettes. Téléphone obligatoire, **CGV natives obligatoires**, newsletter facultative **décochée**. Aucun nouveau champ « Message pour Céline » ; message cadeau natif du panier conservé.
- Newsletter : consentement explicite dans `payment.additional_data`, stockage natif du paiement, inscription via le service Magento à la création de commande ; paramètres invité / compte / confirmation respectés. Une case décochée ne désinscrit personne ; une panne newsletter ne fait pas échouer la commande.
- Mondial Relay en point relais, pays FR / MC / BE / LU, retrait sur rendez-vous **payé sur place**, cadeau 2 € configurable, franco point relais à **60 €** porté par le seul seuil du panier. Aucune TVA facturée (franchise en base), mention existante à conserver.
- Moyens Mollie actuels **provisoires**, jugés pertinents par Pierre mais à valider avec Céline. Ne pas revenir automatiquement à la carte seule et ne pas modifier leur configuration sans cet arbitrage. Tous ne sont pas affichables dans tous les contextes navigateur / pays.
- Les confirmations utilisent la dernière commande de la session et les vrais ViewModels ; aucun identifiant de commande arbitraire lu dans l’URL. L’échec ne promet pas l’absence de débit.

## Informations à demander au démarrage

Regrouper seulement ce qui manque, puis avancer sur l’audit et la préparation indépendants des réponses :

1. Domaine canonique, domaine de préproduction, hébergeur / serveur cible, accès déjà disponibles, gestion DNS et mode de déploiement souhaité. Identifier clairement la cible avant toute mutation distante.
2. SMTP et adresses de réception de recette, accès Mollie, disponibilité des clés reCAPTCHA liées au domaine. Les secrets se saisissent dans les interfaces ou le stockage sécurisé, jamais dans un fichier versionné, un journal ou une capture.
3. Code enseigne et tarifs Mondial Relay, adresse précise de retrait, méthodes Mollie validées avec Céline, état de validation des contenus juridiques.
4. Arbitrage de la branche `traduction-fr` avant provisionnement. Ne pas fusionner implicitement son historique ni recopier son `vendor` local.

Le paiement réel de validation doit être effectué par Pierre dans le navigateur ; préparer un parcours concret et lui passer la main à l’étape financière. Ne pas inventer de montant, d’identité ou d’autorisation de remboursement.

## Périmètre du lot 8a

1. **Préparer une installation reproductible** : le paquet `community-engineering/language-fr_fr` fait partie de `composer.lock` (§28) ; déployer les fichiers statiques en `fr_FR` pour la vitrine **et** l’administration, et passer les comptes admin en français. dépendances verrouillées et accès Hyvä, exigences de la version Magento effectivement installée, configuration d’environnement et procédure de déploiement / retour arrière. Provisionner les WOFF2 **originaux** dans les deux thèmes conformément au README ; licences déjà versionnées, fontes ignorées par Git. Aucun subsetting ou conversion.
2. **Préproduction HTTPS** sur la cible convenue : URLs, DNS/certificat, cron et services nécessaires, configuration des secrets, isolation des emails de recette et absence d’indexation. Décrire la sauvegarde des données et médias avant toute migration. L’ouverture commerciale et la CI/CD complète restent au lot 8 sauf demande explicite.
3. **Livraison** : rejouer l’import tarifaire `madameaiguille:shipping:import-rates` et le contrôle `madameaiguille:catalog:check-weight`. Vérifier les réglages effectifs, pas seulement `core_config_data`. Conserver l’avertissement `BDTEST` tant que le code réel manque ; aucune dissimulation CSS.
4. **SMTP et délivrabilité** : expéditeurs, SPF/DKIM/DMARC selon le fournisseur choisi, réception effective de confirmation de commande, paiement reçu, retrait prêt, expédition, création de compte et contact avec pièce jointe. Tracer ce qui est envoyé et ce qui est reçu, sans exposer de secret.
5. **Newsletter** : opt-in invité et connecté, email de confirmation puis confirmation effective, non-inscription sans consentement, respect du refus des invités et des abonnées existantes. Vérifier l’administration et les journaux d’erreur.
6. **reCAPTCHA natif** : le formulaire Hyvä après commande est prérempli et possède le mécanisme, mais `recaptcha_frontend/type_for/customer_create` est non configuré en local. Activer avec les clés / domaines appropriés ; recetter refus serveur et soumission normale. Prévoir aussi le contact selon la configuration retenue. Ne pas contourner le CAPTCHA.
7. **Mollie de bout en bout** : mode test, webhook joignable, passage relais / cadeau / CGV, retour succès réel et confirmation du point relais, événements et statuts Magento, emails effectivement reçus. Rejouer annulation / échec et **restauration native du panier**, jamais simulée par le thème. Réactiver `payment/mollie_general/use_webhooks` sur la préproduction ; une protection HTTP doit laisser accessible la route exacte de webhook, vérifiée dans le module installé. Puis préparer le paiement réel de validation pour Pierre et les clés de production lorsque le compte le permet.
8. **Pièces de vente avec cadeau** : commande dédiée de recette, facture et avoir, ligne / montants / mention de TVA. Ne pas rembourser une ancienne commande pour obtenir un écran. Identifier à l’avance les opérations financières à faire par Pierre.
9. **Recette écran et administration** : 1440 et 390 px sur les parcours concernés, contrôle de chaque section admin touchée, styles réellement servis, tests unitaires et PHPCS des changements. Consigner chaque résultat et chaque dépendance non satisfaite.

## Règles et pièges à conserver

- Toute surcharge visuelle dans le thème enfant approprié ; logique métier dans les modules. Aucun changement manuel de `vendor/`, `pub/static/` ou du CSS compilé. Aucun `tailwind.config.js`.
- **Annoncer chaque commande `composer` ou `bin/magento` avant exécution.** Aucune extension tierce sans accord explicite. La génération d’assets **par Magento** a été autorisée par Pierre au 6b ; elle ne permet pas d’éditer les fichiers générés.
- Deux builds : Tailwind pour Hyvä, déploiement statique Magento pour `MadameAiguille/checkout fr_FR`. Après modification DI, compiler DI.
- `setup:static-content:deploy` peut garder un ancien CSS. Utiliser le service Magento `DeployStaticFile` et son filesystem pour purger uniquement les assets / caches LESS du thème checkout avant régénération, puis comparer servi et compilé. Référence locale finale : Hyvä 205 564 octets ; Luma mobile 748 490, ordinateur 158 330. Ces tailles évolueront légitimement avec les changements.
- Le HTML ou une page de recette peut aussi rester en cache : URL différente pour vérifier la nouvelle version. Dans le navigateur, vérifier les **dimensions effectives dans le DOM** avant de légender une capture. Au dernier contrôle 6b, le viewport s’appliquait mal ; un cadre de même origine aux dimensions explicites a permis de contrôler le vrai tunnel, puis le fichier temporaire a été supprimé.
- **Leaflet** : le widget officiel réinjecte le script si son URL diffère de celle déjà chargée. RequireJS utilise désormais sa même URL **non versionnée** (`unpkg.com/leaflet/dist/leaflet`) pour éviter « Mismatched anonymous define » et la disparition de l’adresse / du mode dans le récapitulatif. Vérifier une seule inclusion, pas seulement la carte. Recetter toute évolution distante. Horaires / Photo de l’infobulle restent une limite CSP assumée ; ne pas affaiblir la CSP pour la masquer.
- Les montants à Livraison sont estimatifs, parfois ceux du choix précédent jusqu’à « Continuer vers le paiement ». Le texte l’indique ; aucun calcul métier JavaScript ajouté.
- Luma garde ses **24 colonnes**. Les traductions du checkout sont dans son dictionnaire propre. Clés exactes requises ; une chaîne avec deux-points dans une directive Knockout peut casser son prétraitement. Les chaînes PHP du tunnel (ex. « Adresse : ligne 1 ») se traduisent dans le dictionnaire du thème **Hyvä** `default` : la traduction est chargée avant la bascule du fallback (§28).
- `config:show` peut renvoyer du vide sur un défaut : utiliser `ScopeConfigInterface`. Une section admin peut planter seule : l’ouvrir réellement.
- Une affectation de champ navigateur peut manquer les événements Knockout : saisie clavier puis sortie du champ. Le stockage `checkout-data` peut préremplir une ancienne adresse.
- Les commandes réservent du stock et des rendez-vous. **Annulation dans l’administration**, jamais par script destructif ; anciennes commandes payées / livrées à préserver. État vide ou suppression : administration également.

## Ce qui dépend encore de Pierre / Céline

Sans domaine, serveur, SMTP ou compte validé, préparer les fichiers et la procédure utiles mais ne pas déclarer le déploiement ou les paiements recettés. Chaque manque doit avoir un responsable et une échéance dans §23. Contenus juridiques, tarifs, code enseigne et adresse réelle ne s’inventent pas. Fusion de panier à la connexion, session expirée et recette transversale complète restent au lot 8 sauf opportunité de les couvrir ici.

Terminer par **tout le §25** : recette écrans et administration, sections de documentation avec chemins réels, mémo Céline, limites, journal par commit, plan et styleguide si nécessaire ; fusion en avance rapide dans `main` et push des deux branches ; **puis prompt de reprise du lot 8**. Commencer par un plan court et l’audit vérifiable, sans redemander les décisions déjà acquises.
