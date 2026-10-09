/**
 * Widget Mondial Relay : chargé à la demande, seulement quand la cliente choisit le point relais.
 * Même URL Leaflet que le chargeur interne du widget : sa déduplication compare les src exacts.
 * Une URL versionnée différente lui fait réinjecter un module AMD anonyme et interrompt RequireJS.
 * Les deux ressources distantes suivent désormais les versions servies par leurs éditeurs.
 */
var config = {
    paths: {
        'madameaiguille/leaflet': 'https://unpkg.com/leaflet/dist/leaflet',
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
