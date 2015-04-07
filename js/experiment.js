/**
 * @file
 * Javascript behaviors for the experiment module.
 */

(function($) {

    /**
     * Behavior that loads block content.
     */
    Drupal.behaviors.experimentLoadBlock = {
        attach: function(context) {
            // Check if we have an experiment id.
            if (drupalSettings.experiment_id) {
                var BlockModel = Backbone.Model.extend({
                    urlRoot: '/experiments'
                });
                var blockContent = new BlockModel({ id: drupalSettings.experiment_id }).fetch(
                    {
                        success: function (collection, response) {
                            console.log('.' + drupalSettings.experiment_id);
                            $('.' + drupalSettings.experiment_id + ' .content').html(response.html);
                        }
                    }
                );
            }
        }
    }

})(jQuery);

