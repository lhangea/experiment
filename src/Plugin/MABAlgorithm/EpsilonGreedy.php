<?php

/**
 * @file
 * Contains the Epsilon greedy algorithm plugin.
 */

namespace Drupal\experiment\Plugin\MABAlgorithm;

use Drupal\Core\Form\FormStateInterface;
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
    if ($this->utility->getRand() > $this->configuration['epsilon']) {
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

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['epsilon'] = [
      '#type' => 'textfield',
      '#title' => t('Epsilon'),
      '#default_value' => (isset($this->configuration['epsilon'])) ? $this->configuration['epsilon'] : NULL,
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Validate the epsilon for values between 0 and 1 inclusive.
    if (!$this->utility->isFloatBetweenZeroAndOne($form_state->getValue('epsilon'))) {
      $form_state->setErrorByName('epsilon', $this->t('Epsilon value must be a decimal between 0 and 1'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['epsilon'] = $form_state->getValue('epsilon');
  }

}
