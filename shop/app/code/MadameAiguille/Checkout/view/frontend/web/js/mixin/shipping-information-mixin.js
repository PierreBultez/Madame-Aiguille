/**
 * Récapitulatif de l'étape Paiement : le mode de livraison nomme le point relais ou le rendez-vous choisi,
 * l'adresse affichée côté navigateur restant celle saisie par la cliente.
 */
define([
    'MadameAiguille_Checkout/js/model/relay-point',
    'MadameAiguille_Checkout/js/model/pickup-slot'
], function (relayPoint, pickupSlot) {
    'use strict';

    return function (ShippingInformation) {
        return ShippingInformation.extend({
            getShippingMethodTitle: function () {
                var title = this._super(),
                    point = relayPoint.selected();

                if (relayPoint.isRelayMethod() && point) {
                    return title + ' — ' + point.name + ', ' + point.city;
                }

                if (pickupSlot.isPickupMethod() && pickupSlot.selected()) {
                    return title + ' — ' + pickupSlot.fullLabel(pickupSlot.selected());
                }

                return title;
            }
        });
    };
});
