/**
 * L'étape Livraison ne se valide pas en point relais tant qu'aucun point n'est choisi.
 * Le serveur refuse de toute façon (Plugin\Checkout\AssignRelayPoint) ; ceci évite l'aller-retour.
 */
define([
    'mage/translate',
    'MadameAiguille_Checkout/js/model/relay-point'
], function ($t, relayPoint) {
    'use strict';

    return function (Shipping) {
        return Shipping.extend({
            validateShippingInformation: function () {
                if (relayPoint.isRelayMethod() && !relayPoint.selected()) {
                    this.errorValidationMessage($t('Please choose your relay point.'));

                    return false;
                }

                return this._super();
            }
        });
    };
});
