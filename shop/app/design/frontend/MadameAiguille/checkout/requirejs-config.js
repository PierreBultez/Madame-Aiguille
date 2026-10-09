var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/payment/default': {
                'js/view/payment-title': true
            },
            'MadameAiguille_Checkout/js/view/relay-point': {
                'js/view/relay-accessibility': true
            },
            'Magento_CheckoutAgreements/js/view/checkout-agreements': {
                'js/view/agreements-link': true
            },
            'Magento_Checkout/js/view/summary/abstract-total': {
                'js/summary/full-mode': true
            },
            'Magento_Checkout/js/view/summary/cart-items': {
                'js/summary/expanded-items': true
            }
        }
    }
};
