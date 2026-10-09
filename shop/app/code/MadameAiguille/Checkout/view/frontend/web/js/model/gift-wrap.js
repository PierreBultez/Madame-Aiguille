/**
 * Choix de l'emballage cadeau, partagé entre la case du tunnel et l'envoi au serveur.
 */
define(['ko'], function (ko) {
    'use strict';

    var config = window.checkoutConfig.madameaiguilleGiftWrap || {enabled: false};

    return {
        config: config,
        requested: ko.observable(!!config.requested)
    };
});
