/**
 * État partagé du point relais choisi, entre le composant, la validation de l'étape et l'envoi au serveur.
 */
define([
    'ko',
    'Magento_Checkout/js/model/quote'
], function (ko, quote) {
    'use strict';

    var config = window.checkoutConfig.madameaiguilleRelayPoint || {},
        selected = ko.observable(null);

    return {
        config: config,
        selected: selected,

        /**
         * @return {Boolean}
         */
        isRelayMethod: function () {
            var method = quote.shippingMethod();

            return !!method && method['carrier_code'] === config.carrierCode;
        },

        /**
         * Pays de recherche du widget pour un pays d'adresse (Monaco → réseau français).
         *
         * @param {String} countryId
         * @return {String}
         */
        searchCountry: function (countryId) {
            return (config.searchCountries || {})[countryId] || countryId;
        }
    };
});
