<?php

/**
 * @file
 * Contains \Drupal\experiment\ExperimentServiceProvider.
 */

namespace Drupal\experiment;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Overrides the page_manager.executable_factory service to return a different
 * PageExecutableInterface object.
 */
class ExperimentServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Alter the executable factory service defined by the page manager module.
    $definition = $container->getDefinition('page_manager.executable_factory');
    $definition->setClass('Drupal\experiment\ExperimentPageExecutableFactory');
  }

}
