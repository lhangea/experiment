<?php

/**
 * @file
 * Contains Drupal\experiment\Controller\ExperimentListBuilder.
 */

namespace Drupal\experiment\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;

/**
 * Provides a listing of experiment config entities.
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
      '#markup' => $this->t("<p>This is a list of all the experiments from this website.</p>"),
    ];
    $build[] = parent::render();

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations(EntityInterface $entity) {
    $operations = parent::getOperations($entity);
    // Add the link to the results page.
    $operations['results'] = [
      'title' => 'Results',
      'weight' => 150,
      'url' => Url::fromRoute('experiment.results', ['experiment' => $entity->id()]),
    ];

    return $operations;
  }

}
