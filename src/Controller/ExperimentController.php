<?php

/**
 * @file ...
 */

namespace Drupal\experiment\Controller;

use Symfony\Component\HttpFoundation\Response;

class ExperimentController {

  // @todo maybe use automatic parameter conversion for Experiment entity.
  public function selectArm($experiment_id) {

    // @todo Inject the service.
    $storage = \Drupal::entityManager()
      ->getStorage('experiment');

    $experiment = $storage->load($experiment_id);

    $blocks = $experiment->getBlocks();

    $response = new Response();
    $response->setContent(json_encode(array(
      'html' => 'Block content for block: ' . $blocks[rand(0, 1)],
    )));
    $response->headers->set('Content-Type', 'application/json');

    return $response;
  }
}
