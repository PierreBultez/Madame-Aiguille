/** Consentement facultatif, jamais mémorisé ni précoché au chargement du tunnel. */
define(['ko', 'uiComponent', 'Magento_Checkout/js/model/payment/place-order-hooks'], function (ko, Component, hooks) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/newsletter'
        },

        initialize: function () {
            this._super();
            this.enabled = Boolean(window.checkoutConfig.madameaiguilleNewsletterEnabled);
            this.requested = ko.observable(false);
            hooks.requestModifiers.push(function (headers, payload) {
                var payment = payload.paymentMethod;

                payment.additional_data = payment.additional_data || {};
                payment.additional_data.madameaiguille_newsletter = this.enabled && this.requested();
            }.bind(this));

            return this;
        }
    });
});
