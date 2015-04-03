<?php

/**
 * @file
 * Contains Drupal\experiment\Entity\Experiment.
 */

namespace Drupal\experiment\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the experiment config entity.
 *
 *    - list_builder: The class that provides listings of the entity.
 *    - form: An array of entity form classes keyed by their operation.
 *  - entity_keys: Specifies the class properties in which unique keys are
 *    stored for this entity type. Unique keys are properties which you know
 *    will be unique, and which the entity manager can use as unique in database
 *    queries.
 *  - links: entity URL definitions. These are mostly used for Field UI.
 *    Arbitrary keys can set here. For example, User sets cancel-form, while
 *    Node uses delete-form.
 *
 * @ConfigEntityType(
 *   id = "experiment",
 *   label = @Translation("Experiment"),
 *   admin_permission = "access content",
 *   handlers = {
 *     "access" = "Drupal\experiments\ExperimentAccessController",
 *     "list_builder" = "Drupal\experiment\Controller\ExperimentListBuilder",
 *     "form" = {
 *       "add" = "Drupal\experiment\Form\ExperimentAddForm",
 *       "edit" = "Drupal\experiment\Form\ExperimentEditForm",
 *       "delete" = "Drupal\experiment\Form\ExperimentDeleteForm"
 *     }
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "edit-form" = "/experiment/config_entity_example/manage/{experiment}",
 *     "delete-form" = "/experiment/config_entity_example/manage/{experiment}/delete"
 *   }
 * )
 */
class Experiment extends ConfigEntityBase {

  /**
   * The experiment ID.
   *
   * @var string
   */
  public $id;

  /**
   * The experiment UUID.
   *
   * @var string
   */
  public $uuid;

  /**
   * The experiment label.
   *
   * @var string
   */
  public $label;

  /**
   * The experiment floopy flag.
   *
   * @var string
   */
  public $floopy;
}
