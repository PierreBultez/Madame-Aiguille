/**
 * Carte de sélection du point relais Mondial Relay (widget officiel, code enseigne seul).
 *
 * Le widget n'est chargé qu'au premier choix du point relais : aucune requête vers Mondial Relay,
 * unpkg ou OpenStreetMap tant que la cliente ne l'a pas demandé.
 */
define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'MadameAiguille_Checkout/js/model/relay-point'
], function ($, ko, Component, quote, relayPoint) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'MadameAiguille_Checkout/relay-point',
            widgetId: 'madameaiguille-relay-widget',
            targetId: 'madameaiguille-relay-target'
        },

        widget: null,
        searchedFor: null,

        initialize: function () {
            this._super();

            this.selected = relayPoint.selected;
            this.isVisible = ko.pureComputed(relayPoint.isRelayMethod);
            this.isVisible.subscribe(this.start, this);
            quote.shippingAddress.subscribe(this.search, this);

            return this;
        },

        /**
         * @param {HTMLElement} element
         */
        setWidgetElement: function (element) {
            this.widget = $(element);
            element.addEventListener('click', this.focusItem.bind(this), true);
            this.start(this.isVisible());
        },

        /**
         * Chaque point de la liste porte un onclick inline, refusé par la CSP du tunnel.
         * On le retire avant qu'il ne se déclenche et on rejoue son action : centrer et sélectionner le point.
         *
         * @param {Event} event
         */
        focusItem: function (event) {
            var item = $(event.target).closest('.PR-List-Item', this.widget[0]);

            if (!item.length) {
                return;
            }
            item.removeAttr('onclick');

            if (!item.hasClass('PR-Disabled')) {
                this.widget.trigger('FocusOnMap', this.widget.find('.PR-List-Item').index(item));
            }
        },

        /**
         * @param {Boolean} visible
         */
        start: function (visible) {
            if (!visible || !this.widget || this.widget.data('madameaiguilleStarted')) {
                return;
            }
            this.widget.data('madameaiguilleStarted', true);

            require(['madameaiguille/leaflet'], function (leaflet) {
                // Leaflet chargé par RequireJS ne crée pas la variable globale attendue par le widget
                window.L = window.L || leaflet;
                require(['madameaiguille/mondialRelayWidget'], this.render.bind(this));
            }.bind(this));
        },

        render: function () {
            var address = quote.shippingAddress() || {},
                country = relayPoint.searchCountry(address.countryId || 'FR');

            // Normalement déclarés par un script inline du widget, que la CSP du tunnel peut bloquer
            window.MondialRelayLanguage = window.MondialRelayLanguage || {Horaires: 'Horaires', Photo: 'Photo'};

            this.searchedFor = country + ':' + (address.postcode || '');
            this.widget.MR_ParcelShopPicker({
                Target: '#' + this.targetId,
                Brand: relayPoint.config.brand,
                Country: country,
                AllowedCountries: country,
                PostCode: address.postcode || '',
                ColLivMod: '24R',
                NbResults: 7,
                Responsive: true,
                EnableLoadMore: false,
                OnParcelShopSelected: this.select.bind(this)
            });
        },

        /**
         * Nouvelle recherche quand l'adresse change ; un point d'un autre pays n'est plus valable.
         *
         * @param {Object} address
         */
        search: function (address) {
            var country, key;

            if (!address || !this.widget || !this.widget.data('madameaiguilleStarted')) {
                return;
            }
            country = relayPoint.searchCountry(address.countryId || 'FR');
            key = country + ':' + (address.postcode || '');

            if (this.selected() && this.selected()['country_id'] !== country) {
                this.selected(null);
            }
            if (key !== this.searchedFor && address.postcode) {
                this.searchedFor = key;
                this.widget.trigger('MR_SetParams', {Country: country, AllowedCountries: country});
                this.widget.trigger('MR_DoSearch', [address.postcode, country]);
            }
        },

        /**
         * @param {Object} data point renvoyé par le widget
         */
        select: function (data) {
            this.selected({
                id: data.Pays + '-' + data.ID,
                name: $.trim(data.Nom),
                street: [$.trim(data.Adresse1), $.trim(data.Adresse2)].filter(Boolean),
                postcode: $.trim(data.CP),
                city: $.trim(data.Ville),
                'country_id': data.Pays
            });
        }
    });
});
