<?php

namespace Drupal\jobiqo_graphql\Wrappers\Response;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\jobiqo_graphql\Wrappers\Violation\ViolationCollection;

/**
 * Type of response used when content is returned.
 */
class ContentResponse extends ViolationResponse implements ResponseInterface {

  /**
   * The content to be served (eg job page, landing page).
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface|null
   */
  protected $content;

  /**
   * ContentResponse constructor.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface|null $content
   *   The content to be served (eg job page, landing page).
   * @param \Drupal\jobiqo_graphql\Wrappers\Violation\ViolationCollection $violations
   *   List of violations happened during user registration mutation.
   */
  public function __construct(ContentEntityInterface $content = NULL, ViolationCollection $violations = NULL) {
    parent::__construct($violations);
    $this->content = $content;
  }

  /**
   * {@inheritdoc}
   */
  public function code(): int {
    return 200;
  }

  /**
   * Gets the content to be served.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface|null
   *   The content to be served (eg job page, landing page).
   */
  public function content() {
    return $this->content;
  }

}
