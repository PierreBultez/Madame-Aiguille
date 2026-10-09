/** Afficher les totaux natifs dès la livraison ; aucun recalcul côté thème. */
define([], function () {
    'use strict';

    return function (Component) {
        return Component.extend({
            isFullMode: function () {
                return Boolean(this.getTotals());
            }
        });
    };
});
