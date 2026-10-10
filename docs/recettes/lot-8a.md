# Recette du lot 8a — préproduction, emails et validation Mollie

Branche : `lot-8a-preproduction`, créée le 09/10/2026 depuis `main` (`73b3865`, langue française intégrée).
Journal tenu au fil du lot ; aucune valeur secrète n'y figure.

## Décisions de Pierre (09 et 10/10/2026)

- Cible : le VPS OVH qui héberge déjà ses autres sites, accès SSH par sa clé. **Domaine principal directement**
  (`madame-aiguille.fr`), sans sous-domaine de préproduction, non indexé ; l'équipe Mollie doit pouvoir voir le site.
- **Installation neuve** (pas de copie de la base locale), 2 ou 3 vraies créations saisies ensuite.
- Pas de bandeau « boutique en préparation ».
- Déploiement par **versions successives** (`releases/` + lien `current`), **build dans GitHub Actions** ; la clé SSH
  personnelle de Pierre sert au workflow ; pas d'utilisateur de déploiement.
- `madame-aiguille.fr` canonique, `www` en 301, **un certificat pour les deux noms**.
- **Double authentification** de l'administration dès l'installation.
- reCAPTCHA **v2 invisible** sur création de compte, contact, newsletter et mot de passe oublié ; pas sur la commande.
- SMTP **Brevo** ; IP locale et IP du VPS autorisées chez Brevo par Pierre.
- Webhook Mollie volontairement coupé en local, actif sur le serveur.
- VPS aligné sur le poste : Ubuntu 26.04 LTS, PHP 8.5.4, MariaDB 12.3.3, OpenSearch 3.9, Valkey 9.0.4 ;
  Varnish 7.7 (version de référence de Magento 2.4.9).
- Céline : `BDTEST`, tarifs, lieu de retrait, contenus juridiques et moyens Mollie restent provisoires.

## Audit de départ (09/10/2026)

| Constat | Conséquence |
|---|---|
| Mollie 3.1.3 ne facture et ne passe une commande en « Paiement reçu » **que par le webhook** (`SuccessfulPayment` s'arrête si le type n'est pas `webhook`) ; le retour navigateur ne fait qu'afficher | Commande locale `000000023` payée en test chez Mollie mais restée « En attente de paiement » : attendu sans webhook. Le serveur doit l'avoir actif |
| Aucun email ne partait en local : port 587 + IP non autorisée chez Brevo (« 525 Unauthorized IP ») | Corrigé par Pierre dans Brevo ; le réglage `ssl` sur 587 est sans effet (STARTTLS automatique du transport Symfony) |
| Domaine chez OVH, Brevo vérifié, DKIM `brevo1` / `brevo2`, DMARC `p=none` ; SPF limité à OVH | Alignement DMARC par DKIM ; SPF à compléter si Brevo le demande |
| VPS Ubuntu 25.10 (fin de vie), PHP 8.4, Elasticsearch 9 inutilisé à 24,5 Go de RAM, Redis partagé par trois sites | Mise à niveau 26.04 par Pierre, Elasticsearch supprimé, OpenSearch installé, Redis remplacé par Valkey |
| Formulaires surchargés (newsletter, contact) : points d'accroche reCAPTCHA conservés ; compte, connexion et mot de passe oublié viennent du parent Hyvä | Activation par la seule configuration |
| `Magento_TwoFactorAuth` désactivé dans `config.php` | Activé (`04fd144`) |

## Serveur

| Étape | Résultat |
|---|---|
| Elasticsearch 9.5.3 | Supprimé (index internes seulement, aucun site client) ; 24 Go de RAM libérés |
| OpenSearch 3.9.0 | Nœud unique local, tas 2 Go, statut vert ; certificats de démonstration supprimés par Pierre |
| Varnish 7.7.3 | 127.0.0.1:6081, 1 Go, en-têtes Magento |
| Ubuntu 26.04.1 LTS | Mise à niveau par Pierre. **PHP 8.4 retiré sans remplaçant** : quatre sites en 502, rétablis en PHP 8.5.4 (script lancé par Pierre, garde-fou de l'agent sur les ressources partagées) |
| Dépôts, MariaDB 12.3.3, Valkey 9.0.4 | Scripts 10, 20, 60 lancés par Pierre ; dump complet avant MariaDB ; Redis supprimé ; cinq sites à 200 |
| Hébergement (script 70) | Utilisateur `madame-aiguille`, arborescence, pool dédié, MariaDB 2 Go, certificat `madame-aiguille.fr` + `www` (expire le 08/01/2027), rotation des journaux |

## Local

- `madameaiguille:env:check` : 0 erreur, 3 avertissements attendus (HTTP, `BDTEST`) ; profil `--serveur --noindex` en
  erreur sur le poste, comme attendu.
- Patches d'installation appliqués sans ajouter ni réécrire de ligne de configuration (322 avant, 322 après).
- Double authentification active ; email du compte `admin` local corrigé en `pierre.bultez@proton.me` à la demande
  de Pierre (modèle utilisateur Magento, validations natives).
- Tests : 115 tests, 298 assertions. PHPCS Magento2 : 0 erreur sur les fichiers PHP ajoutés.
