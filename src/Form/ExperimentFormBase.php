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
 *
 * @package Drupal\experiment\Form
 *
 * @ingroup experiment
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
   * Construct the ExperimentFormBase.
   *
   * For simple entity forms, there's no need for a constructor. Our experiment form
   * base, however, requires an entity query factory to be injected into it
   * from the container. We later use this query factory to build an entity
   * query for the exists() method.
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
  }

  /**
   * Factory method for ExperimentFormBase.
   *
   * When Drupal builds this class it does not call the constructor directly.
   * Instead, it relies on this method to build the new object. Why? The class
   * constructor may take multiple arguments that are unknown to Drupal. The
   * create() method always takes one parameter -- the container. The purpose
   * of the create() method is twofold: It provides a standard way for Drupal
   * to construct the object, meanwhile it provides you a place to get needed
   * constructor parameters from the container.
   *
   * In this case, we ask the container for an entity query factory. We then
   * pass the factory to our class as a constructor parameter.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.query'),
      $container->get('plugin.manager.block'),
      $container->get('plugin.manager.mab_algorithm')
    );
  }

  /**
   * Overrides \Drupal\Core\Entity\EntityForm::buildForm().
   *
   * Builds the entity add/edit form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   An associative array containing the current state of the form.
   *
   * @return array
   *   An associative array containing the experiment add/edit form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get anything we need from the base class.
    $form = parent::buildForm($form, $form_state);

    // Drupal provides the entity to us as a class variable. If this is an
    // existing entity, it will be populated with existing values as class
    // variables. If this is a new entity, it will be a new object with the
    // class of our entity. Drupal knows which class to call from the
    // annotation on our Experiment class.
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
    // @todo Improve the UX for adding blocks and selecting the view modes.
    $blocks = array();
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
    $algorithms = array();
    $algorithm_definitions = $this->mabAlgorithmManager->getDefinitions();

    foreach ($algorithm_definitions as $plugin_id => $plugin_definition) {
      $algorithms[$plugin_id] = $plugin_definition['label'];
    }

    // Since the form builder is called after every AJAX request, we rebuild
    // the form based on $form_state.
    $selected_algorithm = $form_state->getValue('algorithm');
    $algorithm = ($selected_algorithm) ? $selected_algorithm : $experiment->getAlgorithm();

    $form['algorithm'] = [
      '#title' => $this->t('Algorithm'),
      '#type' => 'select',
      '#options' => $algorithms,
      '#default_value' => $algorithm,
      '#ajax' => [
        'callback' => [$this, 'ajaxAlgorithmSettingsCallback'],
        'wrapper' => 'algorithm-settings-div',
        'effect' => 'fade',
        'progress' => ['type' => 'none'],
      ],
    ];

    $plugin = \Drupal::getContainer()->get('plugin.manager.mab_algorithm')->createInstance($algorithm);

    $form['settings_fieldset'] = [
      '#title' => $this->t('Algorithm settings'),
      '#prefix' => '<div id="algorithm-settings-div">',
      '#suffix' => '</div>',
      '#type' => 'fieldset',
      '#description' => t('Configure the parameters of the algorithm'),
    ];

    $form['settings_fieldset']['algorithm'] = $plugin->buildConfigurationForm();

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
   * Overrides Drupal\Core\Entity\EntityFormController::actions().
   *
   * To set the submit button text, we need to override actions().
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   An associative array containing the current state of the form.
   *
   * @return array
   *   An array of supported actions for the current entity form.
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    // Get the basic actins from the base class.
    $actions = parent::actions($form, $form_state);

    // Change the submit button text.
    $actions['submit']['#value'] = $this->t('Save');

    // Return the result.
    return $actions;
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::validate().
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   An associative array containing the current state of the form.
   */
  public function validate(array $form, FormStateInterface $form_state) {
    parent::validate($form, $form_state);

    // Add code here to validate your config entity's form elements.
    // Nothing to do here.
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::save().
   *
   * Saves the entity. This is called after submit() has built the entity from
   * the form values. Do not override submit() as save() is the preferred
   * method for entity form controllers.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   An associative array containing the current state of the form.
   */
  public function save(array $form, FormStateInterface $form_state) {
    // EntityForm provides us with the entity we're working on.
    $experiment = $this->getEntity();

    $form_state->cleanValues();
    // The algorithm configuration is stored in the 'algorithm' key in the form,
    // pass that through for submission.
    $algorithm_config = (new FormState())->setValues($form_state->getValue(['settings_fieldset', 'algorithm']));
    // @todo See if it's better to have a class instance variable holding the algorithm.
    $algorithm = \Drupal::getContainer()->get('plugin.manager.mab_algorithm')->createInstance($experiment->getAlgorithm());
    $algorithm->submitConfigurationForm($form, $algorithm_config);
    // Update the original form values.
    $form_state->setValue(['settings_fieldset', 'algorithm'], $algorithm_config->getValues());

    $experiment->setAlgorithmConfig($algorithm->getConfiguration());
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
      drupal_set_message($this->t('Experiment %label has been updated.', array('%label' => $experiment->label())));
      $this->logger('contact')->notice('Experiment %label has been updated.', ['%label' => $experiment->label(), 'link' => $edit_link]);
    }
    else {
      // If we created a new entity...
      // @todo Here we need to take care of the case: a new block is added
      //   while updating the experiment.
      \Drupal::state()->set('experiment.' . $experiment->id(), array_fill_keys($experiment->getBlocks(), 0));
      drupal_set_message($this->t('Experiment %label has been added.', array('%label' => $experiment->label())));
      $this->logger('contact')->notice('Experiment %label has been added.', ['%label' => $experiment->label(), 'link' => $edit_link]);
    }

    // Redirect the user back to the listing route after the save operation.
    $form_state->setRedirect('entity.experiment.list');
  }

}
