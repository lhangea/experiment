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
    $results = $this->state->get('experiment.' . $this->configuration['experiment_id']);
    $values = $results['values'];

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
  public function update($variation_id, $reward) {
    // Get the current values.
    $results = $this->state->get('experiment.' . $this->configuration['experiment_id']);

    $results['counts'][$variation_id] += 1;
    $n = $results['counts'][$variation_id];

    $value = $results['values'][$variation_id];
    $new_value = (($n - 1) / (float)$n) * $value + (1 / (float)$n) * $reward;
    $results['values'][$variation_id] = $new_value;

    $this->state->get('experiment.' . $this->configuration['experiment_id'], $results);
  }

}
