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
                var css_class = (drupalSettings.selectedLinks.indexOf(index) > -1) ? "condition-selected" : "condition-selectee";
                $(this)
                    .addClass(css_class)
                    .click(function( event ) {
                        event.preventDefault();
                        $(this).toggleClass("condition-selectee").toggleClass("condition-selected");
                    });
            });
            // When clicking on save button change the values of the hidden
            // input fields for this block. The closing of the modal is done
            // by a Drupal Ajax Command.
            $(".allowed-submit").click(function() {
                var links = $("#drupal-modal").find("div a, input[type=submit]:not('.allowed-submit'), button");
                var selected = [];
                links.each(function( index, element ) {
                    if ($(this).hasClass("condition-selected")) {
                        selected.push(index);
                    }
                });
                if (selected.length == 0) {
                    selected.push(-1);
                }
                $("input[name=\"" + drupalSettings.hiddenInputName + "\"]").val(selected);
                $("#edit-variations-set-unused").trigger('change');
            });
        }
    }

})(jQuery, Drupal, drupalSettings);
