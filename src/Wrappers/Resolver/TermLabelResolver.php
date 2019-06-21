<?php

namespace Drupal\jobiqo_graphql\Wrappers\Resolver;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\Resolver\ResolverInterface;
use Drupal\taxonomy\Entity\Term;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Term label resolver.
 */
class TermLabelResolver implements ResolverInterface {

  /**
   * {@inheritdoc}
   */
  public function resolve($term, $args, ResolveContext $context, ResolveInfo $info) {
    // We don't always load the full term so sometimes we might just get a
    // stdObject.
    if ($term instanceof Term) {
      return $term->label();
    }
    elseif (isset($term->name)) {
      return $term->name;
    }
  }

}
