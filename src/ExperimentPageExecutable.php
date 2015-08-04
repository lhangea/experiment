<?php

/**
 * @file
 * Contains \Drupal\experiment\ExperimentPageExecutable.
 */

namespace Drupal\experiment;

use Drupal\page_manager\PageExecutable;
use Drupal\page_manager\Plugin\ContextAwareVariantInterface;
use Drupal\page_manager\Plugin\PageAwareVariantInterface;

/**
 * Represents a page entity during runtime execution.
 */
class ExperimentPageExecutable extends PageExecutable {

  /**
   * {@inheritdoc}
   */
  public function selectDisplayVariant() {
    if (!$this->selectedDisplayVariant) {
      foreach ($this->page->getVariants() as $display_variant) {
        if ($display_variant instanceof ContextAwareVariantInterface) {
          $display_variant->setContexts($this->getContexts());
        }
        if ($display_variant->access()) {
          if ($display_variant instanceof PageAwareVariantInterface) {
            $display_variant->setExecutable($this);
          }
          $this->selectedDisplayVariant = $display_variant;
          break;
        }
      }
    }
    return $this->selectedDisplayVariant;
  }

}
