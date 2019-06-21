<?php

namespace Drupal\jobiqo_graphql\Plugin\GraphQL\DataProducer\EntityDefinition\Fields;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Retrieves the "description" from a given field definition.
 *
 * @DataProducer(
 *   id = "entity_definition_field_description",
 *   name = @Translation("Entity definition field description"),
 *   description = @Translation("Return entity definition field description."),
 *   consumes = {
 *     "entity_definition_field" = @ContextDefinition("any",
 *       label = @Translation("Entity definition field")
 *     )
 *   },
 *   produces = @ContextDefinition("string",
 *     id = @Translation("Entity definition field description")
 *   )
 * )
 */
class Description extends DataProducerPluginBase {

  /**
   * Resolves the field description.
   *
   * @param \Drupal\Core\Field\FieldDefinitionInterface $entity_definition_field
   *   The entity field definition.
   *
   * @return string
   *   The description
   */
  public function resolve(FieldDefinitionInterface $entity_definition_field) {
    return $entity_definition_field->getDescription();
  }

}
