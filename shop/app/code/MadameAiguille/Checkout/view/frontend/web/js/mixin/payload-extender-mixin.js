/**
 * Joint le point relais ou le créneau de retrait choisi, et l'emballage cadeau, aux informations de livraison envoyées au serveur.
 */
define([
    'mage/utils/wrapper',
    'MadameAiguille_Checkout/js/model/relay-point',
    'MadameAiguille_Checkout/js/model/pickup-slot',
    'MadameAiguille_Checkout/js/model/gift-wrap'
], function (wrapper, relayPoint, pickupSlot, giftWrap) {
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

            attributes['madameaiguille_gift_wrap'] = !!giftWrap.requested();

            return payload;
        });
    };
});
