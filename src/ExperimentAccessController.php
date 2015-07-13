<?php

/**
 * @file
 * Contains \Drupal\experiment\ExperimentAccessController.
 */

namespace Drupal\experiment;

use Drupal\Core\Access\AccessResult;
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
    // For 'update' and 'delete' operations use the same permission.
    return AccessResult::allowedIf($account->hasPermission('administer experiments'))->cachePerPermissions();
  }

}
