/**
 * L'étape Livraison ne se valide pas sans point relais (livraison en point relais)
 * ni sans créneau (retrait à l'atelier). Le serveur refuse de toute façon ; ceci évite l'aller-retour.
 */
define([
    'mage/translate',
    'MadameAiguille_Checkout/js/model/relay-point',
    'MadameAiguille_Checkout/js/model/pickup-slot'
], function ($t, relayPoint, pickupSlot) {
    'use strict';

    return function (Shipping) {
        return Shipping.extend({
            validateShippingInformation: function () {
                if (relayPoint.isRelayMethod() && !relayPoint.selected()) {
                    this.errorValidationMessage($t('Please choose your relay point.'));

                    return false;
                }

                if (pickupSlot.isPickupMethod() && !pickupSlot.selected()) {
                    this.errorValidationMessage($t('Please choose your pickup time.'));

                    return false;
                }

                return this._super();
            }
        });
    };
});
