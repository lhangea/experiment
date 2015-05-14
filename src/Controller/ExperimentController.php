<?php

/**
 * @file
 * Contains \Drupal\experiment\Controller\ExperimentController.
 */

namespace Drupal\experiment\Controller;

use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\experiment\ExperimentInterface;
use Drupal\experiment\MABAlgorithmManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class ExperimentController implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The block manager.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * The multi armed bandit algorithm manager.
   *
   * @var \Drupal\experiment\MABAlgorithmInterface
   */
  protected $mabAlgorithmManager;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs an ExperimentController object.
   *
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   The block manager.
   * @param \Drupal\experiment\MABAlgorithmManagerInterface $mab_algorithm_manager
   *   The multi armed bandit algorithm manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(BlockManagerInterface $block_manager, MABAlgorithmManagerInterface $mab_algorithm_manager, RendererInterface $renderer) {
    $this->blockManager = $block_manager;
    $this->mabAlgorithmManager = $mab_algorithm_manager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('plugin.manager.block'),
      $container->get('plugin.manager.mab_algorithm'),
      $container->get('renderer')
    );
  }

  /**
   * Generates html content for the experiment received as argument.
   *
   * @param ExperimentInterface $experiment
   *   The experiment object.
   *
   * @return Response
   *   JSON response for the given experiment.
   */
  public function getBlockContent(ExperimentInterface $experiment) {
    $algorithm = $this->mabAlgorithmManager->createInstanceFromExperiment($experiment);
    $response = new Response();

    $selected_plugin = $algorithm->select();
    $index = strrpos($selected_plugin, ':');
    $plugin_id = substr($selected_plugin, 0, $index);
    $view_mode = substr($selected_plugin, $index + 1, strlen($selected_plugin));
    $config = [];
    if ($view_mode) {
      $config['view_mode'] = $view_mode;
    }
    $block = $this->blockManager->createInstance($plugin_id, $config);
    $response->setContent(json_encode([
      'html' => $this->renderer->render($block->build()),
    ]));
    $response->headers->set('Content-Type', 'application/json');

    return $response;
  }

}
