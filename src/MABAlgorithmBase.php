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
   * @var Array Holds the number of times each variation has been shown.
   */
  protected $counts;

  /**
   * @var Array Holds the average reward for each variation.
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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, StateInterface $state) {
    $this->state = $state;
    $results = $state->get('experiment.' . $configuration['experiment_id']);
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
        $container->get('state')
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
    $form['epsilon'] = [
      '#type' => 'textfield',
      '#title' => t('Epsilon'),
      '#default_value' => (isset($this->configuration['epsilon'])) ? $this->configuration['epsilon'] : NULL,
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Validate the epsilon for values between 0 and 1 inclusive.
    if (!$this->isFloatBetweenZeroAndOne($form_state->getValue('epsilon'))) {
      $form_state->setErrorByName('epsilon', $this->t('Epsilon value must be a decimal between 0 and 1'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['epsilon'] = $form_state->getValue('epsilon');
  }

  /**
   * Helper function which generates a random number.
   *
   * @return float
   *   Random number between 0 and 1.
   *
   * @todo Add unit tests for this.
   */
  public function getRand() {
    return mt_rand() / mt_getrandmax();
  }

  /**
   * Finds the index of the maximum key from an array.
   *
   * @param array
   *   Array containing key value pairs.
   *
   * @return string
   *   The key of the max value from the array.
   *
   * @todo Add unit tests for this.
   */
  function getIndMax($array) {
    $max_key = -1;
    $max_val = -1;

    // Logical or between all array elements to determine if we are right after
    // a new experiment has been initialized.
    $initial = !in_array(TRUE, $array, FALSE);
    if ($initial) {
      return array_rand($array);
    }
    else {
      foreach ($array as $key => $value) {
        if ($value > $max_val) {
          $max_key = $key;
          $max_val = $value;
        }
      }
      return $max_key;
    }
  }

  /**
   * Checks if the float value of a string is a decimal number between 0 and 1.
   *
   * @param $string
   *   The string to be checked.
   * @return bool
   *   TRUE if the value is between 0 and 1 (inclusive)
   *   FALSE otherwise
   *
   * @todo Add unit tests.
   */
  public function isFloatBetweenZeroAndOne($string) {
    $number = floatval($string);

    return $number > 0 && $number <= 1 || $number == 0 && $string == '0';
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

}
