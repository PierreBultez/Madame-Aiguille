/**
 * Widget Mondial Relay : chargé à la demande, seulement quand la cliente choisit le point relais.
 * Leaflet est figé en 1.9.4 ; le widget, lui, n'est pas versionnable (Mondial Relay sert toujours la dernière 4.x).
 */
var config = {
    paths: {
        'madameaiguille/leaflet': 'https://unpkg.com/leaflet@1.9.4/dist/leaflet',
        'madameaiguille/mondialRelayWidget':
            'https://widget.mondialrelay.com/parcelshop-picker/jquery.plugin.mondialrelay.parcelshoppicker.min'
    },
    shim: {
        'madameaiguille/mondialRelayWidget': {
            deps: ['jquery']
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'MadameAiguille_Checkout/js/mixin/shipping-mixin': true
            },
            'Magento_Checkout/js/model/shipping-save-processor/payload-extender': {
                'MadameAiguille_Checkout/js/mixin/payload-extender-mixin': true
            },
            'Magento_Checkout/js/view/shipping-information': {
                'MadameAiguille_Checkout/js/mixin/shipping-information-mixin': true
            }
        }
    }
};
