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
    if ($operation == 'view') {
      return TRUE;
    }

    return parent::checkAccess($entity, $operation, $langcode, $account);
  }

}
