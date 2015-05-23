<?php

/**
 * @file
 * Contains Drupal\experiment\Form\ExperimentFormBase.
 */

namespace Drupal\experiment\Form;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Drupal\experiment\MABAlgorithmManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Typically, we need to build the same form for both adding a new entity,
 * and editing an existing entity. Instead of duplicating our form code,
 * we create a base class. Drupal never routes to this class directly,
 * but instead through the child classes of ExperimentAddForm and ExperimentEditForm.
 */
class ExperimentFormBase extends EntityForm {

  /**
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQueryFactory;

  /**
   * @var \Drupal\Core\Block\BlockManagerInterface
   */
  protected $blockManager;

  /**
   * @var \Drupal\experiment\MABAlgorithmManager
   */
  protected $mabAlgorithmManager;

  /**
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Constructs the ExperimentFormBase.
   *
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *   An entity query factory for the experiment entity type.
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   The block manager.
   * @param \Drupal\experiment\MABAlgorithmManager $mabAlgorithmManager
   *   Multi armed bandit algorithm manager.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(QueryFactory $query_factory, BlockManagerInterface $block_manager, MABAlgorithmManager $mabAlgorithmManager, StateInterface $state) {
    $this->entityQueryFactory = $query_factory;
    $this->blockManager = $block_manager;
    $this->mabAlgorithmManager = $mabAlgorithmManager;
    $this->state = $state;
  }

  /**
   * Factory method for ExperimentFormBase.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query'),
      $container->get('plugin.manager.block'),
      $container->get('plugin.manager.mab_algorithm'),
      $container->get('state')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get anything we need from the base class.
    $form = parent::buildForm($form, $form_state);

    $experiment = $this->entity;
    $form['#tree'] = TRUE;

    // Build the form.
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $experiment->label(),
      '#required' => TRUE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#title' => $this->t('Machine name'),
      '#default_value' => $experiment->id(),
      '#machine_name' => [
        'exists' => [$this, 'exists'],
        'replace_pattern' => '([^a-z0-9_]+)|(^custom$)',
        'error' => 'The machine-readable name must be unique, and can only contain lowercase letters, numbers, and underscores. Additionally, it can not be the reserved word "custom".',
      ],
      '#disabled' => !$experiment->isNew(),
    ];

    // Build the list of blocks.
    $blocks = [];
    $definitions = $this->blockManager->getDefinitions();
    foreach ($definitions as $plugin_id => $plugin_definition) {
      // Don't add the placeholder block.
      if ($plugin_id != 'experiment_block') {
        $blocks[$plugin_id] = $plugin_definition['admin_label'];
      }
    }

    $form['variations_set'] = [
      '#title' => $this->t('Variations set'),
      '#type' => 'fieldset',
    ];
    $form['variations_set']['blocks'] = [
      '#type' => 'select',
      '#title' => $this->t('Block'),
      '#options' => $blocks,
      '#empty_option' => $this->t('Select a new block'),
      '#ajax' => [
        'callback' => [$this, 'ajaxViewModesCallback'],
        'wrapper' => 'view-modes',
        'effect' => 'fade',
        'progress' => ['type' => 'none'],
      ],
    ];
    // @todo This doesn't return the right results ATM but it is being worked on
    //   and should be solved soon.
    $options = $this->entityManager->getViewModeOptions('block_content');
    $selected_block = $form_state->getValue(['variations_set', 'blocks']);
    // Check if the block has view modes or not.
    // @todo Find a better way to check this.
    $has_view_modes = substr($selected_block, 0, strlen('block_content:')) === 'block_content:';
    $form['variations_set']['container'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'view-modes',
      ],
    ];
    // Display the view mode selector only if the block has view modes
    // and if it has more than 1 view mode.
    $form['variations_set']['container']['view_modes'] = [
      '#type' => 'select',
      '#title' => $this->t('View Mode'),
      '#options' => $options,
      '#description' => $this->t('Output the block in this view mode.'),
      '#access' => (count($options) > 1 && $has_view_modes),
    ];
    $form['variations_set']['add_block'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#submit' => [[$this, 'addBlockSubmitCallback']],
      '#validate' => [[$this, 'addBlockValidateCallback']],
      '#ajax' => [
        'callback' => [$this, 'ajaxAddBlockCallback'],
        'wrapper' => 'blocks-list',
        'effect' => 'fade',
        'progress' => 'none',
      ],
      '#limit_validation_errors' => [['variations_set']],
      '#attributes' => [
        'class' => ['add-block-button'] ,
      ],
      '#attached' => [
        'library' => ['experiment/experiment.admin'],
      ],
      '#value' => $this->t('Add Block'),
    ];
    $form['variations_set']['blocks_list'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'blocks-list',
      ],
    ];
    $form['variations_set']['blocks_list']['table'] = [
      '#type' => 'table',
      '#header' => [$this->t('Title'), $this->t('Machine Name'), $this->t('View Mode'), $this->t('Operations')],
      '#empty' => t('There are no items yet.'),
    ];
    if (!$form_state->get(['variations_set', 'block_list', 'storage'])) {
      $added_blocks = $experiment->getBlocks();
      $form_state->set(['variations_set', 'block_list', 'storage'], $added_blocks);
    }
    else {
      $added_blocks = $form_state->get(['variations_set', 'block_list', 'storage']);
    }
    $form['variations_set']['blocks_list']['hidden_values'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'hidden-values',
      ],
    ];
    // Add the selected blocks to the table.
    foreach ($added_blocks as $id => $added_block) {
      $variable_name = $added_block['machine_name'];
      $variable_name .= ($added_block['view_mode']) ? ':' . $added_block['view_mode'] : '';
      // @todo This is a workaround. Usually the form state should contain the
      //   proper values (input type hidden modified by javascript) on rebuild
      //   but it doesn't. File a bug for this.
      $user_input = $form_state->getUserInput();
      if ($user_input[$variable_name] != '') {
        $form_state->setValue(['variations_set', 'blocks_list', 'hidden_values', $variable_name], $user_input[$variable_name]);
      }
      $row = [];
      $row[]['#markup'] = $blocks[$added_block['machine_name']];
      $row[]['#markup'] = $added_block['machine_name'];
      $row[]['#markup'] = $added_block['view_mode'];

      $selected_links = $form_state->getValue(['variations_set', 'blocks_list', 'hidden_values', $variable_name]);
      // @todo Find a better way to store the values in the config entity.
      //   Separate the success condition configuration from selected blocks.
      if ($selected_links === NULL) {
        // If the values are not in the form state, try loading them
        // from the experiment storage.
        foreach ($added_blocks as $block) {
          if ($block['machine_name'] == $added_block['machine_name'] && $block['view_mode'] == $added_block['view_mode']) {
            $selected_links = $block['selected_links'];
          }
        }
        // If the experiment doesn't have these settings neither, it means that
        // we are creating a new experiment, so set an initial value so that not
        // link is selected.
        if ($selected_links === NULL) {
          $selected_links = [-1];
        }
      }
      $row[] = [
        '#type' => 'operations',
        '#links' => [
          'add_success_condition' => [
            'title' => $this->t('Configure'),
            'url' => Url::fromRoute('experiment.block.admin_configure', [
              'plugin_id' => $added_block['machine_name'],
              'view_mode' => ($added_block['view_mode']) ? $added_block['view_mode'] : 'none',
              'selected_links' => Json::encode($selected_links),
            ]),
            'attributes' => [
              'class' => ['use-ajax'],
              'data-dialog-type' => 'modal',
              'data-dialog-options' => Json::encode([
                'width' => 700,
              ]),
            ],
          ],
          'remove' => [
            'title' => $this->t('Remove'),
            'url' => Url::fromRoute('<front>'),
          ],
        ],
      ];
      $form['variations_set']['blocks_list']['table'][$id] = $row;
      $form['variations_set']['blocks_list']['hidden_values'][$variable_name] = [
        '#type' => 'hidden',
        '#default_value' => $selected_links,
        '#name' => $variable_name,
      ];
    }

    $form['variations_set']['unused'] = [
      '#type' => 'textfield',
      '#ajax' => [
        'callback' => [$this, 'ajaxAddBlockCallback'],
        'event' => 'change',
        'wrapper' => 'blocks-list',
      ],
    ];

    // Get a list of all algorithm plugins.
    $algorithms = [];
    $algorithm_definitions = $this->mabAlgorithmManager->getDefinitions();

    foreach ($algorithm_definitions as $plugin_id => $plugin_definition) {
      $algorithms[$plugin_id] = $plugin_definition['label'];
    }

    // Since the form builder is called after every AJAX request, we rebuild
    // the form based on $form_state.
    $selected_algorithm = $form_state->getValue(['algorithm_fieldset', 'algorithm']);
    $algorithm_id = ($selected_algorithm) ? $selected_algorithm : $experiment->getAlgorithmId();

    // If there isn't any algorithm selected i.e. first time when adding a new
    // experiment page is requested select the first algorithm as default.
    if (!$algorithm_id) {
      reset($algorithms);
      $algorithm_id = key($algorithms);
    }
    $experiment->setAlgorithmId($algorithm_id);

    $algorithm = $this->mabAlgorithmManager->createInstanceFromExperiment($experiment);

    $form['algorithm_fieldset'] = [
      '#title' => $this->t('Algorithm'),
      '#type' => 'fieldset',
    ];
    $form['algorithm_fieldset']['algorithm'] = [
      '#title' => $this->t('Select Algorithm'),
      '#type' => 'select',
      '#options' => $algorithms,
      '#default_value' => $algorithm_id,
      '#ajax' => [
        'callback' => [$this, 'ajaxAlgorithmSettingsCallback'],
        'wrapper' => 'algorithm-settings',
        'effect' => 'fade',
        'progress' => 'none',
      ],
    ];

    $form['algorithm_fieldset']['settings'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'algorithm-settings',
      ],
    ];
    $plugin_definition = $algorithm->getPluginDefinition();
    $form['algorithm_fieldset']['settings']['description'] = [
      '#markup' => $plugin_definition['description'],
    ];
    // This part is may be different on every form rebuilt based on the
    // selected algorithm.
    $form['algorithm_fieldset']['settings']['form'] = $algorithm->buildConfigurationForm([], $form_state);

    return $form;
  }

  /**
   * Ajax handler for algorithm settings.
   */
  function ajaxAlgorithmSettingsCallback(array $form, FormStateInterface $form_state) {
    return $form['algorithm_fieldset']['settings'];
  }

  /**
   * Returns the view modes associated with the selected block.
   */
  function ajaxViewModesCallback(array $form, FormStateInterface $form_state) {
    return $form['variations_set']['container'];
  }

  /**
   * Validation handler for the 'Add block' button.
   */
  function addBlockValidateCallback(array $form, FormStateInterface $form_state) {
    $selected_block = [
      'machine_name' => $form_state->getValue(['variations_set', 'blocks']),
      'view_mode' => $form_state->getValue(['variations_set', 'container', 'view_modes']),
    ];
    if (empty($selected_block['machine_name'])) {
      $form_state->setErrorByName('variations_set][blocks', $this->t('Please select a block'));
    }
    $blocks_list = $form_state->get(['variations_set', 'block_list', 'storage']);
    if ($blocks_list === NULL) {
      $blocks_list = [];
    }
    // The same block can only be added once.
    foreach ($blocks_list as $block) {
      if ($selected_block['machine_name'] === $block['machine_name'] && $selected_block['view_mode'] === $block['view_mode']) {
        $form_state->setErrorByName('variations_set', $this->t('You cannot add the same variation twice'));
      }
    }
  }

  /**
   * Submit handler for the 'Add block' button.
   */
  function addBlockSubmitCallback(array $form, FormStateInterface $form_state) {
    $block = [
      'machine_name' => $form_state->getValue(['variations_set', 'blocks']),
      'view_mode' => $form_state->getValue(['variations_set', 'container', 'view_modes']),
    ];
    $blocks_list = $form_state->get(['variations_set', 'block_list', 'storage']);
    if ($blocks_list === NULL) {
      $blocks_list = [];
    }
    $blocks_list[] = $block;
    // Add the block to the set of selected blocks.
    $form_state->set(['variations_set', 'block_list', 'storage'], $blocks_list);
    $form_state->setRebuild();
  }

  /**
   * Ajax handler for blocks list.
   */
  function ajaxAddBlockCallback(array $form, FormStateInterface $form_state) {
    return $form['variations_set']['blocks_list'];
  }

  /**
   * Checks for an existing experiment.
   *
   * @param string|int $entity_id
   *   The entity ID.
   * @param array $element
   *   The form element.
   * @param FormStateInterface $form_state
   *   The form state.
   *
   * @return bool
   *   TRUE if this format already exists, FALSE otherwise.
   */
  public function exists($entity_id, array $element, FormStateInterface $form_state) {
    // Use the query factory to build a new experiment entity query.
    $query = $this->entityQueryFactory->get('experiment');

    // Query the entity ID to see if its in use.
    $result = $query->condition('id', $element['#field_prefix'] . $entity_id)
      ->execute();

    // We don't need to return the ID, only if it exists or not.
    return (bool) $result;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    // Get the basic actions from the base class.
    $actions = parent::actions($form, $form_state);

    // Change the submit button text.
    $actions['submit']['#value'] = $this->t('Save');

    // Return the result.
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array $form, FormStateInterface $form_state) {
    parent::validate($form, $form_state);

    // Add code here to validate your config entity's form elements.
    $form_state->cleanValues();
    // The algorithm configuration is stored in the 'algorithm' key in the form,
    // pass that through form submission.
    $algorithm_config = (new FormState())->setValues($form_state->getValue(['algorithm_fieldset', 'settings', 'form']));
    $algorithm = $this->mabAlgorithmManager->createInstanceFromExperiment($this->getEntity());
    $algorithm->validateConfigurationForm($form, $algorithm_config);

    // Update the original form values.
    // @todo I don't know why do we have to do this!!
    $form_state->setValue(['algorithm_fieldset', 'settings', 'form'], $algorithm_config->getValues());
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $experiment = $this->getEntity();
    $algorithm = $this->mabAlgorithmManager->createInstanceFromExperiment($experiment);

    $form_state->cleanValues();
    // The algorithm configuration is stored in the 'algorithm' key in the form,
    // pass that through form submission.
    $algorithm_config = (new FormState())->setValues($form_state->getValue(['algorithm_fieldset', 'settings', 'form']));
    $algorithm->submitConfigurationForm($form, $algorithm_config);

    // Update the original form values.
    $form_state->setValue(['algorithm_fieldset', 'settings', 'form'], $algorithm_config->getValues());
    $experiment->setAlgorithmConfig($algorithm->getConfiguration());

    $added_blocks = $form_state->get(['variations_set', 'block_list', 'storage']);
    foreach ($added_blocks as $key => $block) {
      $element_name = $block['machine_name'];
      $element_name .= ($block['view_mode']) ? ':' . $block['view_mode'] : '';
      $added_blocks[$key]['selected_links'] = $form_state->getValue(['variations_set', 'blocks_list', 'hidden_values', $element_name]);
    }
    $experiment->setBlocks($added_blocks);
    // Drupal already populated the form values in the entity object. Each
    // form field was saved as a public variable in the entity class. PHP
    // allows Drupal to do this even if the method is not defined ahead of
    // time.
    $status = $experiment->save();

    // Grab the URL of the new entity. We'll use it in the message.
    $url = $experiment->urlInfo();

    // Create an edit link.
    $edit_link = $this->l($this->t('Edit'), $url);
    // @todo study the possibility of not resetting the experiment if there
    //   are no changes to the blocks list and to the algorithm configuration
    //   If it remains like this redirect to a confirmation page when updating.
    $this->state->set('experiment.' . $experiment->id(), [
        'counts' => array_fill_keys($experiment->createUniqueKeysForBlocks(), 0),
        'values' => array_fill_keys($experiment->createUniqueKeysForBlocks(), 0),
      ]
    );

    if ($status == SAVED_UPDATED) {
      drupal_set_message($this->t('Experiment %label has been updated.', ['%label' => $experiment->label()]));
      $this->logger('contact')->notice('Experiment %label has been updated.', ['%label' => $experiment->label(), 'link' => $edit_link]);
    }
    // New entity.
    else {
      drupal_set_message($this->t('Experiment %label has been added.', ['%label' => $experiment->label()]));
      $this->logger('contact')->notice('Experiment %label has been added.', ['%label' => $experiment->label(), 'link' => $edit_link]);
    }

    // Redirect the user back to the listing route after the save operation.
    $form_state->setRedirect('entity.experiment.list');
  }

}
