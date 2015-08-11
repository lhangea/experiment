/**
 * @file
 * A Backbone View that controls the overall success condition selection app.
 *
 * @see Drupal.experiment.AppModel
 */

(function ($, _, Backbone, Drupal) {

  "use strict";

  Drupal.experiment.AppView = Backbone.View.extend(/** @lends Drupal.quickedit.AppView# */{

    /**
     *
     */
    initialize: function (options) {
      console.log('initializing the experiment app view');
    }

  });

}(jQuery, _, Backbone, Drupal));
