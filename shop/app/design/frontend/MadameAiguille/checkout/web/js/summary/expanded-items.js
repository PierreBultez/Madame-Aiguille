/** Ouvrir la liste native à chaque étape, comme dans la maquette. */
define([], function () {
    'use strict';

    return function (Component) {
        return Component.extend({
            isItemsBlockExpanded: function () {
                return true;
            }
        });
    };
});
