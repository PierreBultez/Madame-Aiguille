/**
 * Ligne « Emballage cadeau » du récapitulatif du tunnel, lue dans les totaux renvoyés par Magento.
 */
define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/totals',
    'MadameAiguille_Checkout/js/model/gift-wrap'
], function (Component, totals, giftWrap) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'MadameAiguille_Checkout/summary/gift-wrap'
        },

        segment: function () {
            return totals.getSegment(giftWrap.config.totalCode || 'madameaiguille_gift_wrap');
        },

        isDisplayed: function () {
            var segment = this.segment();

            return !!segment && parseFloat(segment.value) > 0;
        },

        getTitle: function () {
            return this.segment() ? this.segment().title : '';
        },

        getValue: function () {
            return this.getFormattedPrice(this.segment() ? this.segment().value : 0);
        }
    });
});
