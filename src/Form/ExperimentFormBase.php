<?php

/**
 * @file
 * Contains Drupal\experiment\Form\ExperimentFormBase.
 */

namespace Drupal\experiment\Form;

use Drupal\Core\Block\BlockManagerInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\experiment\MABAlgorithmManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ExperimentFormBase.
 *
 * Typically, we need to build the same form for both adding a new entity,
 * and editing an existing entity. Instead of duplicating our form code,
 * we create a base class. Drupal never routes to this class directly,
 * but instead through the child classes of ExperimentAddForm and ExperimentEditForm.
 */
class ExperimentFormBase extends EntityForm {

  /**
   * @var \Drupal\experiment\MABAlgorithmInterface
   */
  protected $algorithm;

  /**
   * @var \Drupal\experiment\ExperimentInterface
   */
  protected $experiment;

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
   * Constructs the ExperimentFormBase.
   *
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *   An entity query factory for the experiment entity type.
   * @param \Drupal\Core\Block\BlockManagerInterface $block_manager
   *   The block manager.
   * @param \Drupal\experiment\MABAlgorithmManager $mabAlgorithmManager
   *   Multi armed bandit algorithm manager.
   */
  public function __construct(QueryFactory $query_factory, BlockManagerInterface $block_manager, MABAlgorithmManager $mabAlgorithmManager) {
    $this->entityQueryFactory = $query_factory;
    $this->blockManager = $block_manager;
    $this->mabAlgorithmManager = $mabAlgorithmManager;
    $this->experiment = $this->getEntity();
  }

  /**
   * Factory method for ExperimentFormBase.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query'),
      $container->get('plugin.manager.block'),
      $container->get('plugin.manager.mab_algorithm')
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
    // @todo Improve the UX for adding blocks and add the feature: select the
    //   same block but different view modes.
    $blocks = [];
    // @todo See exactly what's the deal with getDefinitionsForContexts.
    $definitions = $this->blockManager->getDefinitionsForContexts();
    $sorted_definitions = $this->blockManager->getSortedDefinitions($definitions);

    foreach ($sorted_definitions as $plugin_id => $plugin_definition) {
      $blocks[$plugin_id] = $plugin_definition['admin_label'];
    }

    // @todo Investigate why the block array is saved like this.
    $form['blocks'] = [
      '#type' => 'select',
      '#title' => $this->t('Blocks'),
      '#options' => $blocks,
      '#default_value' => array_keys($experiment->getBlocks()),
      '#multiple' => TRUE,
    ];

    // Get a list of all algorithm plugins.
    $algorithms = [];
    $algorithm_definitions = $this->mabAlgorithmManager->getDefinitions();

    foreach ($algorithm_definitions as $plugin_id => $plugin_definition) {
      $algorithms[$plugin_id] = $plugin_definition['label'];
    }

    // Since the form builder is called after every AJAX request, we rebuild
    // the form based on $form_state.
    $selected_algorithm = $form_state->getValue('algorithm');
    $algorithm_id = ($selected_algorithm) ? $selected_algorithm : $experiment->getAlgorithm();

    // If there isn't any algorithm selected i.e. first time when adding a new
    // experiment page is requested select the first algorithm as default.
    if (!$algorithm_id) {
      reset($algorithms);
      $algorithm_id = key($algorithms);
    }
    $experiment->setAlgorithm($algorithm_id);

    $this->algorithm = $this->mabAlgorithmManager->createInstanceFromExperiment($experiment);

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
      '#description' => t('Configure the parameters of the algorithm'),
    ];

    $form['settings_fieldset']['algorithm'] = $this->algorithm->buildConfigurationForm([], $form_state);

    // Return the form.
    return $form;
  }

  /**
   * Ajax handler for algorithm settings.
   */
  function ajaxAlgorithmSettingsCallback(array $form, FormStateInterface $form_state) {
    return $form['settings_fieldset'];
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
    // @todo Validate the algorithm plugin configuration form here.
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $experiment = $this->getEntity();

    $form_state->cleanValues();
    // The algorithm configuration is stored in the 'algorithm' key in the form,
    // pass that through form submission.
    $algorithm_config = (new FormState())->setValues($form_state->getValue(['settings_fieldset', 'algorithm']));
    $this->algorithm->submitConfigurationForm($form, $algorithm_config);

    // Update the original form values.
    $form_state->setValue(['settings_fieldset', 'algorithm'], $algorithm_config->getValues());
    $experiment->setAlgorithmConfig($this->algorithm->getConfiguration());

    // Drupal already populated the form values in the entity object. Each
    // form field was saved as a public variable in the entity class. PHP
    // allows Drupal to do this even if the method is not defined ahead of
    // time.
    $status = $experiment->save();

    // Grab the URL of the new entity. We'll use it in the message.
    $url = $experiment->urlInfo();

    // Create an edit link.
    $edit_link = $this->l(t('Edit'), $url);

    if ($status == SAVED_UPDATED) {
      // If we edited an existing entity...
      drupal_set_message($this->t('Experiment %label has been updated.', ['%label' => $experiment->label()]));
      $this->logger('contact')->notice('Experiment %label has been updated.', ['%label' => $experiment->label(), 'link' => $edit_link]);
    }
    else {
      // If we created a new entity...
      // @todo Here we need to take care of the case: a new block is added
      //   while updating the experiment.
      \Drupal::state()->set('experiment.' . $experiment->id(), array_fill_keys($experiment->getBlocks(), 0));
      drupal_set_message($this->t('Experiment %label has been added.', ['%label' => $experiment->label()]));
      $this->logger('contact')->notice('Experiment %label has been added.', ['%label' => $experiment->label(), 'link' => $edit_link]);
    }

    // Redirect the user back to the listing route after the save operation.
    $form_state->setRedirect('entity.experiment.list');
  }

}
