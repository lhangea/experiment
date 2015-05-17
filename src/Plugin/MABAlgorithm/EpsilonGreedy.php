<?php

/**
 * @file
 * Contains the Epsilon greedy algorithm.
 */

namespace Drupal\experiment\Plugin\MABAlgorithm;

use Drupal\experiment\MABAlgorithmBase;

/**
 * Defines the epsilon greedy multi armed bandit algorithm.
 *
 * @MABAlgorithm(
 *   id = "epsilon_greedy",
 *   label = @Translation("Epsilon greedy"),
 *   description = @Translation("Epsilon greedy exploration is a strategy that randomly picks a variation with probability epsilon and will display the best known variation otherwise."),
 * )
 */
class EpsilonGreedy extends MABAlgorithmBase {

  /**
   * {@inheritdoc}
   */
  public function select() {
    // Exploit (use the best known variation).
    if ($this->getRand() > $this->configuration['epsilon']) {
      return $this->getIndMax($this->values);
    }
    // Explore (select a random variation).
    else {
      return array_rand($this->values);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function updateAverageWithNullReward($variation_id)
  {
    $this->counts[$variation_id] += 1;
    $n = $this->counts[$variation_id];
    $value = $this->values[$variation_id];
    $new_value = (($n - 1) / (float)$n) * $value;
    $this->values[$variation_id] = $new_value;

    $this->saveResults();
  }

  /**
   * {@inheritdoc}
   */
  public function updateAverageWithReward($variation_id, $reward)
  {
    $n = $this->counts[$variation_id];
    $this->values[$variation_id] += (1 / (float)$n) * (float)$reward;
    $this->saveResults();
  }
}
