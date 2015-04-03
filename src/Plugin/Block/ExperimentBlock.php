<?php

/**
 * @file
 * Contains \Drupal\experiment\Plugin\Block\ExperimentBlock.
 */

namespace Drupal\experiment\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides an 'Start a new experiment' block.
 *
 * @Block(
 *   id = "experiment_block",
 *   admin_label = @Translation("Experiment"),
 * )
 */
class ExperimentBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return array(
      '#markup' => $this->t('This is a place holder for the blocks associated with this experiment.'),
    );
  }

  /**
   * {@inheritdoc}
   */
  function blockForm($form, FormStateInterface $form_state) {
    $options = array(
      $this->t('Nice Block'),
      $this->t('Even nicer Block'),
    );

    $form['experiment'] = array(
      '#type' => 'details',
      '#title' => $this->t('Experiment settings'),
      '#open' => TRUE,
    );

    $form['experiment']['name'] = array(
      '#type' => 'textfield',
      '#title' => t('Name'),
      '#size' => 60,
      '#maxlength' => 128,
      '#required' => TRUE,
    );

    $form['experiment']['blocks'] = array(
      '#type' => 'select',
      '#title' => t('Selected'),
      '#options' => $options,
      '#multiple' => TRUE,
      '#description' => t('Select blocks that are part of this experiment.'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['block_mode'] = $form_state->getValue('book_block_mode');
  }
}