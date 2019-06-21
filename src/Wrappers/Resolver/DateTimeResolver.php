<?php

namespace Drupal\jobiqo_graphql\Wrappers\Resolver;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\Resolver\ResolverInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Datetime resolver.
 */
class DateTimeResolver implements ResolverInterface {

  /**
   * {@inheritdoc}
   */
  public function resolve($time, $args, ResolveContext $context, ResolveInfo $info) {
    if ($dt = date(\DateTime::ISO8601, $time)) {
      return $dt;
    }
    else {
      throw new \Exception('Error converting date');
    }
  }

}
