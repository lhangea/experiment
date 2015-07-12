<?php

/**
 * @file
 * Contains \Drupal\experiment\MABAlgorithmBase.
 */

namespace Drupal\experiment;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for multi armed bandit algorithms plugins.
 */
abstract class MABAlgorithmBase extends PluginBase implements MABAlgorithmInterface, ContainerFactoryPluginInterface {

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Contains useful methods for MAB algorithms.
   *
   * @var \Drupal\experiment\AlgorithmUtility
   */
  protected $utility;

  /**
   * @var array Holds the number of times each variation has been shown.
   */
  protected $counts;

  /**
   * @var array Holds the average reward for each variation.
   */
  protected $values;

  /**
   * Constructs a FieldDiffBuilderBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   * @param \Drupal\experiment\AlgorithmUtility $utility
   *   The algorithm utility service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, StateInterface $state, AlgorithmUtility $utility) {
    $this->state = $state;
    $this->utility = $utility;
    // @todo Here we probably need to have a separate method for getting the
    //   algorithm configuration form (that's why we need to instantiate and
    //   algorithm plugin when we don't have the experiment id yet).
    $experiment_id = (isset($configuration['experiment_id'])) ? $configuration['experiment_id'] : '';
    $results = $state->get('experiment.' . $experiment_id);
    $this->values = $results['values'];
    $this->counts = $results['counts'];
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->configuration += $this->defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('state'),
      $container->get('algorithm.utility')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {

  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * Saves the updated results.
   */
  public function saveResults() {
    $results = [
      'counts' => $this->counts,
      'values' => $this->values,
    ];
    $this->state->set('experiment.' . $this->configuration['experiment_id'], $results);
  }

  /**
   * {@inheritdoc}
   */
  public function updateAverageWithNullReward($variation_id)
  {
    // Only update if the variation returned belongs to the experiment.
    if (isset($this->counts[$variation_id])) {
      $this->counts[$variation_id] += 1;
      $n = $this->counts[$variation_id];
      $value = $this->values[$variation_id];
      $new_value = (($n - 1) / (float)$n) * $value;
      $this->values[$variation_id] = $new_value;
      $this->saveResults();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function updateAverageWithReward($variation_id, $reward)
  {
    // Only update if the variation returned belongs to the experiment.
    if (isset($this->values[$variation_id])) {
      $n = $this->counts[$variation_id];
      $this->values[$variation_id] += (1 / (float)$n) * (float)$reward;
      $this->saveResults();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getViews() {
    return $this->counts;
  }

  /**
   * {@inheritdoc}
   */
  public function getValues() {
    return $this->values;
  }

  /**
   * {@inheritdoc}
   */
  public function getComputedValues() {
    return $this->values;
  }

}
