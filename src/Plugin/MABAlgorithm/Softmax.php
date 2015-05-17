<?php

/**
 * @file
 */

namespace Drupal\experiment\Plugin\MABAlgorithm;

use Drupal\Core\Form\FormStateInterface;
use Drupal\experiment\MABAlgorithmBase;

/**
 * Defines the epsilon greedy multi armed bandit algorithm.
 *
 * @MABAlgorithm(
 *   id = "softmax",
 *   label = @Translation("Softmax"),
 *   description = @Translation("Description of the softmax algorithm."),
 * )
 */
class Softmax extends MABAlgorithmBase {

  /**
   * {@inheritdoc}
   */
  public function select()
  {

  }

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
  public function updateAverageWithNullReward($variation_id)
  {
    // TODO: Implement updateAverageWithNullReward() method.
  }

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
  public function updateAverageWithReward($variation_id, $reward)
  {
    // TODO: Implement updateAverageWithReward() method.
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => t('Softmax epsilon'),
      '#default_value' => $this->configuration['epsilon'],
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
    ];

    $form['weight'] = [
      '#type' => 'textfield',
      '#title' => t('I don\'t know what weight'),
      '#default_value' => $this->configuration['epsilon'],
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
    ];

    return $form;
  }

}
