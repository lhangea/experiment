<?php

/**
 * @file
 * Contains Drupal\experiment\Controller\ExperimentListBuilder.
 */

namespace Drupal\experiment\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of experiment entities.
 */
class ExperimentListBuilder extends ConfigEntityListBuilder {

  /**
   * Builds the header row for the entity listing.
   *
   * @return array
   *   A render array structure of header strings.
   *
   * @see Drupal\Core\Entity\EntityListController::render()
   */
  public function buildHeader() {
    $header['label'] = $this->t('Experiment');
    $header['machine_name'] = $this->t('Machine Name');
    $header['floopy'] = $this->t('Floopy');
    return $header + parent::buildHeader();
  }

  /**
   * Builds a row for an entity in the entity listing.
   *
   * @param EntityInterface $entity
   *   The entity for which to build the row.
   *
   * @return array
   *   A render array of the table row for displaying the entity.
   *
   * @see Drupal\Core\Entity\EntityListController::render()
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $this->getLabel($entity);
    $row['machine_name'] = $entity->id();
    $row['floopy'] = $entity->floopy;

    return $row + parent::buildRow($entity);
  }

  /**
   * Adds some descriptive text to our entity list.
   *
   * @return array
   *   Renderable array.
   */
  public function render() {
    $build['description'] = array(
      '#markup' => $this->t("<p>The Experiment module defines an"
        . " Experiment entity type. This is a list of the Experiment entities currently"
        . " in your Drupal site.</p><p>By default, when you enable this"
        . " module, one entity is created from configuration. This is why we"
        . " call them Config Entities. Marvin, the paranoid android, is created"
        . " in the database when the module is enabled.</p><p>You can view a"
        . " list of Experiments here. You can also use the 'Operations' column to"
        . " edit and delete Experiments.</p>"),
    );
    $build[] = parent::render();
    return $build;
  }

}
