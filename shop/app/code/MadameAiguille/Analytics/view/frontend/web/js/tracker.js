/**
 * Madame Aiguille — mesure d'audience Matomo (lot 9).
 *
 * Chargé par une seule balise <script src async> (tracker.phtml), sans code inline : il lit ses réglages dans ses
 * propres attributs data-*, prépare la file _paq, puis charge matomo.js depuis le serveur de statistiques. Si ce
 * serveur ne répond pas, seule la mesure échoue : la page n'attend rien de lui.
 *
 * Exemption de consentement de la CNIL : aucun cookie (disableCookies), ni User ID, ni recoupement ; l'IP est
 * anonymisée et la durée de conservation réglée dans Matomo (docs/conformite/matomo-cnil.md).
 */
(function () {
    'use strict';

    var script = document.currentScript;
    var url = script && script.getAttribute('data-url');
    var siteId = script && script.getAttribute('data-site-id');
    if (!url || !siteId) {
        return;
    }

    var page = script.getAttribute('data-page');
    var _paq = window._paq = window._paq || [];

    // Avant tout appel de suivi : aucun cookie _pk_* n'est jamais écrit
    _paq.push(['disableCookies']);

    if (page === 'not-found') {
        // Convention de Matomo pour regrouper les pages introuvables et leur provenance
        _paq.push(['setDocumentTitle', '404/URL = ' + encodeURIComponent(location.pathname + location.search)
            + '/From = ' + encodeURIComponent(document.referrer)]);
    }

    if (page === 'search') {
        var count = script.getAttribute('data-search-count');
        // Remplace trackPageView sur la page de résultats : la visite compte, avec le mot-clé et le nombre de résultats
        _paq.push(['trackSiteSearch', script.getAttribute('data-search-keyword') || '', false,
            count === null ? false : parseInt(count, 10)]);
    } else {
        _paq.push(['trackPageView']);
    }
    _paq.push(['enableLinkTracking']);
    _paq.push(['setTrackerUrl', url + 'matomo.php']);
    _paq.push(['setSiteId', siteId]);

    var matomo = document.createElement('script');
    matomo.async = true;
    matomo.src = url + 'matomo.js';
    document.head.appendChild(matomo);
})();
