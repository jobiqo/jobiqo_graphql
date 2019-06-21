<?php

namespace Drupal\jobiqo_graphql\Wrappers\Response;

/**
 * Type of response used when error is encountered.
 */
class ErrorResponse implements ResponseInterface {

  /**
   * The reason behind the error.
   *
   * @var string
   */
  protected $reason;

  /**
   * The HTTP response code.
   *
   * @var int
   */
  protected $code;

  /**
   * ErrorResponse constructor.
   *
   * @param string $reason
   *   The reason behind the error.
   * @param int $code
   *   The HTTP response code.
   */
  public function __construct(string $reason, int $code) {
    $this->reason = $reason;
    $this->code = $code;
  }

  /**
   * {@inheritdoc}
   */
  public function code(): int {
    return $this->code;
  }

  /**
   * Gets the reason of the error.
   *
   * @return string
   *   The reason behind the error.
   */
  public function reason(): string {
    return $this->reason;
  }

}
