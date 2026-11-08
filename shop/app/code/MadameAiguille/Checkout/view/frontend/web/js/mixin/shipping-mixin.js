/**
 * L'étape Livraison ne se valide pas sans point relais (livraison en point relais),
 * sans créneau (retrait à l'atelier), ni avec une carte cadeau incomplète. Le serveur refuse de toute façon ; ceci évite l'aller-retour.
 */
define([
    'mage/translate',
    'MadameAiguille_Checkout/js/model/relay-point',
    'MadameAiguille_Checkout/js/model/pickup-slot',
    'MadameAiguille_Checkout/js/model/gift-wrap'
], function ($t, relayPoint, pickupSlot, giftWrap) {
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

                if (giftWrap.isIncomplete()) {
                    this.errorValidationMessage(
                        $t('To add gift wrapping, please fill in who it is from, who it is for and your message.')
                    );

                    return false;
                }

                return this._super();
            }
        });
    };
});
