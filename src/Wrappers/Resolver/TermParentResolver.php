<?php

namespace Drupal\jobiqo_graphql\Wrappers\Resolver;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\Resolver\ResolverInterface;
use Drupal\taxonomy\Entity\Term;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Term parent resolver.
 */
class TermParentResolver implements ResolverInterface {

  /**
   * {@inheritdoc}
   */
  public function resolve($term, $args, ResolveContext $context, ResolveInfo $info) {
    if ($term instanceof Term) {
      // We can safely assume a term is not a child of 2 terms.
      return $term->get('parent')[0]->target_id;
    }
    elseif (!empty($term->parents)) {
      return $term->parents[0];
    }
  }

}
