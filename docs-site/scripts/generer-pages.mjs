// Génère les pages du site depuis docs/*.md, seule source de vérité : une page par section « ## »,
// le texte d'avant la première section devient la page « Présentation ». Lancé par npm run dev / build.
//
// Réécritures, hors code : « §26 » devient un lien vers la section 26 de la documentation du thème,
// les chemins des captures du guide pointent vers /guide-back-office/, et <…> est échappé. Le corps de chaque
// page est enveloppé dans « ::: v-pre » : Vue compile chaque page et interpréterait {{ … }}, même dans le code
// en ligne (les gabarits d'email en contiennent).
import { cpSync, mkdirSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const ici = dirname(fileURLToPath(import.meta.url));
const racine = join(ici, '..', '..');
const docs = join(racine, 'docs');
const src = join(ici, '..', 'src');

const DOCUMENTS = [
    {
        fichier: 'guide-back-office.md',
        dossier: 'guide',
        titre: 'Guide du back-office',
    },
    {
        fichier: 'documentation-theme.md',
        dossier: 'documentation',
        titre: 'Documentation du thème',
        groupes: [
            ['Fondations', [1, 2, 3, 4, 5, 6, 16]],
            ['Contenus', [7, 13, 18, 19]],
            ['Catalogue', [8, 9, 10, 11, 12]],
            ['Panier et tunnel', [14, 20, 26, 27]],
            ['Compte et emails', [21, 22, 28]],
            ['Exploitation', [15, 17, 23, 24, 25, 29]],
        ],
    },
    {
        fichier: 'commandes-et-deploiement.md',
        dossier: 'commandes',
        titre: 'Commandes et déploiement',
    },
    {
        fichier: 'guide-exploitation.md',
        dossier: 'exploitation',
        titre: "Guide d'exploitation",
    },
];

const sansAccents = (texte) => texte.normalize('NFD').replace(/[̀-ͯ]/g, '');

function slug(titre) {
    return sansAccents(titre)
        .toLowerCase()
        .replace(/`/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, '')
        .slice(0, 60)
        .replace(/-$/, '');
}

/** Découpe un document en préambule et sections « ## », sans se laisser tromper par les # des blocs de code. */
function decouper(contenu) {
    const lignes = contenu.split('\n');
    let titre = '';
    let enCode = false;
    const preambule = [];
    const sections = [];
    for (const ligne of lignes) {
        if (/^\s*(```|~~~)/.test(ligne)) {
            enCode = !enCode;
        }
        if (!enCode && !titre && /^# /.test(ligne)) {
            titre = ligne.slice(2).trim();
            continue;
        }
        if (!enCode && /^## /.test(ligne)) {
            sections.push({ titre: ligne.slice(3).trim(), lignes: [] });
            continue;
        }
        (sections.length ? sections[sections.length - 1].lignes : preambule).push(ligne);
    }
    return { titre, preambule, sections };
}

/** Numéro et intitulé d'une section « 26. Livraison et paiements (lot 5) ». */
function numeroter(titre) {
    const m = titre.match(/^(\d+)\.\s+(.*)$/);
    return m ? { numero: Number(m[1]), intitule: m[2] } : { numero: null, intitule: titre };
}

/** Réécrit le texte hors code (blocs et code en ligne). */
function reecrire(lignes, liensParagraphes) {
    let enCode = false;
    return lignes.map((ligne) => {
        if (/^\s*(```|~~~)/.test(ligne)) {
            enCode = !enCode;
            return ligne;
        }
        if (enCode) {
            return ligne;
        }
        // Les titres descendent d'un cran : ### devient une entrée du sommaire de la page
        const titre = ligne.match(/^(#{3,6}) (.*)$/);
        if (titre) {
            ligne = `${titre[1].slice(1)} ${titre[2]}`;
        }
        return ligne
            .split(/(`[^`]*`)/)
            .map((morceau, i) => {
                if (i % 2 === 1) {
                    return morceau;
                }
                return morceau
                    .replace(/\]\(guide-back-office\//g, '](/guide-back-office/')
                    .replace(/\{\{/g, '&#123;&#123;')
                    .replace(/\}\}/g, '&#125;&#125;')
                    .replace(/<(?!\/?(br|strong|em|code|a|kbd|sup|sub|details|summary)\b)/g, '&lt;')
                    .replace(/§(\d+)/g, (tout, n) => (liensParagraphes[n] ? `[§${n}](${liensParagraphes[n]})` : tout));
            })
            .join('');
    });
}

/** Sans interprétation par Vue : ni {{ … }} ni directives, y compris dans le code en ligne. */
function proteger(corps) {
    return `::: v-pre\n${corps.trim()}\n:::\n`;
}

function ecrire(chemin, contenu) {
    mkdirSync(dirname(chemin), { recursive: true });
    writeFileSync(chemin, contenu);
}

const lus = DOCUMENTS.map((doc) => ({ ...decouper(readFileSync(join(docs, doc.fichier), "utf8")), ...doc }));

// Adresses des sections de chaque document, et liens « §N » vers la documentation du thème
for (const doc of lus) {
    doc.pages = doc.sections.map((section) => {
        const { numero, intitule } = numeroter(section.titre);
        const nom = numero !== null ? `${String(numero).padStart(2, '0')}-${slug(intitule)}` : slug(intitule);
        return { ...section, numero, intitule, lien: `/${doc.dossier}/${nom}`, fichier: `${doc.dossier}/${nom}.md` };
    });
}
const liensParagraphes = Object.fromEntries(
    lus.find((doc) => doc.dossier === 'documentation').pages.map((page) => [page.numero, page.lien])
);

const sidebar = {};
for (const doc of lus) {
    rmSync(join(src, doc.dossier), { recursive: true, force: true });

    ecrire(
        join(src, doc.dossier, 'index.md'),
        `# ${doc.titre}\n\n${proteger(reecrire(doc.preambule, liensParagraphes).join('\n').trim())}`
    );

    doc.pages.forEach((page) => {
        const affiche = page.numero !== null ? `${page.numero}. ${page.intitule}` : page.intitule;
        ecrire(join(src, page.fichier), `# ${affiche}\n\n${proteger(reecrire(page.lignes, liensParagraphes).join('\n'))}`);
    });

    const entree = (page) => ({ text: page.intitule.replace(/`/g, ''), link: page.lien });
    const presentation = { text: 'Présentation', link: `/${doc.dossier}/` };
    sidebar[`/${doc.dossier}/`] = doc.groupes
        ? [
              { text: doc.titre, items: [presentation] },
              ...doc.groupes.map(([texte, numeros]) => ({
                  text: texte,
                  collapsed: false,
                  items: numeros.map((n) => entree(doc.pages.find((page) => page.numero === n))),
              })),
          ]
        : [{ text: doc.titre, items: [presentation, ...doc.pages.map(entree)] }];
}

// Toutes les sections du thème doivent figurer dans un groupe
const theme = lus.find((doc) => doc.dossier === 'documentation');
const classees = new Set(theme.groupes.flatMap(([, numeros]) => numeros));
const oubliees = theme.pages.filter((page) => !classees.has(page.numero)).map((page) => page.numero);
if (oubliees.length) {
    throw new Error(`Sections du thème sans groupe dans la barre latérale : ${oubliees.join(', ')}`);
}

ecrire(join(src, '.vitepress', 'sidebar.json'), JSON.stringify(sidebar, null, 2));
rmSync(join(src, 'public', 'guide-back-office'), { recursive: true, force: true });
cpSync(join(docs, 'guide-back-office'), join(src, 'public', 'guide-back-office'), { recursive: true });

console.log(lus.map((doc) => `${doc.titre} : ${doc.pages.length} pages`).join('\n'));
