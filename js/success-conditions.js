/**
 * @file
 * Contains behaviours for recording the selected DOM elements.
 */

(function($) {

    /**
     * Behavior that records the selected DOM elements.
     */
    Drupal.behaviors.experimentSuccessCondition = {
        attach: function(context) {
            $.fn.getSelector = function() {
                var el = this[0];
                if (!el.tagName) {
                    return '';
                }

                // If we have an ID, we're done; that uniquely identifies this element
                var el$ = $(el);
                var id = el$.attr('id');
                if (id) {
                    return '#' + id;
                }

                var classNames = el$.attr('class');
                var classSelector;
                if (classNames) {
                    classSelector = '.' + $.trim(classNames).replace(/\s/gi, '.');
                }

                var selector;
                var parent$ = el$.parent();
                var siblings$ = parent$.children();
                var needParent = false;
                if (classSelector && siblings$.filter(classSelector).length == 1) {
                    // Classes are unique among siblings; use that
                    selector = classSelector;
                } else if (siblings$.filter(el.tagName).length == 1) {
                    // Tag name is unique among siblings; use that
                    selector = el.tagName;
                } else {
                    // Default to saying "nth child"
                    selector = ':nth(' + $(this).index() + ')';
                    needParent = true;
                }

                // Bypass ancestors that don't matter
                if (!needParent) {
                    for (ancestor$ = parent$.parent();
                         ancestor$.length == 1 && ancestor$.find(selector).length == 1;
                         parent$ = ancestor$, ancestor$ = ancestor$.parent());
                    if (ancestor$.length == 0) {
                        return selector;
                    }
                }

                return parent$.getSelector() + ' > ' + selector;
            }

            $("*").click(function() {
                var selector = $(this).getSelector();
                console.log(selector + ' --> matches ' + $(selector).length + ' element');
                return false;
            });
        }
    }

})(jQuery);
