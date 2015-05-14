/**
* @file
* Javascript behaviors for the experiment module.
*/

(function($) {
    /**
     * Behavior that loads block content.
     */
    Drupal.behaviors.experimentClickSuccessCondition = {
        attach: function(context) {
            $("#drupal-modal")
                .find(" div a, input[type=submit]:not('.allowed-submit'), button")
                .addClass("condition-selectee")
                .click(function( event ) {
                    event.preventDefault();
                    $(this).toggleClass("condition-selectee").toggleClass("condition-selected");
                });
        }
    }

})(jQuery);
