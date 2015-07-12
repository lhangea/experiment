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
   * id of the selected variation.
   *
   * @return string
   *   The variation identifier.
   */
  public function select();

  /**
   * Recomputes the mean reward for a variation with 0 reward.
   *
   * When a variation is shown, the algorithm needs to compute the new average
   * reward for that variation. Usually that should be done when the user
   * navigates away from the page the variation is displayed on or when the user
   * closes the the tab or the browser. If the user did not click on any of the
   * targeted links then the reward is 0. If the user clicked on 1 or more
   * targeted links then the reward is the sum of the rewards of the clicked
   * links.
   * In theory the update should be a single method but in practice it is easier
   * to split the update part in two: the first update is done when the
   * variation is shown, without taking into account user feedback and the
   * second update happens only when some user meets a success condition for
   * our experiment (in the common case when it clicks a link).
   *
   * This method is responsible for the first step of the update and it should
   * compute the new average reward for the variation, given 0 reward.
   *
   * @param string $variation_id
   *  The id of the variation.
   *
   * @return NULL
   */
  public function updateAverageWithNullReward($variation_id);

  /**
   * Responsible for updating the average variation reward.
   *
   * @see updateAverageWithNullReward
   *
   * This method does the second step of the update part. It is called when a
   * success condition is met and updates the average variation reward.
   *
   * @param string $variation_id
   *   The if of the variation.
   * @param float $reward
   *   A positive float number representing the reward for a achieving a goal.
   *
   * @return NULL
   */
  public function updateAverageWithReward($variation_id, $reward);

  /**
   * Returns the number of times each variation has been shown.
   *
   * @return array
   *   Associative array containing views information for all of the variations.
   */
  public function getViews();

  /**
   * Returns the mean reward for each variation.
   *
   * @return array
   *   Associative array containing average rewards of all of the variations.
   */
  public function getValues();

  /**
   * Returns the mean reward for each variation.
   *
   * The difference between this method and the getValues() method is that this
   * method returns the values that are computed each time when deciding which
   * variation to show. These values are much more important than the values
   * returned by the getValues() method which are raw values and might not be
   * used directly in variation selection decision. However, if the algorithms
   * does not use some special on the fly computed bonuses like UCB1 then the
   * two methods should return the same values.
   *
   * @return array
   *   Associative array containing average rewards of all of the variations.
   */
  public function getComputedValues();

}
