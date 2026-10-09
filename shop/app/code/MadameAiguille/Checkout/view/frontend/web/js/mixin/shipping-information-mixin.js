/**
 * Récapitulatif de l'étape Paiement : le mode de livraison nomme le point relais choisi,
 * l'adresse affichée côté navigateur restant celle saisie par la cliente.
 */
define([
    'MadameAiguille_Checkout/js/model/relay-point'
], function (relayPoint) {
    'use strict';

    return function (ShippingInformation) {
        return ShippingInformation.extend({
            getShippingMethodTitle: function () {
                var title = this._super(),
                    point = relayPoint.selected();

                return relayPoint.isRelayMethod() && point ? title + ' — ' + point.name + ', ' + point.city : title;
            }
        });
    };
});
