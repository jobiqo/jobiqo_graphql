<?php

namespace Drupal\jobiqo_graphql\Wrappers\Response;

/**
 * Redirect response.
 */
class RedirectResponse implements ResponseInterface {

  /**
   * The target path to redirect to.
   *
   * @var string
   */
  protected $target;

  /**
   * The HTTP response code.
   *
   * @var int
   */
  protected $code;

  /**
   * RedirectResponse constructor.
   *
   * @param string $target
   *   The target path to redirect to.
   * @param int $code
   *   The HTTP response code.
   */
  public function __construct(string $target, int $code) {
    $this->target = $target;
    $this->code = $code;
  }

  /**
   * {@inheritdoc}
   */
  public function code(): int {
    return $this->code;
  }

  /**
   * {@inheritdoc}
   */
  public function target(): string {
    return $this->target;
  }

}
