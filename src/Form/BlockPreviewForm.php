<?php

/**
 * @file
 * Contains Drupal\experiment\Form\BlockPreviewForm.
 */

namespace Drupal\experiment\Form;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseDialogCommand;
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
  public function buildForm(array $form, FormStateInterface $form_state, $plugin_id = NULL, $view_mode = NULL, $selected_links = NULL) {
    $configuration = [];
    if ($view_mode !== 'none') {
      $configuration['view_mode'] = $view_mode;
    }
    $block = $this->blockManager->createInstance($plugin_id, $configuration);

    $form['block'] = $block->build();
    // Remove type #actions element in order to avoid having the button descend
    // in the footer of the modal window.
    $form['block']['actions'] = $form['block']['actions']['submit'];
    $form['#attached']['library'] = ['experiment/experiment.select'];
    $hidden_input_name = ($view_mode !== 'none') ? $plugin_id . ':' . $view_mode : $plugin_id;
    $form['#attached']['drupalSettings']['selectedLinks'] = JSON::decode($selected_links);
    $form['#attached']['drupalSettings']['hiddenInputName'] = $hidden_input_name;
    // We need to have this values in the ajax callback so that we can select
    // the element associated with this block.
    $form['plugin_id'] = ['#type' => 'hidden', '#value' => $plugin_id];
    $form['view_mode'] = ['#type' => 'hidden', '#value' => $view_mode];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Save'),
      '#attributes' => [
        'class' => ['allowed-submit'],
      ],
      '#ajax' => [
        'callback' => [$this, 'dialogSaveAndClose'],
      ],
    ];

    return $form;
  }

  /**
   * Returns an AjaxResponse with command to save and close the dialog.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The JSON response object.
   */
  public function dialogSaveAndClose(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new CloseDialogCommand('#drupal-modal'));

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // We need to have this method to implement the interface but in this case
    // we will not use it since we will "submit" the form through ajax.
    // Note that this method is executed before the ajax call.
  }

}
