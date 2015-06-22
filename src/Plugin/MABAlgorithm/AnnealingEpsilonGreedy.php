<?php

/**
 * @file
 * Contains the Annealing epsilon greedy algorithm plugin.
 */

namespace Drupal\experiment\Plugin\MABAlgorithm;

use Drupal\experiment\MABAlgorithmBase;

/**
 * Defines the annealing epsilon greedy multi armed bandit algorithm.
 *
 * @MABAlgorithm(
 *   id = "annealing_epsilon_greedy",
 *   label = @Translation("Annealing Epsilon greedy"),
 *   description = @Translation("This algorithm behaves exactly like Epsilon Greedy algorithm but this one lowers the value of Epsilon at each step leading to less and less exploration as the time passes and besides, Epsilon parameter doesn't need to be set by the user, it is up to algorithm to initialise and update it at each step."),
 * )
 */
class AnnealingEpsilonGreedy extends MABAlgorithmBase {

  /**
   * {@inheritdoc}
   */
  public function select() {
    $t = array_sum($this->counts) + 1;
    $epsilon =  1 / log($t + 0.0000001);

    // Exploit (use the best known variation).
    if ($this->utility->getRand() > $epsilon) {
      return $this->utility->getIndMax($this->values);
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
