/** Traduire l'intitulé visible sans modifier les méthodes ni leur configuration. */
define(['mage/translate'], function ($t) {
    'use strict';

    return function (Component) {
        return Component.extend({
            getTitle: function () {
                var title = this._super();

                if (title === 'Pay with Klarna.' || title === 'Pay with Klarna') {
                    return $t('Pay with Klarna.');
                }

                return $t(title);
            }
        });
    };
});
