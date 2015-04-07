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

  /**
   * Returns the blocks associated with this experiment.
   *
   * @return array
   *   The blocks array.
   */
  public function getBlocks();

  /**
   * Set the blocks associated with the experiment.
   *
   * @param array $blocks Array of blocks associated with the experiment.
   */
  public function setBlocks(array $blocks);

  /**
   * Returns the id of the algorithm plugin.
   *
   * @return string
   *   Id of the algorithm.
   */
  public function getAlgorithm();

  /**
   * Set the algorithm.
   *
   * @param string $algorithm Algorithm used by the experiment.
   */
  public function setAlgorithm($algorithm);

  /**
   * Add a block to the list of block of this experiment.
   *
   * @param string $block
   *   The plugin id of the block.
   */
  public function addBlock($block);


}
