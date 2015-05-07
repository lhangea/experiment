<?php

namespace Drupal\experiment;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Multi armed bandit algorithm plugin manager.
 */
class MABAlgorithmManager extends DefaultPluginManager implements MABAlgorithmManagerInterface {

  /**
   * Constructs a MABAlgorithmManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/MABAlgorithm', $namespaces, $module_handler, 'Drupal\experiment\MABAlgorithmInterface', 'Drupal\experiment\Annotation\MABAlgorithm');
    $this->alterInfo('mab_algorithm_info');
    $this->setCacheBackend($cache_backend, 'mab_algorithm_info_plugins');
  }

  /**
   * {@inheritdoc}
   */
  public function createInstanceFromExperiment(ExperimentInterface $experiment) {
    $algorithm_configuration = $experiment->getAlgorithmConfig() ? $experiment->getAlgorithmConfig() : [];

    // When first submitting the experiment form save the experiment id
    // in the algorithm configuration.
    if (!$algorithm_configuration['experiment_id'] && $experiment->id()) {
      $algorithm_configuration['experiment_id'] = $experiment->id();
    }
    // Create an instance of the algorithm associated with the experiment.
    $algorithm = $this->createInstance($experiment->getAlgorithmId(), $algorithm_configuration);

    return $algorithm;
  }

}
