<?php

namespace Drupal\jobiqo_graphql\Plugin\GraphQL\DataProducer\EntityDefinition\Fields;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Retrieves the "label" from a given field definition.
 *
 * @DataProducer(
 *   id = "entity_definition_field_label",
 *   name = @Translation("Entity definition field label"),
 *   description = @Translation("Return entity definition field label."),
 *   consumes = {
 *     "entity_definition_field" = @ContextDefinition("any",
 *       label = @Translation("Entity definition field")
 *     )
 *   },
 *   produces = @ContextDefinition("string",
 *     id = @Translation("Entity definition field label")
 *   )
 * )
 */
class Label extends DataProducerPluginBase {

  /**
   * Resolves the field label.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $entity_definition_field
   *   The entity field definition.
   *
   * @return string
   *   The field label.
   */
  public function resolve(FieldDefinitionInterface $entity_definition_field) {
    return $entity_definition_field->getLabel();
  }

}
