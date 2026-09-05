# Guide de bonnes pratiques — Développement du thème Hyvä "Madame Aiguille"

Document interne — v0.2

> **Mise à jour v0.2 (05/09/2026)** — Alignement sur la révision du modèle catalogue (séries limitées au lieu de pièces uniques) et sur le formulaire de contact retenu à la place du formulaire sur-mesure. Ajout des tokens de marque Tailwind issus de `charte-graphique.md`.

Document de référence à garder ouvert pendant le développement du thème. Il complète `architecture-technique.md` (choix globaux) en se concentrant sur les pratiques de code au quotidien.

## 1. Structure du thème enfant

Rappel : on ne touche jamais à `Hyva/default` directement. Tout le code custom vit dans un thème enfant dédié.

```
app/design/frontend/MadameAiguille/theme/
├── composer.json
├── registration.php
├── theme.xml              → <parent>Hyva/default</parent>
├── etc/view.xml
├── media/preview.png
├── web/
│   ├── tailwind/           (copié depuis le parent, config à adapter)
│   ├── css/styles.css      (généré — jamais édité à la main)
│   ├── js/
│   └── svg/icons/
└── Magento_<Module>/
    ├── layout/
    └── templates/
```

Le thème est versionné comme n'importe quel module Composer du projet, dans le repo Git principal.

## 2. Bonnes pratiques de templates (.phtml)

- **Toujours échapper les sorties.** Aucune variable ne doit être imprimée brute dans un template : `$escaper->escapeHtml($texte)`, `escapeUrl()`, `escapeCss()`, `escapeJs()`, `escapeHtmlAttr()` selon le contexte. C'est la première ligne de défense contre les failles XSS, et c'est encore plus important sur un site qui manipule des données saisies par les clients (formulaire de contact notamment, qui accepte une pièce jointe).
- **Zéro logique métier dans le `.phtml`.** Un template ne doit qu'afficher — pas de calcul de prix, pas de règle de gestion (ex. "reste-t-il moins de 3 pièces ?"). Cette logique va dans un ViewModel (cf. §3).
- **Toujours traduire les textes visibles** avec `__('Mon texte')`, même si le site est mono-langue au départ — ça ne coûte rien et évite un chantier de rattrapage si un jour une deuxième langue est envisagée.
- **Ne pas dupliquer un template entier pour un micro-changement.** Si tu ne changes qu'un bloc de layout, préfère `<referenceBlock>`/`<referenceContainer>` en layout XML plutôt que de copier tout le `.phtml` du parent, sinon tu perds les futures mises à jour du parent sur tout le reste du fichier.

## 3. Le pattern ViewModel (à utiliser systématiquement)

Un ViewModel est une classe PHP simple, injectée au template via le layout XML, qui expose uniquement des méthodes utiles à l'affichage — sans dépendre du Block Magento (donc facilement testable isolément).

**Déclaration en layout XML :**

```xml
<referenceBlock name="product.info.main">
    <arguments>
        <argument name="view_model" xsi:type="object">
            MadameAiguille\Theme\ViewModel\Product\LimitedSeries
        </argument>
    </arguments>
</referenceBlock>
```

**La classe :**

```php
namespace MadameAiguille\Theme\ViewModel\Product;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Catalog\Model\Product;

class LimitedSeries implements ArgumentInterface
{
    public function isLimitedSeries(Product $product): bool
    {
        return (bool) $product->getData('serie_limitee');
    }

    public function getRemainingLabel(Product $product): ?string
    {
        // "Plus que 2 exemplaires" — cf. spécification fonctionnelle §2
    }
}
```

**Dans le template :** on récupère l'instance via `$block->getViewModel()` (ou `$block->getData('view_model')` selon la déclaration retenue), puis on appelle ses méthodes publiques — jamais de nouvelle instanciation directe dans le `.phtml`.

Pourquoi s'y tenir strictement sur ce projet : l'affichage des séries limitées (mention "Série limitée", stock restant, état épuisé), le bloc Nouveautés et le formulaire de contact vont vite accumuler de petites règles d'affichage. Centraliser ça dans des ViewModels dédiés (un par responsabilité — `LimitedSeries`, `ContactForm`, etc.) évite que la logique se disperse dans des `.phtml` de plus en plus difficiles à suivre.

## 4. Layout XML

- Un fichier de layout par handle de page (`catalog_product_view.xml`, `checkout_cart_index.xml`, etc.), jamais un fichier fourre-tout
- Utiliser la fusion (`<referenceBlock>`) plutôt que la surcharge complète dès que possible
- Un ViewModel = une déclaration `<argument name="view_model">` claire, nommée explicitement — éviter de réutiliser un même ViewModel générique pour des responsabilités différentes

## 5. Tailwind CSS

- **Toute la palette et la typographie de la marque se définissent une seule fois** : les couleurs dans `hyva.config.json` (`tokens.values.color`), les familles dans le `@theme` de `tailwind-source.css` — jamais de couleur hexadécimale hardcodée dans un `.phtml`. Si la charte évolue, un seul endroit à modifier.
- Rester en utility-first autant que possible ; éviter `@apply` sauf pour un composant vraiment répété partout (bouton principal, badge "série limitée"...) — un usage excessif de `@apply` fait perdre l'intérêt de Tailwind (lisibilité, pas de CSS à maintenir séparément).
- Vérifier que la config `content`/`hyva.config.json` inclut bien les chemins du thème parent en plus du thème enfant, pour que le purge ne supprime pas des classes utilisées dans des templates hérités non surchargés (cf. échange précédent — piège fréquent).
- Toujours lancer `npm run build` avant un déploiement — le `styles.css` généré ne doit jamais être édité à la main ni divergent de la config source.


### ⚠️ Tailwind v4 — pas de `tailwind.config.js`

Hyvä 1.5.2 utilise **Tailwind CSS v4** (`tailwindcss ^4.3.1`, `@tailwindcss/cli`). La quasi-totalité de la documentation Hyvä disponible en ligne porte encore sur la v3 : ne pas transposer ses réflexes.

Ce qui change concrètement, dans `web/tailwind/` :

| v3 | v4 (ce projet) |
|---|---|
| `tailwind.config.js` | Configuration en CSS, via `@theme { }` dans `tailwind-source.css` |
| Tableau `content: [...]` | Directives `@source "../../**/*.phtml"` |
| `theme.extend.colors` | Tokens dans `hyva.config.json` → `tokens.values.color`, en **oklch** |
| — | `npx hyva-sources` et `npx hyva-tokens` génèrent `generated/hyva-source.css` et `generated/hyva-tokens.css` avant chaque build |
| — | `tailwind.include.src` dans `hyva.config.json` pour hériter du thème parent |

Scripts npm : `npm run watch` en développement, `npm run build` en production, `npm run generate` pour régénérer seulement les fichiers dérivés.

Référence : <https://docs.hyva.io/hyva-themes/working-with-tailwindcss/>

### Tokens de marque

Couleurs dans le `hyva.config.json` du thème enfant, en oklch (hex en regard pour contrôle) :

```json
"brand":       "oklch(53.4% 0.054 26.8)",   /* #8A615C — boutons, titres, liens */
"brand-dark":  "oklch(45.5% 0.046 26.8)",   /* #6E4D49 — survol */
"brand-rose":  "oklch(67.8% 0.078 31.5)",   /* #C3867A — décoratif uniquement */
"brand-nude":  "oklch(84.4% 0.053 51.8)",   /* #E9C3AD — filets, ornements */
"brand-blush": "oklch(93.8% 0.014 17.4)",   /* #F4E7E7 — fonds de section */
"brand-ivory": "oklch(96.2% 0.013 48.6)",   /* #FAF0EB — fond de page */
"brand-paper": "oklch(99.3% 0.004 56.4)",   /* #FFFCFA — cartes, header */
"brand-ink":   "oklch(42.8% 0.032 44.4)"    /* #5F4A41 — corps de texte */
```

Familles typographiques dans `tailwind-source.css` :

```css
@theme {
  --font-display: 'Britney', Didot, 'Bodoni MT', Georgia, serif;
  --font-body: 'Sentient', 'Iowan Old Style', Georgia, serif;
}
```

Aucune valeur hexadécimale en dur dans un `.phtml`, aucune classe arbitraire type `text-[#8A615C]`.

⚠️ `brand-rose` (`#C3867A`) ne passe pas le seuil WCAG AA avec du texte blanc (3,0 : 1). Les boutons utilisent `brand`. Cf. `charte-graphique.md` §3.1.

### Fontes auto-hébergées **[NOUVEAU]**

Britney et Sentient (ITF, Free Font License) sont **auto-hébergées** — aucun chargement distant, ni Google Fonts, ni API Fontshare.

- Fichiers `.woff2` dans `web/fonts/` du thème enfant, **servis tels quels** : la licence ITF interdit le subsetting, la conversion de format et la modification des métadonnées
- Les `@font-face` vont dans un fichier dédié importé en tête de la source Tailwind (`web/tailwind/src/fonts.css`), jamais dispersés dans les composants
- Ne déclarer que les graisses utilisées : Britney Regular ; Sentient Light, Regular, Medium, Italic
- Précharger Britney Regular et Sentient Regular via `default_head_blocks.xml` :

```xml
<link rel="preload" src="fonts/Sentient-Regular.woff2"
      src_type="font/woff2" as="font" crossorigin="anonymous"/>
```

- `font-display: swap` sur toutes les déclarations
- Vérifier après build que les directives `@source` couvrent bien les templates du thème enfant, et que les classes utilisées uniquement dans des blocs CMS sont déclarées via `@source inline(...)`

> ⚠️ **Piège Hyvä classique** : les classes Tailwind utilisées dans un bloc CMS édité en back-office ne sont pas vues par le scanner au moment du build, et disparaissent donc du CSS de production. Toute classe destinée à être utilisée par Céline dans le CMS doit être déclarée explicitement — en Tailwind v4, via `@source inline(...)`.

## 6. Alpine.js

- Garder les `x-data` inline courts et déclaratifs (ouverture/fermeture d'un panneau, toggle, compteur) ; dès qu'un composant dépasse quelques lignes de logique, l'extraire dans `web/js/alpine/` via `Alpine.data('nomDuComposant', () => ({...}))` plutôt que de tout laisser inline dans le `.phtml`.
- Alpine ne doit gérer que l'UI (ouvrir/fermer, animer, afficher/masquer) — jamais recalculer une donnée métier déjà connue côté serveur (prix, disponibilité). Le serveur reste la source de vérité ; Alpine consomme ce que Magento a déjà calculé.

## 7. Compatibilité des extensions tierces

Avant d'installer toute extension Magento (paiement, avis clients...), vérifier sa compatibilité Hyvä (module de compatibilité officiel ou communautaire) plutôt que de découvrir après coup qu'elle s'affiche cassée. Tenir ce tableau à jour au fil du projet :

| Extension | Besoin couvert | Module de compatibilité Hyvä | Statut |
|---|---|---|---|
| Stripe (officiel) | Paiement CB | À vérifier au moment de l'installation | À faire |
| Mondial Relay | Sélecteur point relais | À identifier (cf. architecture-technique.md §5) | À faire |

## 8. Sécurité

- Escaping systématique (§2) — non négociable, y compris sur les données affichées dans les emails ou dans le formulaire de contact
- Aucun secret (clé API Stripe, identifiants) dans le code du thème : tout passe par la configuration Magento (chiffrée en base) ou `app/etc/env.php`, jamais en dur dans un `.phtml`/ViewModel
- Le thème `Hyva/default-csp` (Content Security Policy) est une option à évaluer si tu veux durcir davantage la sécurité front — à ne considérer qu'une fois le site stabilisé, ça ajoute une contrainte sur tout script/style inline

## 9. Performance

- `loading="lazy"` sur les images produit hors première vue
- Laisser `etc/view.xml` définir des tailles d'image cohérentes plutôt que de servir systématiquement l'image originale
- Ne pas ajouter de librairie JS supplémentaire pour un besoin qu'Alpine couvre déjà — chaque dépendance ajoutée grignote le gain de performance obtenu en abandonnant Luma

## 10. Accessibilité (rapide, à ne pas négliger)

- `alt` renseigné sur toutes les images produit (texte descriptif, pas juste le nom de fichier)
- Contrastes de couleurs suffisants dans la palette Tailwind définie en §5 (à vérifier une fois la charte de Madame Aiguille arrêtée)
- Navigation clavier fonctionnelle sur les composants Alpine interactifs (menu mobile, mini-panier, galerie produit) — Alpine propose des directives dédiées (ex. piégeage du focus dans une modale) à utiliser plutôt que de les ignorer

## 11. Workflow Git

- `node_modules/` : ignoré (jamais commité)
- `web/css/styles.css` : recommandation de **ne pas committer** le fichier généré et de le régénérer systématiquement en CI/CD (`npm run build` dans le pipeline GitHub Actions, cf. architecture-technique.md §7) — évite les divergences entre ce qui est commité et ce que la config Tailwind produirait réellement
- `package-lock.json` : commité, pour builds reproductibles (`npm ci`)

## 12. Journal des composants développés

À tenir à jour au fil du projet, pour garder une vue d'ensemble de ce qui est fait/en cours :

| Composant | ViewModel associé | Statut |
|---|---|---|
| Header / navigation | — | À faire |
| Footer | — | À faire |
| Page d'accueil | — | À faire |
| Fiche produit — taille (2 formats) | — | À faire |
| Fiche produit — série limitée (mention + stock restant) | `LimitedSeries` | À faire |
| Bloc Nouveautés / Actualités (page d'accueil) | — | À faire |
| Formulaire de contact (pièce jointe + produit concerné) | `ContactForm` (à créer) | À faire |
| Bloc Instagram + newsletter (page d'accueil) | — | À faire |
| Panier / mini-panier | — | À faire |
| Checkout (livraison/paiement) | — | À faire |
| Compte client | — | À faire |

## 13. Avant chaque montée de version Hyvä

Renvoi vers `plan-de-tests.md` §2 (tests de non-régression) — en complément, spécifique à Hyvä :

- Lire le changelog de la version ciblée (changements de structure de `tailwind-source.css`/`hyva.config.json` fréquents d'une version majeure à l'autre, cf. migration Tailwind v3 → v4 par exemple)
- Revérifier la compatibilité de chaque extension listée en §7 avant de monter de version
