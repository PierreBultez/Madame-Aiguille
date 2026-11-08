/**
 * Case « Emballage et message cadeau » de l'étape Livraison et sa carte (De la part de, Pour, message).
 * Le montant s'ajoute au total au passage à l'étape suivante, quand Magento recalcule le panier.
 */
define([
    'ko',
    'uiComponent',
    'MadameAiguille_Checkout/js/model/gift-wrap'
], function (ko, Component, giftWrap) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'MadameAiguille_Checkout/gift-wrap'
        },

        initialize: function () {
            this._super();

            this.config = giftWrap.config;
            this.requested = giftWrap.requested;
            this.sender = giftWrap.sender;
            this.recipient = giftWrap.recipient;
            this.message = giftWrap.message;
            this.maxLength = giftWrap.maxLength;
            this.remaining = ko.pureComputed(function () {
                return this.maxLength - this.message().length;
            }, this);

            return this;
        }
    });
});
