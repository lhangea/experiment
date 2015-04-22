<?php

/**
 * @file
 * Contains \Drupal\experiment\MABAlgorithmInterface.
 */

namespace Drupal\experiment;

use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;

/**
 * Defines an interface for multi armed bandit algorithm plugin definitions.
 */
interface MABAlgorithmInterface extends ConfigurablePluginInterface, PluginFormInterface {

  /**
   * When for a certain experiment we need to know which variation to show,
   * the plugin manager simply calls this method on the experiment instance and
   * this method should return the index of the next arm to pull i.e. block id.
   */
  public function select();

  /**
   * @todo Add doc here.
   */
  public function update();

}
