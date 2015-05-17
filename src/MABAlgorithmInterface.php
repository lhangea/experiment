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
   * Decides which variation to show.
   *
   * When the system needs to decide which variation to show it will simply
   * call this method on an experiment object and this method should return the
   * id of the variation.
   *
   * @return string
   *   The variation identifier.
   */
  public function select();

  /**
   * Recomputes the mean reward for a variation.
   *
   * This method is called when a certain variation is shown. It updates
   * the mean reward for the shown variation right when showing the variation
   *
   * @param string $variation_id
   *  The id of the variation.
   *
   * @return NULL
   */
  public function updateAverageWithNullReward($variation_id);

  /**
   * Updates the mean reward.
   *
   * @param string $variation_id
   *   The if of the variation.
   * @param float $reward
   *   A positive float number representing the reward for a achieving a goal.
   *
   * @return NULL
   */
  public function updateAverageWithReward($variation_id, $reward);

}
