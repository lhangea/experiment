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
    // @todo This needs to be replaced with actual experiment config entities.
    // @todo Remove from the list the already started experiments.
    $options = array(
      $this->t('Experiment 1'),
      $this->t('Experiment 2'),
    );

    $form['experiment'] = array(
      '#type' => 'details',
      '#title' => $this->t('Experiment settings'),
      '#open' => TRUE,
    );

    $form['experiment']['block'] = array(
      '#type' => 'select',
      '#title' => t('Selected'),
      '#options' => $options,
      '#description' => t('Select experiment to associate with this block.'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['experiment']['uuid'] = $form_state->getValue(['experiment', 'block']);
  }
}