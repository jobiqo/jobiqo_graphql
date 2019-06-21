<?php

namespace Drupal\jobiqo_graphql\Plugin\GraphQL\DataProducer\EntityDefinition\Fields;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use GraphQL\Type\Definition\ResolveInfo;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;

/**
 * Retrieves the weight value of a field.
 *
 * @DataProducer(
 *   id = "entity_definition_field_weight",
 *   name = @Translation("Entity definition field weight"),
 *   description = @Translation("Return entity definition field weight."),
 *   consumes = {
 *     "entity_definition_field" = @ContextDefinition("any",
 *       label = @Translation("Entity definition field")
 *     )
 *   },
 *   produces = @ContextDefinition("string",
 *     id = @Translation("Entity definition field weight")
 *   )
 * )
 */
class Weight extends DataProducerPluginBase {

  /**
   * Resolves the "weight" property.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $entity_definition_field
   *   The entity field definition.
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *   Refinable cacheability metadata.
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   *   Field resolver related context.
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *   Field resolver related info.
   *
   * @return int
   *   The field weight.
   */
  public function resolve(
    FieldDefinitionInterface $entity_definition_field,
    RefinableCacheableDependencyInterface $metadata,
    ResolveContext $context,
    ResolveInfo $info
  ) {
    $form_display_context = $context->getContext('entity_form_display', $info);

    if ($form_display_context) {
      $content = $form_display_context->get('content');
      $field_id = $entity_definition_field->getName();

      if (isset($content[$field_id])) {
        return $content[$field_id]['weight'];
      }
      else {
        return 0;
      }
    }
    else {
      return 0;
    }
  }

}
