<?php

namespace Drupal\jobiqo_graphql\Plugin\GraphQL\DataProducer\EntityDefinition;

use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\Entity\BaseFieldOverride;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use GraphQL\Type\Definition\ResolveInfo;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;

/**
 * Retrieve the list of fields from a given entity definition.
 *
 * @DataProducer(
 *   id = "entity_definition_fields",
 *   name = @Translation("Entity definition fields"),
 *   description = @Translation("Return entity definition fields."),
 *   consumes = {
 *     "entity_definition" = @ContextDefinition("any",
 *       label = @Translation("Entity definition")
 *     )
 *   },
 *   produces = @ContextDefinition("any",
 *     entity_definition_field = @Translation("Entity definition field")
 *   )
 * )
 */
class Fields extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

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
   * Resolves the list of fields for a given entity.
   *
   * Respects the optional context parameters "bundle" and "field_types". If
   * bundle context is set it resolves the fields only for that entity bundle.
   * The same goes for field types when either base fields of configurable
   * fields may be returned.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_definition
   *   The entity type definition.
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *   Refinable cacheability metadata.
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   *   Field resolver related context.
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *   Field resolver related info.
   *
   * @return mixed
   *   List of fields.
   */
  public function resolve(
    EntityTypeInterface $entity_definition,
    RefinableCacheableDependencyInterface $metadata,
    ResolveContext $context,
    ResolveInfo $info
  ) {
    $entity_definition->getBundleEntityType();
    /** @var \Drupal\Core\Entity\ContentEntityType $value */
    if ($entity_definition instanceof ContentEntityType) {
      if ($bundle_context = $context->getContext('bundle', $info)) {
        $key = $bundle_context['key'];
        $id = $entity_definition->id();
        $entity_id = $id . '.' . $id . '.' . $key;
        $fields = \Drupal::entityManager()->getFieldDefinitions($id, $key);
      }
      else {
        $id = $entity_definition->id();
        $entity_id = $id . '.' . $id . '.default';
        $fields = \Drupal::entityManager()->getFieldDefinitions($id, $id);
      }

      /** @var \Drupal\Core\Config\Entity\ConfigEntityStorage $form_display_context */
      $form_display_context = $this->entityTypeManager
        ->getStorage('entity_form_display')
        ->load($entity_id);

      $context->setContext('entity_form_display', $form_display_context, $info);
      if ($field_types = $context->getContext('field_types', $info)) {
        foreach ($fields as $field) {
          if ($field_types === 'BASE_FIELDS') {
            if ($field instanceof BaseFieldDefinition) {
              yield $field;
            }
          }
          elseif ($field_types === 'FIELD_CONFIG') {
            if ($field instanceof FieldConfig || $field instanceof BaseFieldOverride) {
              yield $field;
            }
          }
          else {
            yield $field;
          }
        }
      }
      else {
        yield from $fields;
      }
    }
  }

}
