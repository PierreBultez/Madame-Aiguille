/**
 * Rendez-vous de retrait : lieu, puis choix du jour et de l'heure parmi les créneaux encore libres.
 * Les créneaux sont lus à l'affichage ; le serveur revérifie au passage de l'étape et réserve à la commande.
 */
define([
    'ko',
    'uiComponent',
    'mage/storage',
    'Magento_Checkout/js/model/url-builder',
    'MadameAiguille_Checkout/js/model/pickup-slot'
], function (ko, Component, storage, urlBuilder, pickupSlot) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'MadameAiguille_Checkout/pickup-slot'
        },

        initialize: function () {
            this._super();

            this.location = pickupSlot.config.location || {};
            this.addressLines = String(this.location.address || '').split(/\r?\n/).filter(Boolean);
            this.selected = pickupSlot.selected;
            this.slots = ko.observableArray([]);
            this.loading = ko.observable(false);
            this.loaded = ko.observable(false);
            this.selectedDay = ko.observable(null);
            this.isVisible = ko.pureComputed(pickupSlot.isPickupMethod);

            this.days = ko.pureComputed(function () {
                var seen = {};

                return this.slots().reduce(function (days, slot) {
                    var day = slot.substr(0, 10);

                    if (!seen[day]) {
                        seen[day] = true;
                        days.push({value: day, label: pickupSlot.dayLabel(slot)});
                    }

                    return days;
                }, []);
            }, this);

            this.times = ko.pureComputed(function () {
                var day = this.selectedDay();

                return this.slots().filter(function (slot) {
                    return slot.substr(0, 10) === day;
                }).map(function (slot) {
                    return {value: slot, label: pickupSlot.timeLabel(slot)};
                });
            }, this);

            this.selectedDay.subscribe(function (day) {
                if (this.selected() && this.selected().substr(0, 10) !== day) {
                    this.selected(null);
                }
            }, this);

            this.isVisible.subscribe(this.load, this);
            this.load(this.isVisible());

            return this;
        },

        /**
         * @param {Boolean} visible
         */
        load: function (visible) {
            if (!visible || this.loading()) {
                return;
            }
            this.loading(true);

            storage.get(urlBuilder.createUrl('/madameaiguille/pickup-slots', {}), false)
                .done(function (slots) {
                    this.slots(slots || []);
                    if (this.selected() && (slots || []).indexOf(this.selected()) === -1) {
                        this.selected(null);
                    }
                    if (!this.selectedDay() && this.days().length) {
                        this.selectedDay(this.days()[0].value);
                    }
                }.bind(this))
                .always(function () {
                    this.loading(false);
                    this.loaded(true);
                }.bind(this));
        },

        /**
         * @param {String} slot
         * @return {String}
         */
        fullLabel: function (slot) {
            return pickupSlot.fullLabel(slot);
        }
    });
});
