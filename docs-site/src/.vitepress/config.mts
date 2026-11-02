// Site de documentation de Madame Aiguille : guide-magento.pierrebultez.com.
// Les pages viennent de docs/*.md (scripts/generer-pages.mjs) ; la barre latérale aussi.
import { readFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { defineConfig } from 'vitepress';

const sidebar = JSON.parse(readFileSync(new URL('./sidebar.json', import.meta.url), 'utf8'));

export default defineConfig({
    lang: 'fr-FR',
    title: 'Madame Aiguille',
    titleTemplate: ':title — Guide Madame Aiguille',
    description: 'Guide du back-office, documentation du thème et aide-mémoire du déploiement de la boutique Madame Aiguille.',
    cleanUrls: true,
    lastUpdated: false,
    // Documentation interne : pas d'indexation par les moteurs (doublé par l'en-tête nginx)
    head: [
        ['meta', { name: 'robots', content: 'noindex, nofollow' }],
        ['link', { rel: 'icon', href: '/favicon.svg', type: 'image/svg+xml' }],
    ],
    // La page Lighthouse lit docs/audits/lighthouse.json, hors du dossier src/
    vite: {
        server: { fs: { allow: [fileURLToPath(new URL('../../..', import.meta.url))] } },
    },
    themeConfig: {
        logo: '/favicon.svg',
        siteTitle: 'Guide Madame Aiguille',
        nav: [
            { text: 'Guide', link: '/guide/', activeMatch: '^/guide/' },
            { text: 'Thème', link: '/documentation/', activeMatch: '^/documentation/' },
            { text: 'Déploiement', link: '/commandes/', activeMatch: '^/commandes/' },
            { text: 'Lighthouse', link: '/lighthouse', activeMatch: '^/lighthouse' },
            { text: 'Boutique', link: 'https://madame-aiguille.fr' },
        ],
        sidebar,
        outline: { level: [2, 3], label: 'Sur cette page' },
        search: {
            provider: 'local',
            options: {
                translations: {
                    button: { buttonText: 'Rechercher', buttonAriaLabel: 'Rechercher' },
                    modal: {
                        displayDetails: 'Afficher le détail',
                        resetButtonTitle: 'Effacer la recherche',
                        backButtonTitle: 'Fermer la recherche',
                        noResultsText: 'Aucun résultat pour',
                        footer: {
                            selectText: 'ouvrir',
                            navigateText: 'naviguer',
                            closeText: 'fermer',
                        },
                    },
                },
            },
        },
        editLink: {
            // Exécuté dans le navigateur : la correspondance page → fichier source doit rester dans la fonction
            pattern: ({ filePath }) => {
                const sources: Record<string, string> = {
                    guide: 'guide-back-office.md',
                    documentation: 'documentation-theme.md',
                    commandes: 'commandes-et-deploiement.md',
                };
                return `https://github.com/PierreBultez/Madame-Aiguille/blob/main/docs/${sources[filePath.split('/')[0]] ?? ''}`;
            },
            text: 'Voir la source sur GitHub',
        },
        docFooter: { prev: 'Page précédente', next: 'Page suivante' },
        darkModeSwitchLabel: 'Apparence',
        lightModeSwitchTitle: 'Passer en mode clair',
        darkModeSwitchTitle: 'Passer en mode sombre',
        sidebarMenuLabel: 'Menu',
        returnToTopLabel: 'Retour en haut',
        langMenuLabel: 'Langue',
        notFound: {
            title: 'Page introuvable',
            quote: 'Cette page n’existe pas ou a changé d’adresse.',
            linkLabel: 'Revenir à l’accueil',
            linkText: 'Revenir à l’accueil',
        },
        footer: {
            message: 'Généré depuis le dossier docs/ du dépôt Madame Aiguille.',
        },
    },
});
