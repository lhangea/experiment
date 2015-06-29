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
   * Tests that the 'admin/structure/experiment/' path returns the right content.
   */
  public function testExperimentListExists() {
    $this->drupalLogin($this->user);
    $this->drupalGet('admin/structure/experiment');
    $this->assertResponse(200);
  }

}
