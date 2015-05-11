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
      '#prefix' => '<div id="variations-set">',
      '#suffix' => '</div>',
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
    $has_view_modes = substr($selected_block, 0, strlen('block_content:')) === 'block_content:';
    $form['variations_set']['container'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'view-modes',
      ],
    ];
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
        'callback' => [$this, 'addBlockAjaxCallback'],
        'wrapper' => 'blocks-list',
        'effect' => 'fade',
      ],
      '#limit_validation_errors' => ['variations_set'],
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
      '#header' => [$this->t('Title'), $this->t('Machine Name'), $this->t('View Mode')],
      '#empty' => t('There are no items yet.'),
    ];
    if (!$form_state->get(['variations_set', 'block_list', 'storage'])) {
      $added_blocks = $experiment->getBlocks();
      $form_state->set(['variations_set', 'block_list', 'storage'], $experiment->getBlocks());
    }
    else {
      $added_blocks = $form_state->get(['variations_set', 'block_list', 'storage']);
    }
    // Add the selected blocks to the table.
    foreach ($added_blocks as $id => $added_block) {
      $form['variations_set']['blocks_list']['table'][$id][] = [
        '#markup' => $blocks[$added_block['machine_name']],
      ];
      $form['variations_set']['blocks_list']['table'][$id][] = [
        '#markup' => $added_block['machine_name'],
      ];
      $form['variations_set']['blocks_list']['table'][$id][] = [
        '#markup' => $added_block['view_mode'],
      ];
    }

//
//    $form['variations_set']['add_block'] = [
//      '#type' => 'link',
//      '#title' => $this->t('Add a new block'),
//      '#url' => Url::fromRoute('block.admin_add', [
//        'plugin_id' => 'experiment_block',
//        'theme' => 'bartik'
//      ]),
//      '#attributes' => [
//        'class' => array('use-ajax', 'block-filter-text-source'),
//        'data-accepts' => 'application/vnd.drupal-modal',
//        'data-dialog-options' => Json::encode(array(
//          'width' => 700,
//        )),
//      ],
//    ];

//    $form['select_block']['#links'] = array(
//      'title' => $this->t('Add a new block'),
//      'url' => Url::fromRoute('block.admin_add', [
//        'plugin_id' => 'experiment',
//        'theme' => $this->theme
//      ]),
//      'attributes' => array(
//        'class' => array('use-ajax', 'block-filter-text-source'),
//        'data-accepts' => 'application/vnd.drupal-modal',
//        'data-dialog-options' => Json::encode(array(
//          'width' => 700,
//        )),
//      ),
//    );

    // Get a list of all algorithm plugins.
    $algorithms = [];
    $algorithm_definitions = $this->mabAlgorithmManager->getDefinitions();

    foreach ($algorithm_definitions as $plugin_id => $plugin_definition) {
      $algorithms[$plugin_id] = $plugin_definition['label'];
    }

    // Since the form builder is called after every AJAX request, we rebuild
    // the form based on $form_state.
    $selected_algorithm = $form_state->getValue('algorithm');
    $algorithm_id = ($selected_algorithm) ? $selected_algorithm : $experiment->getAlgorithmId();

    // If there isn't any algorithm selected i.e. first time when adding a new
    // experiment page is requested select the first algorithm as default.
    if ($experiment->isNew()) {
      reset($algorithms);
      $algorithm_id = key($algorithms);
    }
    $experiment->setAlgorithmId($algorithm_id);

    $algorithm = $this->mabAlgorithmManager->createInstanceFromExperiment($experiment);

    $form['algorithm'] = [
      '#title' => $this->t('Algorithm'),
      '#type' => 'select',
      '#options' => $algorithms,
      '#default_value' => $algorithm_id,
      '#ajax' => [
        'callback' => [$this, 'ajaxAlgorithmSettingsCallback'],
        'wrapper' => 'algorithm-settings-div',
        'effect' => 'fade',
        'progress' => ['type' => 'none'],
      ],
    ];

    $form['settings_fieldset'] = [
      '#title' => $this->t('Algorithm settings'),
      '#prefix' => '<div id="algorithm-settings-div">',
      '#suffix' => '</div>',
      '#type' => 'fieldset',
    ];

    $plugin_definition = $algorithm->getPluginDefinition();
    $form['settings_fieldset']['description'] = [
      '#markup' => $plugin_definition['description'],
    ];
    $form['settings_fieldset']['algorithm'] = $algorithm->buildConfigurationForm([], $form_state);

    return $form;
  }

  /**
   * Ajax handler for algorithm settings.
   */
  function ajaxAlgorithmSettingsCallback(array $form, FormStateInterface $form_state) {
    return $form['settings_fieldset'];
  }

  /**
   * Ajax handler for view modes select box.
   */
  function ajaxViewModesCallback(array $form, FormStateInterface $form_state) {
    return $form['variations_set']['container'];
  }

  /**
   * Form submission handler for add block button validation.
   */
  function addBlockValidateCallback(array $form, FormStateInterface $form_state) {
    $selected_block = [
      'machine_name' => $form_state->getValue(['variations_set', 'blocks']),
      'view_mode' => $form_state->getValue(['variations_set', 'container', 'view_modes']),
    ];
    $blocks_list = $form_state->get(['variations_set', 'block_list', 'storage']) ? $form_state->get(['variations_set', 'block_list', 'storage']) : [];
    foreach ($blocks_list as $block) {
      if ($selected_block['machine_name'] == $block['machine_name'] && $selected_block['view_mode'] == $block['view_mode']) {
        // @todo This doesn't work. Need to figure it out.
        $form_state->setErrorByName('variations_set', $this->t('You cannot add the same variation twice'));
      }
    }
  }

  /**
   * Form submission handler for add block button.
   */
  function addBlockSubmitCallback(array $form, FormStateInterface $form_state) {
    $block = [
      'machine_name' => $form_state->getValue(['variations_set', 'blocks']),
      'view_mode' => $form_state->getValue(['variations_set', 'container', 'view_modes']),
    ];
    $blocks_list = $form_state->get(['variations_set', 'block_list', 'storage']) ? $form_state->get(['variations_set', 'block_list', 'storage']) : [];
    $blocks_list[] = $block;
    $form_state->set(['variations_set', 'block_list', 'storage'], $blocks_list);
    $form_state->setRebuild();
  }

  /**
   * Ajax handler for blocks list.
   */
  function addBlockAjaxCallback(array $form, FormStateInterface $form_state) {
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
    $algorithm_config = (new FormState())->setValues($form_state->getValue(['settings_fieldset', 'algorithm']));
    $algorithm = $this->mabAlgorithmManager->createInstanceFromExperiment($this->getEntity());
    $algorithm->validateConfigurationForm($form, $algorithm_config);

    // Update the original form values.
    // @todo i don't know why do we have to do this!!
    $form_state->setValue(['settings_fieldset', 'algorithm'], $algorithm_config->getValues());
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
    $algorithm_config = (new FormState())->setValues($form_state->getValue(['settings_fieldset', 'algorithm']));
    $algorithm->submitConfigurationForm($form, $algorithm_config);

    // Update the original form values.
    $form_state->setValue(['settings_fieldset', 'algorithm'], $algorithm_config->getValues());
    $experiment->setAlgorithmConfig($algorithm->getConfiguration());

    $experiment->setBlocks($form_state->get(['variations_set', 'block_list', 'storage']));

    // Drupal already populated the form values in the entity object. Each
    // form field was saved as a public variable in the entity class. PHP
    // allows Drupal to do this even if the method is not defined ahead of
    // time.
    $status = $experiment->save();

    // Grab the URL of the new entity. We'll use it in the message.
    $url = $experiment->urlInfo();

    // Create an edit link.
    $edit_link = $this->l(t('Edit'), $url);
    // @todo study the possibility of not resetting the experiment if there
    //   are no changes to the blocks list and to the algorithm configuration
    //   If it remains like this redirect to a confirmation page when updating.
    $this->state->set('experiment.' . $experiment->id(), [
        'counts' => array_fill_keys($experiment->getBlocks(), 0),
        'values' => array_fill_keys($experiment->getBlocks(), 0),
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
