<?php

namespace Drupal\experiment\Controller;

class ExperimentsController {
  public function listing() {
    return [
      '#markup' => 'This is really an experiment.'
    ];
  }
}