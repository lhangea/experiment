<?php

/**
 * @file
 */

namespace Drupal\experiment\Plugin\MABAlgorithm;

use Drupal\experiment\MABAlgorithmBase;

/**
 * Defines the epsilon greedy multi armed bandit algorithm.
 *
 * @MABAlgorithm(
 *   id = "epsilon_greedy",
 *   label = @Translation("Epsilon greedy"),
 *   description = @Translation("Description of the epsilon greedy algorithm."),
 * )
 */
class EpsilonGreedy extends MABAlgorithmBase {

  /**
   * {@inheritdoc}
   */
  public function select()
  {
    $values = \Drupal::state()->get('experiment.first_experiment');
    // Exploit (use the best known variation).
    if ($this->getRand() > 0.5) {
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
  public function update()
  {
    // TODO: Implement update() method.
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    // @todo This values must be saved dynamically.
    $default_configuration = array(
      'epsilon' => 0.5,
      'experiment_id' => 'first_experiment',
    );
    $default_configuration += parent::defaultConfiguration();

    return $default_configuration;
  }

}