/**
 * Choix « Emballage et message cadeau » et carte qui l'accompagne, partagés entre l'étape Livraison
 * et l'envoi au serveur.
 */
define(['ko'], function (ko) {
    'use strict';

    var config = window.checkoutConfig.madameaiguilleGiftWrap || {enabled: false},
        card = config.card || {},
        model = {
            config: config,
            maxLength: config.messageMaxLength || 250,
            requested: ko.observable(!!config.requested),
            sender: ko.observable(card.sender || ''),
            recipient: ko.observable(card.recipient || ''),
            message: ko.observable(card.message || '')
        };

    /**
     * Option cochée sans carte complète, ou message trop long : l'étape ne se valide pas.
     *
     * @returns {Boolean}
     */
    model.isIncomplete = function () {
        var message = model.message().trim();

        return model.requested() && (
            !model.sender().trim() || !model.recipient().trim() || !message || message.length > model.maxLength
        );
    };

    return model;
});
