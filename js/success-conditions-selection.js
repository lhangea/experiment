/**
 * @file
 * Attaches behavior for the Experiment module.
 */

(function ($, _, Backbone, Drupal, drupalSettings, JSON, storage) {

  "use strict";

  var options = $.extend(drupalSettings.experiment,
    // Merge strings on top of drupalSettings so that they are not mutable.
    {
      strings: {
        experiment: Drupal.t('Success conditions')
      }
    }
  );

  /**
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.experiment = {
    attach: function (context) {
      // Initialize the Experiment app once per page load.
      $('body').once().each(initExperimentEdit);
    }
  };

  /**
   *
   * @namespace
   */
  Drupal.experiment = {

    /**
     * A {@link Drupal.experiment.AppView} instance.
     */
    app: null

  };

  /**
   * Initialize the Experiment app.
   *
   * @param {HTMLElement} bodyElement
   *   This document's body element.
   */
  function initExperimentEdit(bodyElement) {
    console.log('initializing the experiment app');

    // Instantiate AppModel (application state) and AppView, which is the
    // controller of the whole success condition selection process.
    Drupal.experiment.app = new Drupal.experiment.AppView({
      el: bodyElement,
      model: new Drupal.experiment.AppModel()
    });
  }

})(jQuery, _, Backbone, Drupal, drupalSettings, window.JSON, window.sessionStorage);
