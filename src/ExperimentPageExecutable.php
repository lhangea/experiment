<?php

/**
 * @file
 * Contains \Drupal\experiment\ExperimentPageExecutable.
 */

namespace Drupal\experiment;

use Drupal\page_manager\PageExecutable;

/**
 * Represents a page entity during runtime execution.
 */
class ExperimentPageExecutable extends PageExecutable {

  /**
   * {@inheritdoc}
   */
  public function selectDisplayVariant() {
    if ($experiment_id = $this->getExperimentForPage()) {
      // Display the variant selected by the algorithm.
      $experiment = \Drupal::entityManager()->getStorage('experiment')->load($experiment_id);
      $algorithm = \Drupal::getContainer()->get('plugin.manager.mab_algorithm')
        ->createInstanceFromExperiment($experiment);

      $selected_variant_id = $algorithm->select();
      $algorithm->updateAverageWithNullReward($selected_variant_id);
      $collection = $this->page->getPluginCollections();
      $variant = $collection['display_variants']->get($selected_variant_id);
      $variant->setContexts($this->getContexts());
      $variant->setExecutable($this);
      return $variant;
    }
    else {
      // Let the page manager module decide which variant to show.
      return parent::selectDisplayVariant();
    }
  }

  /**
   * Determines if there is an experiment running on the current page.
   *
   * @return bool
   *   TRUE if there is an experiment.
   *   FALSE otherwise.
   */
  public function getExperimentForPage() {
    $query = \Drupal::entityQuery('experiment')
      ->condition('page', $this->getPage()->id(), '=');
    $experiment_ids = $query->execute();

    if (empty($experiment_ids)) {
      return FALSE;
    }
    else {
      return array_shift($experiment_ids);
    }
  }

}
