<?php

/**
 * @file ...
 */

namespace Drupal\experiment\Controller;

use Symfony\Component\HttpFoundation\Response;

class ExperimentController {

  // @todo Use automatic parameter conversion for Experiment entity.
  public function selectArm($experiment_id) {

    // @todo Inject the service.
    $storage = \Drupal::entityManager()
      ->getStorage('experiment');

    $experiment = $storage->load($experiment_id);
    $mabAlgorithmManager = \Drupal::getContainer()->get('plugin.manager.mab_algorithm');
    // @todo Here we need to pass in the algorithm configuration for this
    //   specific experiment.
    $algorithm = $mabAlgorithmManager->createInstance($experiment->getAlgorithm());

    $response = new Response();
    $blockManager = \Drupal::getContainer()->get('plugin.manager.block');
    $block = $blockManager->createInstance($algorithm->select());
    $response->setContent(json_encode(array(
      'html' => \Drupal::service('renderer')->render($block->build()),
    )));
    $response->headers->set('Content-Type', 'application/json');

    return $response;
  }
}
