<?php

namespace Drupal\jobiqo_graphql\Wrappers\Resolver;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\Resolver\ResolverInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Resolves an nested array value under given list of keys.
 */
class NestedArrayValueResolver implements ResolverInterface {

  /**
   * List of keys to get value for.
   *
   * @var array
   */
  protected $keys;

  /**
   * DateTimeResolver constructor.
   *
   * @param array $keys
   *   List of keys to get value for.
   */
  public function __construct(array $keys) {
    $this->keys = $keys;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($array, $args, ResolveContext $context, ResolveInfo $info) {
    $value = &$array;
    foreach ($this->keys as $key) {
      $value = &$value[$key];
    }
    return $value;
  }

}
