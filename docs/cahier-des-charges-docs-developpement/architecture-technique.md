# Architecture technique — Site e-commerce "Madame Aiguille"

Document interne — v0.2

> **Mise à jour v0.2 (05/09/2026)** — Intégration du questionnaire préliminaire de Céline. Impacts techniques : modélisation catalogue revue (séries limitées au lieu de pièces uniques), attribut poids obligatoire pour le calcul automatique des frais de port, ajout du virement bancaire et de la remise en main propre, nom de domaine quasi arrêté, identité visuelle disponible (cf. `charte-graphique.md`).

## 1. Choix technologiques (rappel et justification)

| Composant | Choix retenu | Justification résumée |
|---|---|---|
| Backend e-commerce | Magento Open Source | Robustesse, richesse fonctionnelle native (catalogue, panier, checkout, taxes, back-office), expertise déjà maîtrisée par Pierre |
| Frontend | Thème **Hyvä** | Remplace Luma (jugé daté), 100% rendu serveur (PHTML), stack Tailwind CSS + Alpine.js déjà maîtrisée par Pierre côté Laravel, gratuit et open source depuis la v1.4.0 (nov. 2025), pas de reconstruction du panier/checkout/compte contrairement à une approche headless |
| Architecture front | **Non découplée** (pas de headless GraphQL) | Décision actée : PWA Studio (l'outil officiel headless d'Adobe) n'est plus maintenu depuis 2024 ; Hyvä répond au besoin d'un front moderne sans le coût/risque d'un rebuild complet côté panier/checkout/SEO/SSR |
| Base de données | MariaDB | Déjà en place sur le serveur de Pierre |
| Cache | Valkey + Varnish | Cache applicatif Magento + gestion des sessions |
| Recherche | Opensearch | Moteur de recherche catalogue natif Magento (obligatoire à partir de Magento 2.4) |
| File de messages | RabbitMQ | Traitement asynchrone Magento (indexation, emails, imports) — déjà disponible côté infra |
| Serveur web | Nginx + PHP | Déjà en place |
| Certificats SSL | Certbot | Déjà en place |
| Dépendances | Composer | Gestion des dépendances Magento/Hyvä |
| CI/CD | GitHub Actions | Pipeline de déploiement déjà utilisé par Pierre sur ses autres projets |

## 2. Schéma des composants

```
                     ┌─────────────────────────────┐
                     │        Visiteur / client      │
                     └───────────────┬───────────────┘
                                      │ HTTPS
                                      ▼
                     ┌─────────────────────────────┐
                     │   Nginx (reverse proxy/SSL)  │
                     │        Certbot (TLS)          │
                     └───────────────┬───────────────┘
                                      ▼
                     ┌─────────────────────────────┐
                     │   Magento Open Source (PHP)   │
                     │   + Thème Hyvä (PHTML/         │
                     │     Tailwind CSS/Alpine.js)    │
                     └──┬───────┬────────┬───────────┘
                        │       │        │
             ┌──────────┘   ┌───┘    ┌───┴──────────┐
             ▼              ▼        ▼              ▼
        ┌─────────┐   ┌──────────┐ ┌────────────┐ ┌─────────┐
        │  MySQL/ │   │Valkey+   | |            | |         |
        |         |   |Varnish   │ │Opensearch  │ │RabbitMQ │
        │ MariaDB │   │(cache/   │ │ (recherche/ │ │ (files  │
        │(données)│   │ session) │ │  indexation)│ │  asynch)│
        └─────────┘   └──────────┘ └────────────┘ └─────────┘
```

Le front n'est pas une application séparée : il n'y a qu'un seul déploiement applicatif (Magento + thème Hyvä), ce qui simplifie le pipeline CI/CD par rapport à une architecture headless (pas de build/déploiement d'une app JS distincte, pas de gestion CORS entre deux domaines/services).

## 3. Environnements

> **[À DÉFINIR]** Nombre et nature des environnements à confirmer selon les moyens disponibles sur le serveur dédié :

- **Local** : environnement de dev sur la machine Ubuntu de Pierre (Docker recommandé pour isoler la stack Magento du reste du serveur de dev — à trancher : Docker vs installation native)
- **Staging/recette** : souhaitable pour valider les évolutions avant mise en prod et permettre à la cliente de tester avant publication — à mettre en place sur le serveur dédié, sous-domaine dédié (ex. `staging.madame-aiguille.fr`), protégé par auth HTTP basique pour ne pas être indexé
- **Production** : serveur dédié Ubuntu de Pierre

## 4. Thème Hyvä — points d'implémentation

- Installation via Composer avec clé de licence gratuite (compte hyva.io, jusqu'à 5 clés pour un profil indépendant)
- Build CSS Tailwind via un toolchain Node dédié (étape de build uniquement, pas de runtime Node en production)
- Personnalisation du thème par défaut Hyvä (volontairement minimal) pour coller à l'identité visuelle de Madame Aiguille
- **Vérification de compatibilité Hyvä** pour chaque extension tierce envisagée avant de l'installer (moyens de paiement, avis clients, etc.) — les extensions écrites pour Luma (Knockout/RequireJS) nécessitent un module de compatibilité Hyvä pour s'afficher correctement
- **[NOUVEAU] Personnalisation du thème à partir de l'identité fournie** : la palette et les intentions typographiques de la cliente sont documentées dans `charte-graphique.md`. Concrètement — Hyvä 1.5 étant sous **Tailwind v4** — cela se traduit par des tokens de couleur dans le `hyva.config.json` du thème enfant (en oklch) et des familles typographiques dans le `@theme` de `tailwind-source.css`, plutôt que par des surcharges CSS dispersées. Il n'y a pas de `tailwind.config.js`
- **[NOUVEAU] Header centré** : la maquette place le logo au centre du header, là où Hyvä le place à gauche par défaut → surcharge du template `Magento_Theme::html/header.phtml` à prévoir
- **[RÉVISÉ] Modélisation du catalogue** — la v0.1 tablait sur des attributs `tissu`/`couleur`/`taille` et un booléen "pièce unique". Le questionnaire décrit des **séries limitées de 5 à 10 pièces** avec au maximum **2 tailles** sur certains modèles. Modélisation retenue :

| Attribut | Type | Usage |
|---|---|---|
| `taille` | Attribut de configuration (dropdown, ~2 valeurs) | **Seul axe de variante en v1.** Utilisé uniquement sur les modèles déclinés en 2 formats |
| `serie_limitee` | Booléen | Déclenche l'affichage de la mention "Série limitée" en fiche produit et en vignette |
| `taille_serie` | Entier (optionnel) | Nombre de pièces de la série, pour l'affichage "Série limitée — 8 pièces" |
| `weight` | Natif Magento | **Obligatoire sur tous les produits** : conditionne le calcul automatique des frais de port (cf. §5). Un produit sans poids fausse silencieusement le calcul → à rendre requis côté attribute set et à contrôler en recette |

  Pas d'attribut `tissu` ni `couleur` configurable en v1 : le choix de tissu passe par le formulaire de contact (cf. spécification fonctionnelle §2.2). Conséquence bénéfique : très peu de produits configurables, donc une charge de saisie faible pour la cliente, qui a peu de temps à consacrer au back-office

## 5. Modules/extensions Magento envisagés **[RÉVISÉ]**

| Besoin | Solution envisagée | Statut |
|---|---|---|
| Paiement carte bancaire et virement SEPA | Module **Mollie** (`mollie/magento2`, compatibilité Hyvä officielle) | **Retenu le 10/09/2026** — déjà installé et activé ; à finaliser côté compte marchand Mollie |
| **[NOUVEAU]** Paiement par virement | Natif Magento — `Magento_OfflinePayments` / *Bank Transfer Payment* | **Retenu** — aucun développement, mais un process manuel de rapprochement à cadrer avec la cliente |
| **[NOUVEAU]** Remise en main propre | Natif Magento — *Cash On Delivery* ou méthode d'expédition à 0 € + paiement hors ligne | **Retenu** — à restreindre par code postal si la cliente ne veut pas la proposer partout |
| **[RÉVISÉ]** Frais de port au poids | **Table rates Magento en condition `Weight vs. Destination`**, une grille par transporteur | **Retenu** — répond au souhait de calcul automatique selon le poids **sans API transporteur temps réel**. Import des grilles par CSV |
| Livraison Colissimo | Table rates (grille de poids) | À configurer |
| Livraison Mondial Relay | Module de sélection de point relais (widget carte/liste) | **Point le plus coûteux du lot.** Nécessite un module tiers ; vérifier impérativement la **compatibilité Hyvä** avant de s'engager, le widget s'insérant dans le checkout |
| Livraison Chronopost | Table rates (grille de poids) | **Confirmé par la cliente** (n'était qu'optionnel en v0.1) |
| **[NOUVEAU]** Formulaire de contact avec pièce jointe et champ "produit concerné" | Formulaire natif Magento étendu, ou petit module custom | Le formulaire natif ne gère ni pièce jointe ni champ personnalisé → un module custom léger est probablement plus simple qu'une extension tierce à auditer |
| **[NOUVEAU]** Alerte de stock bas | Natif Magento (notification e-mail de seuil) | Recommandé : compense le rythme de mise à jour mensuel de la cliente |
| **[NOUVEAU]** Newsletter | Natif Magento (`Magento_Newsletter`) | Sous réserve de validation par la cliente ; prévoir le double opt-in |
| **[NOUVEAU]** Bloc Nouveautés en page d'accueil | Widget natif "New Products" + blocs CMS | Alimenté automatiquement par la date de création produit, pour éviter que le bloc se périme |
| Emails transactionnels | Natif Magento | Templates à personnaliser aux couleurs de la marque |
| Recherche | Opensearch natif Magento 2.4+ | Déjà couvert par l'infra existante |
| Avis clients (si activé) | Module natif Magento "Product Reviews" | Suffisant a priori, pas de solution tierce nécessaire |

> **Règle générale** : chaque extension tierce doit faire l'objet d'une vérification de compatibilité Hyvä **avant** installation. Les extensions écrites pour Luma (Knockout/RequireJS) nécessitent un module de compatibilité pour s'afficher correctement.

## 6. Sécurité

- Mise à jour régulière de Magento et de ses dépendances (composer) — point de vigilance particulier compte tenu du précédent incident de sécurité sur l'infrastructure de Pierre (compromission via extension WordPress vulnérable)
- HTTPS obligatoire (Certbot déjà en place)
- Aucune donnée bancaire ne transite ni n'est stockée côté serveur Magento (délégué au prestataire de paiement — Mollie)
- Sauvegardes régulières de la base de données et des médias (fréquence à définir)
- Accès admin Magento restreint par ACL (cf. spécification fonctionnelle §6), authentification forte recommandée pour le compte administrateur technique de Pierre
- **[NOUVEAU]** Captcha natif Magento à activer sur le formulaire de contact et la création de compte : le formulaire de contact accepte une pièce jointe, ce qui en fait une cible d'abus — restreindre les types de fichiers et la taille côté serveur
- **[NOUVEAU]** Le RIB affiché lors d'un paiement par virement ne doit apparaître qu'après validation de la commande, jamais sur une page publique indexable

## 7. Déploiement (CI/CD)

Pipeline GitHub Actions à adapter du pattern existant de Pierre :

1. Déclenchement sur push/merge vers la branche de déploiement (à définir : `main` ou `production`)
2. Installation des dépendances (`composer install`)
3. Build des assets front (Tailwind/Hyvä)
4. Exécution des tests automatisés disponibles (cf. plan de tests)
5. Déploiement vers le serveur (via SSH/rsync ou stratégie équivalente à celle déjà utilisée par Pierre sur ses projets Laravel)
6. Exécution des commandes Magento post-déploiement (`setup:upgrade`, `cache:flush`, réindexation)

> **[À DÉFINIR]** Stratégie de déploiement précise (zero-downtime ou non), et gestion des migrations de données Magento en production.

## 8. Performance

- Cache Valkey pour le cache applicatif Magento et les sessions
- Full Page Cache Magento natif (à activer en production)
- Hyvä réduit fortement le poids des pages par rapport à Luma (suppression de RequireJS/Knockout, CSS Tailwind purgé) — un gain de performance significatif est attendu par rapport à une boutique Magento standard
- Indexation Opensearch à surveiller (temps d'indexation du catalogue, à planifier hors heures de forte fréquentation si le catalogue grossit)

## 9. Nom de domaine et DNS **[NOUVEAU]**

- Choix quasi arrêté : **`madame-aiguille.fr`** (recommandation de Pierre, acceptée par la cliente qui hésitait avec `madameaiguille.fr`)
- Recommandation : **réserver les deux** et rediriger `madameaiguille.fr` en 301 vers le domaine principal, pour couvrir les fautes de frappe à l'oral comme à l'écrit
- Vérifier la disponibilité avant de figer le choix
- Prévoir un sous-domaine `staging.` pour l'environnement de recette (cf. §3)
- Configurer les enregistrements DNS et surtout **SPF/DKIM/DMARC** : le site enverra des emails transactionnels (confirmations, virements, expéditions) depuis ce domaine — sans ces enregistrements, ils finiront en spam, ce qui est rédhibitoire pour un site marchand

## 10. Points ouverts

1. Docker ou installation native pour l'environnement de développement local ?
2. Mise en place ou non d'un environnement de staging/recette ?
3. Stratégie de sauvegarde (fréquence, rétention, test de restauration) ?
4. Réservation du nom de domaine et configuration DNS/emails (cf. §9)
5. **[NOUVEAU]** Identification d'un module Mondial Relay compatible Hyvä — à faire tôt, c'est la seule dépendance tierce structurante du projet
6. **[NOUVEAU]** Formulaire de contact : module custom léger ou extension tierce ?
7. **[NOUVEAU]** Paramétrage TVA selon le statut fiscal de la cliente (franchise en base probable) — à trancher avant la première vente
8. **[NOUVEAU]** Délai d'expiration d'une commande en attente de virement, et relibération automatique du stock
