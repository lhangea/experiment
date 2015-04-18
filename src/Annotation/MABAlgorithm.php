<?php

/**
 * @file
 * Contains \Drupal\experiment\Annotation\MABAlgorithm.
 */

namespace Drupal\experiment\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a MABAlgorithm annotation object.
 *
 * Additional annotation keys for field types can be defined in
 * hook_mab_algorithm_info_alter().
 *
 * Plugin Namespace: Plugin\MABAlgorithm
 *
 * @Annotation
 */
class MABAlgorithm extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the multi armed bandit algorithm plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * A short description of the algorithm plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

}
