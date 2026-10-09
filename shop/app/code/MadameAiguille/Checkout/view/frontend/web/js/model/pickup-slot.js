/**
 * État partagé du créneau de retrait choisi, entre le composant, la validation de l'étape et l'envoi au serveur.
 */
define([
    'ko',
    'Magento_Checkout/js/model/quote'
], function (ko, quote) {
    'use strict';

    var config = window.checkoutConfig.madameaiguillePickup || {},
        selected = ko.observable(null);

    /**
     * « 2026-10-15 10:00 » → Date locale, sans conversion de fuseau.
     *
     * @param {String} slot
     * @return {Date}
     */
    function toDate(slot) {
        var parts = slot.split(/[- :]/);

        return new Date(+parts[0], +parts[1] - 1, +parts[2], +parts[3], +parts[4]);
    }

    return {
        config: config,
        selected: selected,

        /**
         * @return {Boolean}
         */
        isPickupMethod: function () {
            var method = quote.shippingMethod();

            return !!method && method['carrier_code'] === config.carrierCode;
        },

        /**
         * @param {String} slot
         * @return {String} « jeudi 15 octobre »
         */
        dayLabel: function (slot) {
            return toDate(slot).toLocaleDateString('fr-FR', {weekday: 'long', day: 'numeric', month: 'long'});
        },

        /**
         * @param {String} slot
         * @return {String} « 10 h 00 »
         */
        timeLabel: function (slot) {
            var date = toDate(slot);

            return date.getHours() + ' h ' + ('0' + date.getMinutes()).slice(-2);
        },

        /**
         * @param {String} slot
         * @return {String} « jeudi 15 octobre à 10 h 00 »
         */
        fullLabel: function (slot) {
            return this.dayLabel(slot) + ' à ' + this.timeLabel(slot);
        }
    };
});
