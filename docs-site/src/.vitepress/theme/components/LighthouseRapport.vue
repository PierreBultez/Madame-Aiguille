<script setup lang="ts">
// Rapport Lighthouse façon PageSpeed Insights : jauges, métriques et points relevés, avant / après.
// Données : docs/audits/lighthouse.json, produit par docs-site/scripts/resumer-lighthouse.mjs.
import { computed, ref } from 'vue';
import donnees from '../../../../../docs/audits/lighthouse.json';

type Metrique = { valeur: number; score: number | null };
type Resultat = {
    url: string;
    date: string;
    scores: Record<string, number>;
    metriques: Record<string, Metrique>;
    poids: number;
    requetes: number | null;
    alertes: { id: string; titre: string; score: number }[];
};

const avant = donnees.avant as Record<string, Resultat>;
const apres = donnees.apres as Record<string, Resultat>;

const PAGES = [
    { id: 'accueil', nom: 'Accueil' },
    { id: 'categorie', nom: 'Catégorie' },
    { id: 'fiche', nom: 'Fiche produit' },
];
const APPAREILS = [
    { id: 'mobile', nom: 'Mobile' },
    { id: 'desktop', nom: 'Ordinateur' },
];
const CATEGORIES = [
    { id: 'performance', nom: 'Performances' },
    { id: 'accessibility', nom: 'Accessibilité' },
    { id: 'best-practices', nom: 'Bonnes pratiques' },
    { id: 'seo', nom: 'SEO' },
];
const METRIQUES = [
    { id: 'first-contentful-paint', nom: 'First Contentful Paint', aide: 'Premier texte ou image affiché' },
    { id: 'largest-contentful-paint', nom: 'Largest Contentful Paint', aide: 'Plus grand élément affiché (souvent la photo principale)' },
    { id: 'total-blocking-time', nom: 'Total Blocking Time', aide: 'Temps pendant lequel la page ne répond pas' },
    { id: 'cumulative-layout-shift', nom: 'Cumulative Layout Shift', aide: 'Éléments qui bougent pendant le chargement' },
    { id: 'speed-index', nom: 'Speed Index', aide: 'Vitesse à laquelle la page se remplit' },
];
// Titres français des points relevés ; un point inconnu garde le titre anglais de Lighthouse
const ALERTES: Record<string, string> = {
    'bf-cache': 'La page ne profite pas du cache « page précédente » (pages de Magento non mises en cache par le navigateur)',
    'image-delivery-insight': 'Des images peuvent encore être mieux compressées ou dimensionnées',
    'network-dependency-tree-insight': 'Chaîne de requêtes critiques (fontes, styles) avant l’affichage',
    'render-blocking-insight': 'La feuille de styles bloque le premier affichage',
    'label-content-name-mismatch': 'Le nom accessible d’un lien diffère de son texte visible',
    'server-response-time': 'Temps de réponse initial du serveur',
    'document-latency-insight': 'Latence du document HTML',
    'lcp-breakdown-insight': 'Décomposition du Largest Contentful Paint',
    'meta-description': 'Pas de méta-description',
    'total-byte-weight': 'Poids total de la page élevé',
    interactive: 'Délai avant que la page soit utilisable',
};

const page = ref('accueil');
const appareil = ref('mobile');
const cle = computed(() => `${page.value}-${appareil.value}`);
const actuel = computed(() => apres[cle.value]);
const precedent = computed(() => avant[cle.value]);

const CIRCONFERENCE = 2 * Math.PI * 56;
const niveau = (score: number | null) => (score === null ? 'neutre' : score >= 0.9 ? 'bon' : score >= 0.5 ? 'moyen' : 'faible');
const niveauScore = (score: number) => niveau(score / 100);

const nombre = (valeur: number, decimales: number) =>
    valeur.toLocaleString('fr-FR', { minimumFractionDigits: decimales, maximumFractionDigits: decimales });
function formater(id: string, valeur: number) {
    if (id === 'cumulative-layout-shift') {
        return nombre(valeur, 3);
    }
    if (id === 'total-blocking-time') {
        return `${nombre(Math.round(valeur / 10) * 10, 0)} ms`;
    }
    return `${nombre(valeur / 1000, 1)} s`;
}
const ko = (octets: number) => `${nombre(Math.round(octets / 1024), 0)} Ko`;
const date = new Date(donnees.date as string).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
</script>

<template>
    <div class="lh">
        <p class="lh-contexte">
            {{ donnees.outil }} · {{ date }} · <a :href="actuel.url" target="_blank" rel="noopener">{{ actuel.url }}</a>
        </p>

        <div class="lh-choix">
            <div class="lh-onglets" role="tablist" aria-label="Page">
                <button
                    v-for="p in PAGES"
                    :key="p.id"
                    role="tab"
                    :aria-selected="page === p.id"
                    :class="{ actif: page === p.id }"
                    @click="page = p.id"
                >{{ p.nom }}</button>
            </div>
            <div class="lh-onglets" role="tablist" aria-label="Appareil">
                <button
                    v-for="a in APPAREILS"
                    :key="a.id"
                    role="tab"
                    :aria-selected="appareil === a.id"
                    :class="{ actif: appareil === a.id }"
                    @click="appareil = a.id"
                >
                    <svg v-if="a.id === 'mobile'" viewBox="0 0 24 24" aria-hidden="true"><rect x="7" y="2" width="10" height="20" rx="2" /><path d="M11 18h2" /></svg>
                    <svg v-else viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="12" rx="1.5" /><path d="M8 20h8M12 16v4" /></svg>
                    {{ a.nom }}
                </button>
            </div>
        </div>

        <div class="lh-jauges">
            <div v-for="c in CATEGORIES" :key="c.id" class="lh-jauge" :class="niveauScore(actuel.scores[c.id])">
                <svg viewBox="0 0 128 128" role="img" :aria-label="`${c.nom} : ${actuel.scores[c.id]} sur 100`">
                    <circle class="fond" cx="64" cy="64" r="56" />
                    <circle
                        class="arc"
                        cx="64"
                        cy="64"
                        r="56"
                        :stroke-dasharray="`${(actuel.scores[c.id] / 100) * CIRCONFERENCE} ${CIRCONFERENCE}`"
                    />
                    <text x="64" y="64">{{ actuel.scores[c.id] }}</text>
                </svg>
                <span class="lh-jauge-nom">{{ c.nom }}</span>
                <span
                    v-if="precedent && precedent.scores[c.id] !== actuel.scores[c.id]"
                    class="lh-avant"
                    :title="`Avant les corrections : ${precedent.scores[c.id]}`"
                >avant {{ precedent.scores[c.id] }}</span>
            </div>
        </div>

        <ul class="lh-legende" aria-label="Légende">
            <li class="faible">0–49</li>
            <li class="moyen">50–89</li>
            <li class="bon">90–100</li>
        </ul>

        <h3 class="lh-titre">Métriques</h3>
        <div class="lh-metriques">
            <div v-for="m in METRIQUES" :key="m.id" class="lh-metrique" :class="niveau(actuel.metriques[m.id].score)">
                <span class="lh-forme" aria-hidden="true" />
                <div>
                    <div class="lh-metrique-nom" :title="m.aide">{{ m.nom }}</div>
                    <div class="lh-metrique-valeur">
                        {{ formater(m.id, actuel.metriques[m.id].valeur) }}
                        <span
                            v-if="precedent && formater(m.id, precedent.metriques[m.id].valeur) !== formater(m.id, actuel.metriques[m.id].valeur)"
                            class="lh-metrique-avant"
                        >avant {{ formater(m.id, precedent.metriques[m.id].valeur) }}</span>
                    </div>
                    <div class="lh-metrique-aide">{{ m.aide }}</div>
                </div>
            </div>
            <div class="lh-metrique neutre">
                <span class="lh-forme" aria-hidden="true" />
                <div>
                    <div class="lh-metrique-nom">Poids de la page</div>
                    <div class="lh-metrique-valeur">
                        {{ ko(actuel.poids) }}
                        <span v-if="precedent && ko(precedent.poids) !== ko(actuel.poids)" class="lh-metrique-avant">avant {{ ko(precedent.poids) }}</span>
                    </div>
                    <div class="lh-metrique-aide">Tout ce que le navigateur télécharge</div>
                </div>
            </div>
        </div>

        <h3 class="lh-titre">Points relevés par Lighthouse</h3>
        <p class="lh-note">Pistes d’amélioration signalées par Lighthouse ; celles qui restent ne pèsent plus sur la note.</p>
        <ul v-if="actuel.alertes.length" class="lh-alertes">
            <li v-for="a in actuel.alertes" :key="a.id" :class="niveau(a.score)">
                <span class="lh-forme" aria-hidden="true" />{{ ALERTES[a.id] ?? a.titre }}
            </li>
        </ul>
        <p v-else>Aucun.</p>

        <h3 class="lh-titre">Toutes les pages</h3>
        <div class="lh-tableau">
            <table>
                <thead>
                    <tr>
                        <th>Page</th>
                        <th v-for="c in CATEGORIES" :key="c.id">{{ c.nom }}</th>
                        <th>LCP</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-for="p in PAGES" :key="p.id">
                        <tr v-for="a in APPAREILS" :key="a.id" :class="{ courant: cle === `${p.id}-${a.id}` }" @click="page = p.id; appareil = a.id">
                            <td>{{ p.nom }} · {{ a.nom.toLowerCase() }}</td>
                            <td v-for="c in CATEGORIES" :key="c.id">
                                <span v-if="avant[`${p.id}-${a.id}`] && avant[`${p.id}-${a.id}`].scores[c.id] !== apres[`${p.id}-${a.id}`].scores[c.id]" class="lh-pastille avant" :class="niveauScore(avant[`${p.id}-${a.id}`].scores[c.id])">{{ avant[`${p.id}-${a.id}`].scores[c.id] }}</span>
                                <span class="lh-pastille" :class="niveauScore(apres[`${p.id}-${a.id}`].scores[c.id])">{{ apres[`${p.id}-${a.id}`].scores[c.id] }}</span>
                            </td>
                            <td class="lh-lcp">{{ formater('largest-contentful-paint', apres[`${p.id}-${a.id}`].metriques['largest-contentful-paint'].valeur) }}</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <p class="lh-note">Pastille barrée : score avant les corrections du 2 novembre. Cliquer une ligne l’affiche ci-dessus.</p>
    </div>
</template>

<style scoped>
.lh {
    --lh-bon: #0c6;
    --lh-bon-texte: #080;
    --lh-moyen: #fa3;
    --lh-moyen-texte: #c33300;
    --lh-faible: #f33;
    --lh-faible-texte: #c00;
    --lh-neutre: #9e9e9e;
    margin-top: 16px;
}
:global(.dark) .lh {
    --lh-bon-texte: #0c6;
    --lh-moyen-texte: #fa3;
    --lh-faible-texte: #ff6b6b;
}
.lh-contexte {
    color: var(--vp-c-text-2);
    font-size: 14px;
    overflow-wrap: anywhere;
}
.lh-choix {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin: 20px 0 8px;
}
.lh-onglets {
    display: inline-flex;
    border: 1px solid var(--vp-c-divider);
    border-radius: 8px;
    overflow: hidden;
}
.lh-onglets button {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    font-size: 14px;
    color: var(--vp-c-text-2);
    background: transparent;
}
.lh-onglets button + button {
    border-left: 1px solid var(--vp-c-divider);
}
.lh-onglets button.actif {
    color: var(--vp-c-brand-1);
    background: var(--vp-c-brand-soft);
    font-weight: 600;
}
.lh-onglets svg {
    width: 16px;
    height: 16px;
    fill: none;
    stroke: currentColor;
    stroke-width: 2;
    stroke-linecap: round;
}
.lh-jauges {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 8px;
    margin: 28px 0 8px;
    text-align: center;
}
.lh-jauge {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}
.lh-jauge svg {
    width: 100%;
    max-width: 112px;
}
.lh-jauge .fond {
    fill: color-mix(in srgb, var(--lh-c) 10%, transparent);
    stroke: color-mix(in srgb, var(--lh-c) 20%, transparent);
    stroke-width: 8;
}
.lh-jauge .arc {
    fill: none;
    stroke: var(--lh-c);
    stroke-width: 8;
    stroke-linecap: round;
    transform: rotate(-90deg);
    transform-origin: 64px 64px;
    transition: stroke-dasharray 0.6s ease;
}
.lh-jauge text {
    fill: var(--lh-t);
    font: 500 40px/1 Roboto, Helvetica, Arial, sans-serif;
    text-anchor: middle;
    dominant-baseline: central;
}
.lh-jauge-nom {
    font-size: 15px;
    line-height: 1.3;
    color: var(--vp-c-text-1);
}
.lh-avant {
    font-size: 12px;
    color: var(--vp-c-text-3);
    text-decoration: line-through;
}
.bon {
    --lh-c: var(--lh-bon);
    --lh-t: var(--lh-bon-texte);
}
.moyen {
    --lh-c: var(--lh-moyen);
    --lh-t: var(--lh-moyen-texte);
}
.faible {
    --lh-c: var(--lh-faible);
    --lh-t: var(--lh-faible-texte);
}
.neutre {
    --lh-c: var(--lh-neutre);
    --lh-t: var(--vp-c-text-1);
}
.lh-legende {
    display: flex;
    justify-content: center;
    gap: 20px;
    padding: 0;
    margin: 16px 0 8px;
    list-style: none;
    font-size: 13px;
    color: var(--vp-c-text-2);
}
.lh-legende li,
.lh-alertes li {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}
.lh-forme {
    flex: none;
    width: 12px;
    height: 12px;
    background: var(--lh-c);
}
.lh-legende li::before {
    content: '';
    width: 12px;
    height: 12px;
    background: var(--lh-c);
}
/* Formes de Lighthouse : triangle (faible), carré (moyen), rond (bon) */
.faible .lh-forme,
.lh-legende .faible::before {
    clip-path: polygon(50% 0, 100% 100%, 0 100%);
}
.bon .lh-forme,
.lh-legende .bon::before {
    border-radius: 50%;
}
.neutre .lh-forme {
    border-radius: 50%;
    background: transparent;
    border: 2px solid var(--lh-c);
}
.lh-titre {
    margin-top: 36px;
    padding-bottom: 8px;
    border-bottom: 1px solid var(--vp-c-divider);
}
.lh-metriques {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0 32px;
}
.lh-metrique {
    display: flex;
    gap: 12px;
    padding: 14px 0;
    border-bottom: 1px solid var(--vp-c-divider);
}
.lh-metrique .lh-forme {
    margin-top: 5px;
}
.lh-metrique-nom {
    font-size: 14px;
    color: var(--vp-c-text-1);
}
.lh-metrique-valeur {
    font: 400 26px/1.3 Roboto, Helvetica, Arial, sans-serif;
    color: var(--lh-t);
}
.lh-metrique-avant {
    margin-left: 8px;
    font-size: 13px;
    color: var(--vp-c-text-3);
    text-decoration: line-through;
}
.lh-metrique-aide {
    font-size: 13px;
    color: var(--vp-c-text-2);
}
.lh-alertes {
    padding: 0;
    list-style: none;
}
.lh-alertes li {
    padding: 10px 0;
    border-bottom: 1px solid var(--vp-c-divider);
    font-size: 15px;
}
.lh-tableau {
    overflow-x: auto;
}
.lh-tableau table {
    display: table;
    width: 100%;
    margin: 12px 0 0;
}
.lh-tableau th,
.lh-tableau td {
    text-align: center;
    white-space: nowrap;
}
.lh-tableau th:first-child,
.lh-tableau td:first-child {
    text-align: left;
}
.lh-tableau tr {
    cursor: pointer;
}
.lh-tableau tr.courant td {
    background: var(--vp-c-brand-soft);
}
.lh-pastille {
    display: inline-block;
    min-width: 34px;
    padding: 2px 6px;
    border-radius: 999px;
    font: 500 14px/1.4 Roboto, Helvetica, Arial, sans-serif;
    color: var(--lh-t);
    background: color-mix(in srgb, var(--lh-c) 14%, transparent);
}
.lh-pastille.avant {
    margin-right: 4px;
    text-decoration: line-through;
    opacity: 0.7;
}
.lh-lcp {
    font-variant-numeric: tabular-nums;
}
.lh-note {
    font-size: 13px;
    color: var(--vp-c-text-2);
}
@media (max-width: 640px) {
    .lh-jauges {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        row-gap: 24px;
    }
    .lh-metriques {
        grid-template-columns: minmax(0, 1fr);
    }
}
</style>
