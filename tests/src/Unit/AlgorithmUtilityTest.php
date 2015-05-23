<?php

/**
 * @file
 * Contains \Drupal\Tests\experiment\Unit\AlgorithmUtilityTest.
 */

namespace Drupal\Tests\experiment\Unit;

use Drupal\experiment\AlgorithmUtility;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\experiment\AlgorithmUtility
 * @group experiment
 */
class AlgorithmUtilityTest extends UnitTestCase {

  /**
   * @covers ::getRand
   */
  public function testGetRand() {
    $algorithm_utility = new AlgorithmUtility();
    $number = $algorithm_utility->getRand();
    $this->assertGreaterThanOrEqual(0, $number);
    $this->assertLessThanOrEqual(1, $number);
    // @todo Test the distribution also.
  }

  /**
   * @dataProvider providerTestGetIndMax
   * @covers ::getIndMax
   */
  public function testGetIndMax($expected_result, $array) {
    $algorithm_utility = new AlgorithmUtility();
    $this->assertEquals($expected_result, $algorithm_utility->getIndMax($array));
  }

  /**
   * @dataProvider providerIsFloatBetweenZeroAndOne
   * @covers ::isFloatBetweenZeroAndOne
   */
  public function testIsFloatBetweenZeroAndOne($expected_result, $string) {
    $algorithm_utility = new AlgorithmUtility();
    $this->assertEquals($expected_result, $algorithm_utility->isFloatBetweenZeroAndOne($string));
  }

  /**
   * Provides data and expected results for the test method.
   *
   * @return array
   *   Data and expected results.
   */
  public function providerTestGetIndMax() {
    // @todo Add more test data.
    return [
      ['key2', ['key1' => 1, 'key2' => 2]],
    ];
  }

  /**
   * Provides data and expected results for the test method.
   *
   * @return array
   *   Data and expected results.
   */
  public function providerIsFloatBetweenZeroAndOne() {
    return [
      [TRUE, '0.8'],
      [TRUE, 0.8],
      [TRUE, '0'],
      [TRUE, '1'],
      [FALSE, ''],
      [FALSE, '1.1'],
      [FALSE, '-1'],
      [FALSE, 'text'],
    ];
  }

}
