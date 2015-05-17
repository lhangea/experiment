<?php

/**
 * @file
 * Contains the Epsilon greedy algorithm plugin.
 */

namespace Drupal\experiment\Plugin\MABAlgorithm;

use Drupal\experiment\MABAlgorithmBase;

/**
 * Defines the epsilon greedy multi armed bandit algorithm.
 *
 * @MABAlgorithm(
 *   id = "epsilon_greedy",
 *   label = @Translation("Epsilon greedy"),
 *   description = @Translation("Epsilon greedy exploration is a strategy that randomly picks a variation with probability epsilon and displays the best known variation otherwise (probability 1 - epsilon). This algorithm can also behave like traditional A/B testing if epsilon is set to 1. In that case it will randomly display variations and collect results."),
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
