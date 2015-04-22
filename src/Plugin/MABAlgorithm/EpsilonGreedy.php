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
    // @todo Thr name of the experiment should be dynamic.
    $values = \Drupal::state()->get('experiment.first_experiment');
    // Exploit (use the best known variation).
    // @todo The 0.5 should also be dynamic.
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

//  /**
//   * {@inheritdoc}
//   */
//  public function defaultConfiguration() {
//    // @todo This values must be saved dynamically.
//    $default_configuration = array(
//      'epsilon' => 0.5,
//      'experiment_id' => 'first_experiment',
//    );
//    $default_configuration += parent::defaultConfiguration();
//
//    return $default_configuration;
//  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => t('Epsilon'),
      '#default_value' => $this->configuration['epsilon'],
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

  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $form = parent::submitConfigurationForm($form, $form_state);
  }

}
