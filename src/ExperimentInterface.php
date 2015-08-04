<?php

/**
 * @file
 * Contains \Drupal\experiment\ExperimentInterface.
 */

namespace Drupal\experiment;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining an experiment entity.
 */
interface ExperimentInterface extends ConfigEntityInterface {

//  /**
//   * Returns the list of actions associated with this experiment.
//   *
//   * @return array
//   *   The actions array.
//   */
//  public function getActions();
//
//  /**
//   * Set the actions associated with the experiment.
//   *
//   * @param array $actions
//   *   Array of actions associated with the experiment.
//   */
//  public function setActions(array $actions);

  /**
   * Returns the id of the algorithm plugin.
   *
   * @return string
   *   Id of the algorithm.
   */
  public function getAlgorithmId();

  /**
   * Set the algorithm.
   *
   * @param string $algorithm Algorithm used by the experiment.
   */
  public function setAlgorithmId($algorithm);

  /**
   * Returns the configuration of the algorithm plugin.
   *
   * @return string
   *   Id of the algorithm.
   */
  public function getAlgorithmConfig();

  /**
   * Set the configuration for the algorithm.
   *
   * @param array $config Algorithm configuration array.
   */
  public function setAlgorithmConfig($config);

//  /**
//   * Add an action to the list of this experiment's actions.
//   *
//   * @param string $action
//   *   The action id.
//   */
//  public function addAction($action);


}
