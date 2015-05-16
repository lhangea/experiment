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
                    urlRoot: '/experiment'
                });
                new BlockModel({ id: drupalSettings.experiment_id }).fetch({
                    success: function (collection, response) {
                        var block_content = $('.' + drupalSettings.experiment_id + ' .content');
                        block_content.html(response.block_html);

                        var links = block_content.find("div a, input[type=submit], button");
                        links.each(function( index, element ) {
                            $(this).click(function( event ) {
                                if (response.selected_links.indexOf(index) > -1) {
                                    $.post( "/experiment/" + drupalSettings.experiment_id, {
                                        variation_id: response.selected_plugin,
                                        reward: 1
                                    });
                                }
                            });
                        });
                    }
                });
            }
        }
    }

})(jQuery);

