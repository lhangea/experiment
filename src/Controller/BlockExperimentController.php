<?php

/**
 * @file
 * Contains \Drupal\experiment\Controller\BlockExperimentController.
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

class BlockExperimentController extends ExperimentBaseController implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The block manager.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

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
   * Constructs an BlockExperimentController object.
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
    parent::__construct($config_factory, $mab_algorithm_manager);

    $this->blockManager = $block_manager;
    $this->renderer = $renderer;
    $this->state = $state;
    $this->pageCacheKillSwitch = $page_cache_kill_switch;
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
    $response = new Response();
    // Prevent page caching for the current request.
    $this->pageCacheKillSwitch->trigger();
    $selected_plugin = $this->getActionId($experiment, $request);
    $plugin_id = $selected_plugin;
    $view_mode = FALSE;
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
    $blocks = $experiment->getActions();
    $selected_links = [];
    foreach ($blocks as $block) {
      if ($block['machine_name'] == $plugin_id && $block['view_mode'] == $view_mode) {
        $selected_links = array_map('intval', explode(',', $block['selected_links']));
      }
    }
    $selected_block = $this->blockManager->createInstance($plugin_id, $config);
    $block_render_array = $selected_block->build();
    $html = (string) $this->renderer->render($block_render_array);
    $response->setContent(JSON::encode([
      'block_html' => $html,
      'selected_plugin' => $selected_plugin,
      'selected_links' => $selected_links,
    ]));
    $response->headers->set('Content-Type', 'application/json');

    return $response;
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
    // Prepare the data for the page.
    $blocks = $experiment->getActions();
    $algorithm = $this->mabAlgorithmManager->createInstanceFromExperiment($experiment);
    $views = $algorithm->getViews();
    $values = $algorithm->getComputedValues();
    // Builds the page content.
    $build['experiment_name'] = [
      '#markup' => '<h2>' . $this->t('Results for ') . '<em>' . $experiment->label() . '</em></h2>',
    ];
    $build['table'] = [
      '#type' => 'table',
      '#header' => [$this->t('Block'), $this->t('View Mode'), $this->t('Views'), $this->t('Value')],
    ];
    foreach ($blocks as $block) {
      $definition = $this->blockManager->getDefinition($block['machine_name']);
      $id = ($block['view_mode']) ? $block['machine_name'] . '+' . $block['view_mode'] : $block['machine_name'];
      $build['table'][$id][]['#markup'] = SafeMarkup::checkPlain($this->t($definition['admin_label']));
      $build['table'][$id][]['#markup'] = $block['view_mode'];
      $build['table'][$id][]['#markup'] = $views[$id];
      $build['table'][$id][]['#markup'] = sprintf('%0.6f', $values[$id]);;
    }
    $build['details'] = [
      '#markup' => $this->t('The \'Value\' column represents the ratio between total reward and number of visualisations for each variation. It is important to note that for some algorithms like UCB1 the \'Value\' parameter might be different from the ratio because the algorithms add on the fly bonuses to variations in order to optimize the results.'),
    ];

    return $build;
  }

}
