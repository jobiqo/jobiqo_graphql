<?php

namespace Drupal\jobiqo_graphql\Wrappers\Response;

use Drupal\jobiqo_graphql\Wrappers\Violation\ViolationCollection;

/**
 * Base class for responses containing the violations.
 */
abstract class ViolationResponse implements ResponseInterface {

  /**
   * Violations.
   *
   * @var \Drupal\jobiqo_graphql\Wrappers\Violation\ViolationCollection
   */
  protected $violations;

  /**
   * ViolationResponse constructor.
   *
   * @param \Drupal\jobiqo_graphql\Wrappers\Violation\ViolationCollection $violations
   *   List of violations happened during user registration mutation.
   */
  public function __construct(ViolationCollection $violations = NULL) {
    $this->violations = $violations ?: new ViolationCollection();
  }

  /**
   * {@inheritdoc}
   */
  public function code(): int {
    return 200;
  }

  /**
   * Gets the violations.
   *
   * @return array
   *   List of violations happened during user registration mutation.
   */
  public function errors(): array {
    return $this->violations->getViolations();
  }

}
