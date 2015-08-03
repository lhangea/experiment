<?php

/**
 * @file
 * Contains \Drupal\experiment\ExperimentPageExecutableFactory.
 */

namespace Drupal\experiment;

use Drupal\page_manager\PageExecutableFactoryInterface;
use Drupal\page_manager\PageInterface;

/**
 * Provides a factory for page executables.
 */
class ExperimentPageExecutableFactory implements PageExecutableFactoryInterface {

  /**
   * {@inheritdoc}
   */
  public function get(PageInterface $page) {
    return new ExperimentPageExecutable($page);
  }

}
