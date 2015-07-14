<?php

namespace Drupal\experiment\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Class ExperimentCreateTest
 * @package Drupal\experiment\Tests
 *
 * @group experiment
 */
class ExperimentCreateTest extends WebTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = array('experiment');

  /**
   * A simple user with 'access content' permission
   */
  private $user;

  /**
   * Perform any initial set up tasks that run before every test method
   */
  public function setUp() {
    parent::setUp();
    $this->user = $this->drupalCreateUser(array('administer experiments'));
  }

  /**
   * Tests the creation of an experiment.
   */
  public function testExperimentCreate() {
    $this->drupalLogin($this->user);
    // Go to the experiment listing page.
    $this->drupalGet('admin/structure/experiment');
    $this->assertResponse(200);
    $this->assertRaw(t('There is no Experiment yet.'));
    $this->clickLink('Add new experiment');
    // Check if we are redirected on the experiment add page.
    $this->assertUrl(\Drupal::url('entity.experiment.add_form'), [], 'Directed to correct url.');
    $random_string = $this->randomMachineName(8);
    // Data for the form.
    $edit = array(
      'label' => $random_string,
      'id' => strtolower($random_string),
      'variations_set[blocks]' => 'system_powered_by_block',
    );
    $this->drupalPostForm(NULL, $edit, t('Create Experiment'));
    // Check that we cannot submit the form without at least 1 action.
    $this->assertRaw(t('You need to add at least 1 variation to the experiment'));
    // Add an action with Ajax.
    $this->drupalPostAjaxForm(NULL, $edit, array('op' => t('Add Block')));
    // Try again to submit the form.
    $this->drupalPostForm(NULL, $edit, t('Create Experiment'));
    // Check that the experiment has been created correctly and that the user
    // has been redirected on the listing page.
    $this->assertUrl(\Drupal::url('entity.experiment.list'), [], 'Directed to correct url.');
    $this->assertRaw(t('Experiment %label has been added.', array('%label' => $edit['label'])));
    $this->assertNoRaw(t('There is no Experiment yet.'));
  }

}
