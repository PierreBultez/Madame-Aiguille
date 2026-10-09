/**
 * Joint le point relais choisi aux informations de livraison envoyées au serveur.
 */
define([
    'mage/utils/wrapper',
    'MadameAiguille_Checkout/js/model/relay-point'
], function (wrapper, relayPoint) {
    'use strict';

    return function (payloadExtender) {
        return wrapper.wrap(payloadExtender, function (original, payload) {
            payload = original(payload);

            if (relayPoint.isRelayMethod() && relayPoint.selected()) {
                payload.addressInformation['extension_attributes'] = payload.addressInformation['extension_attributes'] || {};
                payload.addressInformation['extension_attributes']['madameaiguille_relay_point'] = relayPoint.selected();
            }

            return payload;
        });
    };
});
