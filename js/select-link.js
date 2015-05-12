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
            $("#drupal-modal div").selectable({
                filter: "a"
            });
        }
    }

})(jQuery);
