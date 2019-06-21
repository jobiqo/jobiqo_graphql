<?php

namespace Drupal\jobiqo_graphql\Wrappers\Resolver;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\Resolver\ResolverInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Resolves an array value under specific key.
 */
class ArrayValueResolver implements ResolverInterface {

  /**
   * Array key to get value for.
   *
   * @var string
   */
  protected $key;

  /**
   * ArrayValueResolver constructor.
   *
   * @param string $key
   *   Array key to get value for.
   */
  public function __construct(string $key) {
    $this->key = $key;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($array, $args, ResolveContext $context, ResolveInfo $info) {
    return $array[$this->key];
  }

}
