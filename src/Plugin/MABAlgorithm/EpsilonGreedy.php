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
    // @todo See if the state is the best option for storing the experiment result.
    $values = $this->state->get('experiment.' . $this->configuration['experiment_id']);

    // Exploit (use the best known variation).
    if ($this->getRand() > $this->configuration['epsilon']) {
      return $this->getIndMax($values);
    }
    // Explore (select a random variation).
    else {
      return array_rand($values);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function update() {

  }

}
