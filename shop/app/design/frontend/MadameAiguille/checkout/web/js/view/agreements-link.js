/** Lien vers la page CMS, résolu avec la base de la boutique et non figé sur localhost. */
define(['mage/url'], function (url) {
    'use strict';

    return function (Component) {
        return Component.extend({cgvUrl: url.build('cgv')});
    };
});
