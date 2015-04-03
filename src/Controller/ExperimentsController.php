<?php

namespace Drupal\experiment\Controller;

class ExperimentsController {
  public function listing() {
    return [
      '#markup' => 'This is really an experiment.'
    ];
  }

  public function add() {
    return [
      '#markup' => 'A new experiment can be added from this page.'
    ];
  }
}