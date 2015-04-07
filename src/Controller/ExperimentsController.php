<?php



namespace Drupal\experiment\Controller;

use Symfony\Component\HttpFoundation\Response;

class ExperimentsController {

  public function getBlock($block_id) {
    $response = new Response();
    $response->setContent(json_encode(array(
      'html' => 'Block content for id: ' . $block_id,
    )));
    $response->headers->set('Content-Type', 'application/json');

    return $response;
  }
}
