<?php

namespace Drupal\jobiqo_graphql\Plugin\GraphQL\DataProducer\EntityDefinition;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use GraphQL\Type\Definition\ResolveInfo;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;

/**
 * Gets entity definition for a given entity type.
 *
 * @DataProducer(
 *   id = "entity_definition",
 *   name = @Translation("Entity definition"),
 *   description = @Translation("Return entity definitions for given entity type."),
 *   consumes = {
 *     "entity_type" = @ContextDefinition("string",
 *       label = @Translation("Entity type")
 *     ),
 *     "bundle" = @ContextDefinition("string",
 *       label = @Translation("Bundle"),
 *       required = FALSE
 *     ),
 *     "field_types" = @ContextDefinition("FieldTypes",
 *       label = @Translation("Field types"),
 *       default = "ALL",
 *       required = FALSE
 *     )
 *   },
 *   produces = @ContextDefinition("any",
 *     entity_definition = @Translation("Entity definition")
 *   )
 * )
 */
class EntityDefinition extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

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
   * @param string $plugin_id
   *   The plugin id.
   * @param array $plugin_definition
   *   The plugin definition array.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity type manager service.
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    array $plugin_definition,
    EntityTypeManager $entity_type_manager
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Resolves entity definition for a given entity type.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $bundle
   *   Optional. The entity bundle which are stored as a context for upcoming
   *   data producers deeper in hierarchy.
   * @param string $field_types
   *   Optional. The field types to retrieve (base fields, configurable fields,
   *   or both) which are stored as a context for upcoming data producers deeper
   *   in hierarchy.
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *   Refinable cacheability metadata.
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   *   Field resolver related context.
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *   Field resolver related info.
   *
   * @return \Drupal\Core\Entity\EntityTypeInterface
   *   The entity definition.
   */
  public function resolve($entity_type,
    $bundle = NULL,
    $field_types = NULL,
    RefinableCacheableDependencyInterface $metadata,
    ResolveContext $context,
    ResolveInfo $info
  ) {
    if ($bundle) {
      $bundle_info = \Drupal::service('entity_type.bundle.info')->getBundleInfo($entity_type);
      if (isset($bundle_info[$bundle])) {
        $bundle_context = $bundle_info[$bundle];
        $bundle_context['key'] = $bundle;
        $context->setContext('bundle', $bundle_context, $info);
      }
    }

    if ($field_types) {
      $context->setContext('field_types', $field_types, $info);
    }

    return $this->entityTypeManager->getDefinition($entity_type);
  }

}
