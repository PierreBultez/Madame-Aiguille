/**
 * Case « Emballage cadeau » de l'étape Livraison. Le montant s'ajoute au total au passage à l'étape suivante,
 * quand Magento recalcule le panier.
 */
define([
    'uiComponent',
    'MadameAiguille_Checkout/js/model/gift-wrap'
], function (Component, giftWrap) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'MadameAiguille_Checkout/gift-wrap'
        },

        initialize: function () {
            this._super();

            this.config = giftWrap.config;
            this.requested = giftWrap.requested;

            return this;
        }
    });
});
