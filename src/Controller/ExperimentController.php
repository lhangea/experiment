<?php

/**
 * @file
 * Contains \Drupal\experiment\Controller\ExperimentController.
 */

namespace Drupal\experiment\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\PageCache\ResponsePolicyInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\experiment\ExperimentInterface;
use Drupal\experiment\MABAlgorithmManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
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
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * @var \Drupal\Core\PageCache\ResponsePolicyInterface
   */
  protected $pageCacheKillSwitch;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs an ExperimentController object.
   *
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   The block manager.
   * @param \Drupal\experiment\MABAlgorithmManagerInterface $mab_algorithm_manager
   *   The multi armed bandit algorithm manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\Core\PageCache\ResponsePolicyInterface
   *   Page cache kill switch service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   */
  public function __construct(BlockManagerInterface $block_manager, MABAlgorithmManagerInterface $mab_algorithm_manager, RendererInterface $renderer, StateInterface $state, ResponsePolicyInterface $page_cache_kill_switch, ConfigFactoryInterface $config_factory) {
    $this->blockManager = $block_manager;
    $this->mabAlgorithmManager = $mab_algorithm_manager;
    $this->renderer = $renderer;
    $this->state = $state;
    $this->pageCacheKillSwitch = $page_cache_kill_switch;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('plugin.manager.block'),
      $container->get('plugin.manager.mab_algorithm'),
      $container->get('renderer'),
      $container->get('state'),
      $container->get('page_cache_kill_switch'),
      $container->get('config.factory')
    );
  }

  /**
   * Generates html content for the experiment received as argument.
   *
   * @param ExperimentInterface $experiment
   *   The experiment object.
   * @param Request $request
   *   Request object.
   *
   * @return Response
   *   JSON response for the given experiment.
   */
  public function getBlockContent(ExperimentInterface $experiment, Request $request) {
    $config = $this->configFactory->get('experiment.settings');
    $algorithm = $this->mabAlgorithmManager->createInstanceFromExperiment($experiment);
    $response = new Response();
    // Prevent page caching for the current request.
    $this->pageCacheKillSwitch->trigger();
    $plugin_id_from_cookie = $request->cookies->get($experiment->id());
    if ($config->get('use_cookies') && $plugin_id_from_cookie) {
      $selected_plugin = $plugin_id_from_cookie;
    }
    else {
      $selected_plugin = $algorithm->select();
      if ($config->get('use_cookies')) {
        // Set a cookie for 2 minutes.
        setrawcookie($experiment->id(), $selected_plugin, REQUEST_TIME + 120, '/');
      }
    }
    $plugin_id = $selected_plugin;
    $view_mode = FALSE;
    $algorithm->updateAverageWithNullReward($selected_plugin);
    $index = strrpos($selected_plugin, '+');
    // If we are dealing with a 'content' block.
    if ($index !== FALSE) {
      $plugin_id = substr($selected_plugin, 0, $index);
      $view_mode = substr($selected_plugin, $index + 1, strlen($selected_plugin));
    }
    $config = [];
    if ($view_mode) {
      $config['view_mode'] = $view_mode;
    }
    $blocks = $experiment->getBlocks();
    $selected_links = [];
    foreach ($blocks as $block) {
      if ($block['machine_name'] == $plugin_id && $block['view_mode'] == $view_mode) {
        $selected_links = array_map('intval', explode(',', $block['selected_links']));
      }
    }
    $selected_block = $this->blockManager->createInstance($plugin_id, $config);
    $block_html = $selected_block->build();
    $response->setContent(JSON::encode([
      'block_html' => $this->renderer->render($block_html),
      'selected_plugin' => $selected_plugin,
      'selected_links' => $selected_links,
    ]));
    $response->headers->set('Content-Type', 'application/json');

    return $response;
  }

  /**
   * Update the experiment results when a seccess condition is met.
   *
   * @param ExperimentInterface $experiment
   *   Experiment entity.
   * @param Request $request
   *   Request object.
   *
   * @return Response
   *   201 response meaning successful POST request.
   */
  public function updateExperimentResults(ExperimentInterface $experiment, Request $request) {
    $parameters = $request->request->all();
    $algorithm = $this->mabAlgorithmManager->createInstanceFromExperiment($experiment);
    $algorithm->updateAverageWithReward($parameters['variation_id'], $parameters['reward']);

    return new Response($this->t('The experiment results were successfully updated'), 201);
  }

  /**
   * Results page for an experiment.
   *
   * @param ExperimentInterface $experiment
   *   The experiment entity.
   *
   * @return array
   *   Render array containing the results.
   */
  public function experimentResults(ExperimentInterface $experiment) {
    $build = [];
    $blocks = $experiment->getBlocks();
    $results = $this->state->get('experiment.' . $experiment->id());
    $build['experiment_name'] = [
      '#markup' => '<h2>' . $this->t('Results for ') . '<em>' . $experiment->label() . '</em></h2>',
    ];
    $build['table'] = [
      '#type' => 'table',
      '#header' => [$this->t('Block'), $this->t('View Mode'), $this->t('Impressions'), $this->t('Value')],
    ];
    foreach ($blocks as $block) {
      $definition = $this->blockManager->getDefinition($block['machine_name']);
      $id = ($block['view_mode']) ? $block['machine_name'] . '+' . $block['view_mode'] : $block['machine_name'];
      $build['table'][$id][]['#markup'] = SafeMarkup::checkPlain($this->t($definition['admin_label']));
      $build['table'][$id][]['#markup'] = $block['view_mode'];
      $build['table'][$id][]['#markup'] = $results['counts'][$id];
      $build['table'][$id][]['#markup'] = $results['values'][$id];
    }

    return $build;
  }

}
