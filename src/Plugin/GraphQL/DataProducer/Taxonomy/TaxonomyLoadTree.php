<?php

namespace Drupal\jobiqo_graphql\Plugin\GraphQL\DataProducer\Taxonomy;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Loads the taxonomy tree.
 *
 * @DataProducer(
 *   id = "taxonomy_load_tree",
 *   name = @Translation("Load multiple taxonomy terms"),
 *   description = @Translation("Loads Taxonomy terms as a tree"),
 *   produces = @ContextDefinition("taxonomy tree",
 *     label = @Translation("Taxonomy tree")
 *   ),
 *   consumes = {
 *     "vid" = @ContextDefinition("string",
 *       label = @Translation("Vocabulary id")
 *     ),
 *     "parent" = @ContextDefinition("Int",
 *       label = @Translation("The term ID under which to generate the tree"),
 *       required = FALSE
 *     ),
 *     "max_depth" = @ContextDefinition("Int",
 *       label = @Translation("Maximum tree depth"),
 *       required = FALSE
 *     )
 *   }
 * )
 */
class TaxonomyLoadTree extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * EntityLoad constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param array $pluginDefinition
   *   The plugin definition array.
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The entity type manager service.
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    array $configuration,
    $pluginId,
    array $pluginDefinition,
    EntityTypeManager $entityTypeManager
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Resolves the taxonomy tree for given vocabulary.
   *
   * @param string $vid
   *   The vocanulary ID.
   * @param int $parent
   *   The ID of the parent's term to load the tree for.
   * @param int $max_depth
   *   Max depth to search in.
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *   Refinable cacheability metadata.
   *
   * @return object[]
   *   A list of stdClass terms in the given vocabulary.
   */
  public function resolve(string $vid, $parent = 0, $max_depth = 10, RefinableCacheableDependencyInterface $metadata) {
    // @todo This should use a buffer system similar to other entities are using.
    $terms = $this->entityTypeManager
      ->getStorage('taxonomy_term')
      ->loadTree($vid, $parent, $max_depth);

    return $terms;
  }

}
