// Thème VitePress par défaut, aux couleurs de la boutique (custom.css), et le rapport Lighthouse de la page dédiée.
import DefaultTheme from 'vitepress/theme';
import type { Theme } from 'vitepress';
import LighthouseRapport from './components/LighthouseRapport.vue';
import './custom.css';

export default {
    extends: DefaultTheme,
    enhanceApp({ app }) {
        app.component('LighthouseRapport', LighthouseRapport);
    },
} satisfies Theme;
