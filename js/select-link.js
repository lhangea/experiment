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
            $( ".region-content" ).selectable({ filter: 'a' });
        }
    }

})(jQuery);
