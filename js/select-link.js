/**
* @file
* Javascript behaviors for the experiment module.
*/

(function($, Drupal, drupalSettings) {
    /**
     * Behavior that loads block content.
     */
    Drupal.behaviors.experimentClickSuccessCondition = {
        attach: function(context) {
            var links = $("#drupal-modal").find("div a, input[type=submit]:not('.allowed-submit'), button");
            links.each(function( index, element ) {
                var css_class = (drupalSettings.selectedLinks.indexOf(index) != -1) ? "condition-selected" : "condition-selectee";
                $(this)
                    .addClass(css_class)
                    .click(function( event ) {
                        event.preventDefault();
                        $(this).toggleClass("condition-selectee").toggleClass("condition-selected");
                    });
            });
        }
    }

})(jQuery, Drupal, drupalSettings);
