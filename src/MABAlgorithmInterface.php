<?php

/**
 * @file
 * Contains \Drupal\experiment\MABAlgorithmInterface.
 */

namespace Drupal\experiment;

/**
 * Defines an interface for multi armed bandit algorithm plugin definitions.
 */
interface MABAlgorithmInterface {

  /**
   * @todo Add comments here.
   */
  public function select();

  /**
   * @todo Add comments here.
   */
  public function update();

}
