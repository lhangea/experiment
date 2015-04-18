<?php

/**
 * @file
 */


namespace Drupal\experiment;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Plugin\PluginFormInterface;

abstract class MABAlgorithmBase extends PluginBase implements MABAlgorithmInterface, ConfigurablePluginInterface, PluginFormInterface {

  /**
   * Generates a random number.
   *
   * @return float
   *   Random number between 0 and 1.
   */
  public function getRand() {
    return mt_rand() / mt_getrandmax();
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration()
  {
    // TODO: Implement getConfiguration() method.
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration)
  {
    // TODO: Implement setConfiguration() method.
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration()
  {
    // TODO: Implement defaultConfiguration() method.
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies()
  {
    // TODO: Implement calculateDependencies() method.
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state)
  {
    // TODO: Implement buildConfigurationForm() method.
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state)
  {
    // TODO: Implement validateConfigurationForm() method.
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state)
  {
    // TODO: Implement submitConfigurationForm() method.
  }
}