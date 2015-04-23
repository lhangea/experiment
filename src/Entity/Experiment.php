<?php

/**
 * @file
 * Contains Drupal\experiment\Entity\Experiment.
 */

namespace Drupal\experiment\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\experiment\ExperimentInterface;

/**
 * Defines the experiment config entity.
 *
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
 *     "access" = "Drupal\experiment\ExperimentAccessController",
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
 *     "edit-form" = "/admin/structure/experiment/manage/{experiment}",
 *     "delete-form" = "/admin/structure/experiment/manage/{experiment}/delete"
 *   }
 * )
 */
class Experiment extends ConfigEntityBase implements ExperimentInterface {

  /**
   * The experiment ID.
   *
   * @var string
   */
  public $id;

  /**
   * The experiment label.
   *
   * @var string
   */
  public $label;

  /**
   * The array of blocks involved in this experiment.
   *
   * @var array
   */
  public $blocks = array();

  /**
   * The id of the algorithm used by an experiment.
   *
   * @var string
   */
  public $algorithm;

  /**
   * The configuration of the algorithm used by an experiment.
   *
   * @var array
   */
  public $algorithmConfig;

  /**
   * {@inheritdoc}
   */
  public function getBlocks() {
    return $this->blocks;
  }

  /**
   * {@inheritdoc}
   */
  public function setBlocks(array $blocks) {
    $this->blocks = $blocks;
  }

  /**
   * {@inheritdoc}
   */
  public function getAlgorithm() {
    return $this->algorithm;
  }

  /**
   * {@inheritdoc}
   */
  public function setAlgorithm($algorithm) {
    $this->algorithm = $algorithm;
  }

  /**
   * {@inheritdoc}
   */
  public function addBlock($block) {
    $this->blocks[] = $block;
  }

  /**
   * {@inheritdoc}
   */
  public function getAlgorithmConfig()
  {
    return $this->algorithmConfig;
  }

  /**
   * {@inheritdoc}
   */
  public function setAlgorithmConfig($config)
  {
    $this->algorithmConfig = $config;
  }
}
