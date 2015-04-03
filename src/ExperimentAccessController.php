<?php

/**
 * @file
 * Contains \Drupal\experiment\ExperimentAccessController.
 */

namespace Drupal\experiment;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines an access controller for the experiment entity.
 */
class ExperimentAccessController extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  public function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    // The $operation parameter tells you what sort of operation access is
    // being checked for.
//    if ($operation == 'view') {
//      return TRUE;
//    }
    // Other than the view operation, we're going to be insanely lax about
    // access. Don't try this at home!
//    return parent::checkAccess($entity, $operation, $langcode, $account);
    return TRUE;
  }

}
