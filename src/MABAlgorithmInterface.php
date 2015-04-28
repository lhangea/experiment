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
   * this method returns the index of the next arm to pull i.e. block id.
   *
   * @return string
   *   The variation id (i.e. block id).
   */
  public function select();

  /**
   * This method is responsible for updating the algorithm belief about the
   * chosen variation by updating the average reward of the chosen variation.
   *
   * @param $variation_id string
   *   The id of the variation whose results need to be updated.
   * @param $reward float
   *   A numeric reward gained by displaying this variation.
   *
   * @return NULL
   */
  public function update($variation_id, $reward);

}
