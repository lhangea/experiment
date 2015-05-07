<?php

/**
 * @file
 * Contains \Drupal\experiment\Plugin\Block\ExperimentBlock.
 */

namespace Drupal\experiment\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Start a new experiment' block.
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
    $build = [
      '#attached' => [
        'drupalSettings' => [
          'experiment_id' => $this->configuration['experiment']['id'],
        ],
        'library' => ['experiment/experiment.block'],
      ],
    ];
    $build['#attributes']['class'][] = $this->configuration['experiment']['id'];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  function blockForm($form, FormStateInterface $form_state) {
    // @todo Inject the service.
    $query = \Drupal::entityQuery('experiment');
    $options = $query
      ->execute();

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
      '#default_value' => $this->configuration['experiment']['id'],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['experiment']['id'] = $form_state->getValue(['experiment', 'block']);
  }

}
