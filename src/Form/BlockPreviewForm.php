<?php

/**
 * @file
 * Contains Drupal\experiment\Form\BlockPreviewForm.
 */

namespace Drupal\experiment\Form;

use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BlockPreviewForm extends FormBase {

  /**
   * The block manager.
   *
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * Constructs an BlockPreviewForm object.
   *
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   The block manager.
   */
  public function __construct(BlockManagerInterface $block_manager) {
    $this->blockManager = $block_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('plugin.manager.block')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'experiment_block_preview_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $plugin_id = NULL, $view_mode = 'default') {
    $block = $this->blockManager->createInstance($plugin_id, ['view_mode' => $view_mode]);

    $form['block'] = $block->build();
    // Remove type #actions element in order to avoid having the button descend
    // in the footer of the modal window.
    $form['block']['actions'] = $form['block']['actions']['submit'];
    $form['#attached']['library'] = ['experiment/experiment.select'];
    $form['#attached']['drupalSettings']['selectedLinks'] = [0, 1];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Save'),
      '#attributes' => [
        'class' => ['allowed-submit'],
      ]
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    drupal_set_message($this->t('Success conditions configured successfully.'));
    $form_state->setRedirect('entity.experiment.list');
  }
}
