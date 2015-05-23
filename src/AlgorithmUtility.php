<?php

/**
 * @file Contains AlgorithmUtility class.
 */

namespace Drupal\experiment;

class AlgorithmUtility {

  /**
   * Helper function which generates a random number.
   *
   * @return float
   *   Random number between 0 and 1.
   */
  public function getRand() {
    return mt_rand() / mt_getrandmax();
  }

  /**
   * Finds the index of the maximum key from an array.
   *
   * @param array
   *   Array containing key value pairs.
   *
   * @return string
   *   The key of the max value from the array.
   */
  function getIndMax($array) {
    $max_key = -1;
    $max_val = -1;

    // Logical or between all array elements to determine if we are right after
    // a new experiment has been initialized.
    $initial = !in_array(TRUE, $array, FALSE);
    if ($initial) {
      return array_rand($array);
    }
    else {
      foreach ($array as $key => $value) {
        if ($value > $max_val) {
          $max_key = $key;
          $max_val = $value;
        }
      }
      return $max_key;
    }
  }


  /**
   * Checks if the float value of a string is a decimal number between 0 and 1.
   *
   * @param $string
   *   The string to be checked.
   * @return bool
   *   TRUE if the value is between 0 and 1 (inclusive)
   *   FALSE otherwise
   */
  public function isFloatBetweenZeroAndOne($string) {
    $number = floatval($string);

    return $number > 0 && $number <= 1 || $number == 0 && $string == '0';
  }

}