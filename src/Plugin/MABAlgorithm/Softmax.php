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
   * {@inheritdoc}
   */
  public function update()
  {
    // TODO: Implement update() method.
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
