<?php

namespace Drupal\jobiqo_graphql\Wrappers\Violation;

/**
 * Violation collection.
 */
class ViolationCollection implements ViolationCollectionInterface {

  /**
   * List of violations.
   *
   * @var array
   */
  protected $violations = [];

  /**
   * {@inheritdoc}
   */
  public function addViolation($message, array $properties = []) {
    $properties['message'] = (string) $message;
    $this->violations[] = $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function getViolations(): array {
    return $this->violations;
  }

}
