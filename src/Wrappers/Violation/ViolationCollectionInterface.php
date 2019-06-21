<?php

namespace Drupal\jobiqo_graphql\Wrappers\Violation;

/**
 * Violation collection interface.
 */
interface ViolationCollectionInterface {

  /**
   * Adds the violation.
   *
   * @param string|\Drupal\Core\StringTranslation\TranslatableMarkup $message
   *   Violation message.
   * @param array $properties
   *   Other properties related to the violation.
   */
  public function addViolation($message, array $properties = []);

  /**
   * Gets the violations.
   *
   * @return \Drupal\jobiqo_graphql\Wrappers\Violation\Violation[]
   *   Violations.
   */
  public function getViolations(): array;

}
