/** La liste du widget tiers devient utilisable au clavier, sans changer sa sélection native. */
define([], function () {
    'use strict';

    return function (Component) {
        return Component.extend({
            setWidgetElement: function (element) {
                var enhance = function () {
                    element.querySelectorAll('.PR-List-Item').forEach(function (item) {
                        item.setAttribute('role', 'button');
                        item.setAttribute('tabindex', item.classList.contains('PR-Disabled') ? '-1' : '0');
                    });
                };

                this._super(element);
                this.relayAccessibilityObserver = new MutationObserver(enhance);
                this.relayAccessibilityObserver.observe(element, {childList: true, subtree: true});
                element.addEventListener('keydown', function (event) {
                    if (event.target.matches('.PR-List-Item') && ['Enter', ' '].includes(event.key)) {
                        event.preventDefault();
                        event.target.click();
                    }
                });
                enhance();
            },

            destroy: function () {
                if (this.relayAccessibilityObserver) {
                    this.relayAccessibilityObserver.disconnect();
                }

                return this._super();
            }
        });
    };
});
