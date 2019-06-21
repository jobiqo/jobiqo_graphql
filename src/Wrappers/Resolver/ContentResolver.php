<?php

namespace Drupal\jobiqo_graphql\Wrappers\Resolver;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\Resolver\ResolverInterface;
use Drupal\jobiqo_graphql\Wrappers\Response\ContentResponse;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Content resolver.
 */
class ContentResolver implements ResolverInterface {

  /**
   * {@inheritdoc}
   */
  public function resolve($content = NULL, $args, ResolveContext $context, ResolveInfo $info) {
    if ($content) {
      return new ContentResponse($content, NULL);
    }
    else {
      return new ContentResponse(NULL, NULL);
    }
  }

}
