<?php

/**
 * @file
 * Contains the UCB1 algorithm plugin.
 */

namespace Drupal\experiment\Plugin\MABAlgorithm;

use Drupal\experiment\MABAlgorithmBase;

/**
 * @MABAlgorithm(
 *   id = "ucb1",
 *   label = @Translation("Upper Confidence Bound 1"),
 *   description = @Translation("Upper Confidence Bound algorithm makes decisions to explore that are driven by our confidence in the estimated value of the arms we’ve selected."),
 * )
 */
class UCB1 extends MABAlgorithmBase {

  /**
   * {@inheritdoc}
   */
  public function select()
  {
    foreach($this->counts as $plugin_id => $count) {
      // Display each variation at least once.
      if ($count == 0) {
        return $plugin_id;
      }
    }

    $ucb_values = [];
    $total_counts = array_reduce($this->counts, function ($a, $b) { return $a + $b; }, 0);
    foreach ($this->values as $plugin_id => $value) {
      $bonus = sqrt(2 * log($total_counts)) / (float)$this->counts[$plugin_id];
      $ucb_values[$plugin_id] = $value + $bonus;
    }

    return $this->getIndMax($ucb_values);
  }

  /**
   * {@inheritdoc}
   */
  public function updateAverageWithNullReward($variation_id)
  {
    parent::updateAverageWithNullReward($variation_id);
  }

  /**
   * {@inheritdoc}
   */
  public function updateAverageWithReward($variation_id, $reward)
  {
    parent::updateAverageWithReward($variation_id, $reward);
  }

}
