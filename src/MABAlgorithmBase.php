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
   * Helper function which generates a random number.
   *
   * @return float
   *   Random number between 0 and 1.
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
   */
  function getIndMax($array) {
    $max_key = -1;
    $max_val = -1;

    foreach ($array as $key => $value) {
      if ($value > $max_val) {
        $max_key = $key;
        $max_val = $value;
      }
    }

    return $max_key;
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
      '#default_value' => $this->configuration['epsilon'],
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
    // @todo Handle the validation.
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['epsilon'] = $form_state->getValue('epsilon');
  }

}
