<?php

namespace Drupal\experiment;

interface MABAlgorithmManagerInterface {

    /**
     * Factory method for creating an algorithm plugin instance.
     *
     * @param \Drupal\experiment\ExperimentInterface $experiment
     *   Experiment configuration entity object.
     *
     * @return \Drupal\experiment\MABAlgorithmInterface
     *   Configured algorithm plugin instance.
     */
    public function createInstanceFromExperiment(ExperimentInterface $experiment);
}
