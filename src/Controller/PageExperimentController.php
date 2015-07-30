<?php

/**
 * @file
 * Contains \Drupal\experiment\Controller\PageExperimentController.
 */

namespace Drupal\experiment\Controller;

use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PageExperimentController {

  use StringTranslationTrait;

  /**
   * The entity form builder.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface
   */
  protected $entityFormBuilder;

  /**
   * Constructs an PageExperimentController object.
   *
   * @param \Drupal\Core\Entity\EntityFormBuilderInterface $entity_form_builder
   *   Entity form builder service.
   */
  public function __construct(EntityFormBuilderInterface $entity_form_builder) {
    $this->entityFormBuilder = $entity_form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('entity.form_builder')
    );
  }

  public function createPageForm() {
    // @todo Find out how to properly load an entity.
    // $entity = new Page(array(), 'page');
    // $form = $this->entityFormBuilder->getForm($entity, 'add');

    return ['#markup' => 'Page entity add form'];
  }

}
