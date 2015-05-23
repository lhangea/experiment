<?php

/**
 * @file
 * Contains \Drupal\experiment\Plugin\Block\ExperimentBlock.
 */

namespace Drupal\experiment\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Start a new experiment' block.
 *
 * @Block(
 *   id = "experiment_block",
 *   admin_label = @Translation("Experiment"),
 * )
 */
class ExperimentBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQueryFactory;

  /**
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\Query\QueryFactory $query_factory
   *   An entity query factory for the experiment entity type.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, QueryFactory $query_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityQueryFactory = $query_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.query')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [
      '#attached' => [
        'drupalSettings' => [
          'experiment_id' => $this->configuration['experiment']['id'],
        ],
        'library' => ['experiment/experiment.block'],
      ],
    ];
    $build['#attributes']['class'][] = $this->configuration['experiment']['id'];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  function blockForm($form, FormStateInterface $form_state) {
    $ids = $this->entityQueryFactory->get('experiment')->execute();
    $options = [];
    $experiments = \Drupal::entityManager()->getStorage('experiment')->loadMultiple($ids);
    foreach ($experiments as $experiment) {
      $options[$experiment->id()] = $experiment->label();
    }

    $form['experiment'] = array(
      '#type' => 'details',
      '#title' => $this->t('Experiment settings'),
      '#open' => TRUE,
    );

    $form['experiment']['block'] = array(
      '#type' => 'select',
      '#title' => t('Selected'),
      '#options' => $options,
      '#description' => t('Select experiment to associate with this block.'),
      '#default_value' => $this->configuration['experiment']['id'],
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['experiment']['id'] = $form_state->getValue(['experiment', 'block']);
  }

}
