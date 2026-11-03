# Mesure d'audience Matomo — auto-évaluation de l'exemption de consentement (CNIL)

Document interne — v1.0 (03/11/2026), lot 9. Éditeur : Madame Aiguille (Céline Bultez, entreprise individuelle).
Rédigé par Pierre, qui règle et maintient Matomo. Aucun identifiant, aucune IP ni aucun chemin d'administration ici :
le dépôt est public.

## Pourquoi ce document

La boutique mesure son audience **sans bannière de consentement**. C'est permis par l'article 82 de la loi
Informatique et Libertés, tel que la CNIL l'interprète dans ses lignes directrices et sa recommandation « cookies et
autres traceurs » (2020) : un traceur de mesure d'audience est **exempté de consentement** s'il reste strictement
nécessaire au service, ce qui suppose plusieurs conditions cumulatives.

Personne ne délivre de label : c'est **l'éditeur qui doit pouvoir démontrer** que sa configuration remplit chaque
condition, en cas de plainte ou de contrôle. Ce document est cette démonstration. Il dit, critère par critère, quel
réglage le respecte et où le vérifier. Il est à **relire à chaque changement** de Matomo, du module
`MadameAiguille_Analytics` ou de la page Confidentialité : une seule fonction ajoutée (User ID, carte de chaleur,
commerce électronique, partage avec un tiers…) peut faire sortir du cadre, et la bannière deviendrait obligatoire.

## Le dispositif

| Élément | Réglage |
|---|---|
| Outil | Matomo On-Premise 5.14.2, libre, installé sur le VPS de la boutique (`stats.madame-aiguille.fr`, OVH, France) |
| Données | base MariaDB dédiée `matomo`, sur le même serveur ; aucune transmission à Matomo (InnoCraft) ni à un tiers |
| Collecte | script `MadameAiguille_Analytics/view/frontend/web/js/tracker.js` sur la vitrine et le tunnel, jamais dans l'administration |
| Finalité | statistiques de fréquentation pour le seul usage de la boutique : pages vues, entrées et sorties, sources, appareils, pays et régions, recherche interne, pages introuvables |

## Grille

État au 03/11/2026. « Où vérifier » renvoie au menu de Matomo (roue dentée) ou au code du dépôt.

| # | Condition de l'exemption | Réglage retenu | Où vérifier | État |
|---|---|---|---|---|
| 1 | Finalité **strictement limitée** à la mesure d'audience du site, pour le compte exclusif de l'éditeur | Un seul site suivi, données lues par Pierre et Céline (lecture seule) ; aucune publicité, aucun profilage | *Sites web › Gérer* ; *Système › Utilisateurs* | conforme |
| 2 | Production de **données statistiques anonymes** uniquement | Rapports agrégés ; pas de User ID, ni de dimension personnalisée, ni de commerce électronique | `tracker.js` (seuls `disableCookies`, `trackPageView`, `trackSiteSearch`, `enableLinkTracking`) ; *Sites web › Gérer* (e-commerce désactivé) | conforme |
| 3 | **Aucun recoupement** avec d'autres traitements, aucune transmission à un tiers | Base dédiée, hébergement propre ; pas d'import de données Magento (commandes, clientes) | `deploy/serveur/99-matomo.sh` (base et utilisateur MariaDB séparés de ceux de la boutique) | conforme |
| 4 | **Pas de suivi** de la navigation sur d'autres sites ni entre plusieurs éditeurs | Un seul site, « Ne suivre que les visites sur ces URL » coché | *Sites web › Gérer › Madame Aiguille* | conforme |
| 5 | **Adresse IP** : pas de géolocalisation plus précise que la ville, IP non conservée en clair | IP tronquée de **2 octets**, IP tronquée utilisée aussi pour la géolocalisation ; base DB-IP Lite | *Confidentialité › Anonymiser les données* ; *Système › Géolocalisation* | conforme |
| 6 | **Traceur** de durée limitée (13 mois au plus), non prorogé à chaque visite | **Aucun cookie** de mesure (`disableCookies`) ; seul cookie possible : `mtm_consent_removed`, déposé uniquement quand le visiteur s'oppose, pour retenir son choix | `tracker.js` ; recette « aucun cookie `_pk_*` » | conforme |
| 7 | **Conservation** des données collectées : 25 mois au plus | Données brutes supprimées au bout de **760 jours** ; seuls des rapports agrégés anonymes sont gardés au-delà | *Confidentialité › Anonymiser les données › Supprimer les anciennes données brutes* | conforme |
| 8 | **Information** des personnes | Section « Mesure d'audience » de la page *Confidentialité* : outil, finalité, absence de cookie, IP tronquée, durée, droit d'opposition | `/confidentialite` ; patch `DescribeAudienceMeasurementInPrivacyPage` | conforme |
| 9 | **Droit d'opposition** simple et effectif | Module d'opposition de Matomo intégré à la page *Confidentialité*, sans script inline ; recette : case décochée → cookie `mtm_consent_removed`, plus aucune requête de mesure ; case recochée → cookie supprimé | `/confidentialite` ; `Block/OptOut.php` | conforme (recetté en local le 03/11/2026, à recetter en production) |
| 10 | **Sécurité** des données | HTTPS, double authentification des comptes Matomo, utilisateur système et pool PHP dédiés, configuration nginx durcie | `deploy/serveur/nginx/matomo.conf` ; *Personnel › Sécurité* | conforme |
| 11 | Durées et réglages **réexaminés** périodiquement | Relecture de cette grille à chaque mise à jour de Matomo et au moins une fois par an | `guide-exploitation.md` (surveillance) | à tenir |

## Ce qui ferait sortir du cadre

À ne pas activer sans consentement préalable (donc sans bannière) :

- l'identifiant utilisateur (User ID), le suivi des clientes connectées ou de leurs commandes ;
- le **commerce électronique** (commandes, chiffre d'affaires, paniers) : écarté par Pierre le 03/11/2026, à
  réexaminer avec cette grille s'il est demandé ;
- les cartes de chaleur, les enregistrements de session, les formulaires, les médias (extensions payantes) ;
- les cookies de suivi (`_pk_id`, `_pk_ses`…) : retirer `disableCookies` les réactiverait ;
- le partage des données ou l'accès d'un tiers (agence, autre site), un suivi multi-sites ;
- une géolocalisation plus fine que la ville, l'IP en clair.

## Écarts et limites au 03/11/2026

- **Géolocalisation** : base DB-IP City Lite installée le 03/11/2026 (`misc/DBIP-City.mmdb`), mise à jour mensuelle
  par Matomo ; avec l'IP tronquée de 2 octets, la localisation reste au mieux à la ville.
- **Validation** : cette grille est une auto-évaluation technique. Comme le reste de la page *Confidentialité*, le texte
  publié est à faire relire par le conseil de Céline.
