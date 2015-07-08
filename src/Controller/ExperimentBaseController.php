<?php

/**
 * @file
 * Contains \Drupal\experiment\Controller\ExperimentBaseController.
 */

namespace Drupal\experiment\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\experiment\ExperimentInterface;
use Drupal\experiment\MABAlgorithmManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class ExperimentBaseController {

  use StringTranslationTrait;

  /**
   * General configuration for experiments.
   */
  protected $config;

  /**
   * The multi armed bandit algorithm manager.
   *
   * @var \Drupal\experiment\MABAlgorithmInterface
   */
  protected $mabAlgorithmManager;

  /**
   * Constructs an ExperimentBaseController object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\experiment\MABAlgorithmManagerInterface $mab_algorithm_manager
   *   The multi armed bandit algorithm manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, MABAlgorithmManagerInterface $mab_algorithm_manager) {
    $this->config = $config_factory->get('experiment.settings');
    $this->mabAlgorithmManager = $mab_algorithm_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('config.factory'),
      $container->get('plugin.manager.mab_algorithm')
    );
  }

  /**
   * Update the experiment results when a success condition is met.
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
   * Returns the action id decided by the experiment.
   *
   * @param ExperimentInterface $experiment
   *   Experiment object.
   * @param Request $request
   *   Request object.
   *
   * @return string
   *   The selected action id.
   */
  public function getActionId(ExperimentInterface $experiment, Request $request) {
    $algorithm = $this->mabAlgorithmManager->createInstanceFromExperiment($experiment);
    $plugin_id_from_cookie = $request->cookies->get($experiment->id());

    if ($this->config->get('use_cookies') && $plugin_id_from_cookie) {
      $selected_plugin = $plugin_id_from_cookie;
    }
    else {
      $selected_plugin = $algorithm->select();
      if ($this->config->get('use_cookies')) {
        // Set a cookie for 2 minutes.
        setcookie($experiment->id(), $selected_plugin, REQUEST_TIME + 120, '/');
      }
    }
    // Update the mean value for a variation with 0 reward when selected.
    $algorithm->updateAverageWithNullReward($selected_plugin);

    return $selected_plugin;
  }

}
