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

    var pageModel = new Drupal.experiment.PageModel({
      el: "content",
      pageID: "test",
      pageInstanceID: "test",
      id: "content"
    });

    // Initialization should only be called once. Use Underscore's once method
    // to get a one-time use version of the function.
    var initContextualLink = _.once(function () {
      var $links = $('#content');
      var contextualLinkView = new Drupal.experiment.ContextualLinkView($.extend({
        el: $('<li class="experiment"><a href="" role="button" aria-pressed="false"></a></li>').prependTo($links),
        model: pageModel,
        appModel: Drupal.experiment.app.model
      }, options));
      pageModel.set('contextualLinkView', contextualLinkView);
    });

    initContextualLink();
  }

})(jQuery, _, Backbone, Drupal, drupalSettings, window.JSON, window.sessionStorage);
