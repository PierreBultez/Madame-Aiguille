// Résume des rapports Lighthouse (JSON) pour la page « Lighthouse » du site : scores, métriques, poids.
// Les rapports bruts (500 à 800 Ko chacun) ne sont pas versionnés ; seul le résumé l'est, dans docs/audits/.
//
// Rapports nommés <page>-<mobile|desktop>.json, pages : accueil, categorie, fiche. Usage :
//   node docs-site/scripts/resumer-lighthouse.mjs --avant <dossier> --apres <dossier> --sortie docs/audits/lighthouse.json
// Lancer Lighthouse : voir la page « Lighthouse » du site, section « Rejouer l'audit ».
import { readFileSync, readdirSync, writeFileSync } from 'node:fs';
import { join } from 'node:path';

const args = Object.fromEntries(
    process.argv.slice(2).reduce((paires, mot, i, tous) => (mot.startsWith('--') ? [...paires, [mot.slice(2), tous[i + 1]]] : paires), [])
);
if (!args.apres || !args.sortie) {
    console.error('Usage : --apres <dossier> [--avant <dossier>] --sortie <fichier.json>');
    process.exit(1);
}

const CATEGORIES = ['performance', 'accessibility', 'best-practices', 'seo'];
const METRIQUES = ['first-contentful-paint', 'largest-contentful-paint', 'total-blocking-time', 'cumulative-layout-shift', 'speed-index'];

function resumer(fichier) {
    const r = JSON.parse(readFileSync(fichier, 'utf8'));
    const audit = (id) => r.audits[id];
    return {
        url: r.finalDisplayedUrl,
        date: r.fetchTime,
        scores: Object.fromEntries(CATEGORIES.map((c) => [c, Math.round(r.categories[c].score * 100)])),
        metriques: Object.fromEntries(
            METRIQUES.map((m) => [m, { valeur: audit(m).numericValue, score: audit(m).score }])
        ),
        poids: audit('total-byte-weight').numericValue,
        requetes: audit('network-requests')?.details?.items?.length ?? null,
        // Audits pénalisants (hors informatifs), pour comprendre un score
        alertes: Object.values(r.audits)
            .filter((a) => typeof a.score === 'number' && a.score < 0.9 && a.scoreDisplayMode !== 'informative' && a.scoreDisplayMode !== 'manual')
            .filter((a) => !METRIQUES.includes(a.id))
            .map((a) => ({ id: a.id, titre: a.title, score: a.score })),
    };
}

function lire(dossier) {
    if (!dossier) {
        return {};
    }
    const resultats = {};
    for (const nom of readdirSync(dossier).filter((f) => /^[a-z]+-(mobile|desktop)\.json$/.test(f))) {
        resultats[nom.replace('.json', '')] = resumer(join(dossier, nom));
    }
    return resultats;
}

const apres = lire(args.apres);
const premier = Object.values(apres)[0];
const sortie = {
    outil: `Lighthouse ${JSON.parse(readFileSync(join(args.apres, readdirSync(args.apres).find((f) => f.endsWith('.json'))), 'utf8')).lighthouseVersion}`,
    date: premier?.date ?? null,
    avant: lire(args.avant),
    apres,
};
writeFileSync(args.sortie, `${JSON.stringify(sortie, null, 2)}\n`);
console.log(`${Object.keys(apres).length} rapports résumés dans ${args.sortie}`);
