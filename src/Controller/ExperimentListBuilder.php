<?php

/**
 * @file
 * Contains Drupal\experiment\Controller\ExperimentListBuilder.
 */

namespace Drupal\experiment\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\experiment\ExperimentInterface;

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
    $header['algorithm'] = $this->t('Algorithm');
    $header['blocks'] = $this->t('Blocks');
    return $header + parent::buildHeader();
  }

  /**
   * Builds a row for an entity in the entity listing.
   *
   * @param ExperimentInterface $entity
   *   The entity for which to build the row.
   *
   * @return array
   *   A render array of the table row for displaying the entity.
   *
   * @see Drupal\Core\Entity\EntityListController::render()
   */
  public function buildRow(ExperimentInterface $entity) {
    $row['label'] = $this->getLabel($entity);
    $row['machine_name'] = $entity->id();
    // @todo Display label instead.
    $row['algorithm'] = $entity->getAlgorithmId();
    // @todo UX improvements needed.
    // @todo Will be changed.
    $row['blocks'] = implode(", ", $entity->getBlocks());

    return $row + parent::buildRow($entity);
  }

  /**
   * Adds some descriptive text to our entity list.
   *
   * @return array
   *   Renderable array.
   */
  public function render() {
    $build['description'] = [
      '#markup' => $this->t("<p>This will be help text.</p>"),
    ];
    $build[] = parent::render();
    return $build;
  }

}
