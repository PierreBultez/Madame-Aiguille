/**
 * Joint le point relais ou le créneau de retrait choisi aux informations de livraison envoyées au serveur.
 */
define([
    'mage/utils/wrapper',
    'MadameAiguille_Checkout/js/model/relay-point',
    'MadameAiguille_Checkout/js/model/pickup-slot'
], function (wrapper, relayPoint, pickupSlot) {
    'use strict';

    return function (payloadExtender) {
        return wrapper.wrap(payloadExtender, function (original, payload) {
            var attributes;

            payload = original(payload);
            attributes = payload.addressInformation['extension_attributes'] =
                payload.addressInformation['extension_attributes'] || {};

            if (relayPoint.isRelayMethod() && relayPoint.selected()) {
                attributes['madameaiguille_relay_point'] = relayPoint.selected();
            }

            if (pickupSlot.isPickupMethod() && pickupSlot.selected()) {
                attributes['madameaiguille_pickup_slot'] = pickupSlot.selected();
            }

            return payload;
        });
    };
});
