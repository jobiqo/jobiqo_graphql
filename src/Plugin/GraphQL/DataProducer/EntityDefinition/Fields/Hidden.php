<?php

namespace Drupal\jobiqo_graphql\Plugin\GraphQL\DataProducer\EntityDefinition\Fields;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use GraphQL\Type\Definition\ResolveInfo;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;

/**
 * Retrieves the "hidden" property from a given field definition.
 *
 * @DataProducer(
 *   id = "entity_definition_field_hidden",
 *   name = @Translation("Entity definition field hidden"),
 *   description = @Translation("Return entity definition field hidden."),
 *   consumes = {
 *     "entity_definition_field" = @ContextDefinition("any",
 *       label = @Translation("Entity definition field")
 *     )
 *   },
 *   produces = @ContextDefinition("string",
 *     id = @Translation("Entity definition field hidden")
 *   )
 * )
 */
class Hidden extends DataProducerPluginBase {

  /**
   * Resolves the hidden property.
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
   * @return bool
   *   If the field is hidden or not.
   */
  public function resolve(
    FieldDefinitionInterface $entity_definition_field,
    RefinableCacheableDependencyInterface $metadata,
    ResolveContext $context,
    ResolveInfo $info
  ) {
    $form_display_context = $context->getContext('entity_form_display', $info);

    if ($form_display_context) {
      $hidden = $form_display_context->get('hidden');
      $field_id = $entity_definition_field->getName();

      if (isset($hidden[$field_id])) {
        return TRUE;
      }
      else {
        return FALSE;
      }
    }
    else {
      return FALSE;
    }
  }

}
