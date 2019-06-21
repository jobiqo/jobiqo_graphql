<?php

namespace Drupal\jobiqo_graphql\Plugin\GraphQL\DataProducer\EntityDefinition\Fields;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use GraphQL\Type\Definition\ResolveInfo;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;

/**
 * Retrieves the "settings" from a given field definition.
 *
 * @DataProducer(
 *   id = "entity_definition_field_settings",
 *   name = @Translation("Entity definition field settings"),
 *   description = @Translation("Return entity definition field settings."),
 *   consumes = {
 *     "entity_definition_field" = @ContextDefinition("any",
 *       label = @Translation("Entity definition field")
 *     )
 *   },
 *   produces = @ContextDefinition("string",
 *     id = @Translation("Entity definition field settings")
 *   )
 * )
 */
class Settings extends DataProducerPluginBase {

  /**
   * Resolves the field settings.
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
   * @return mixed
   *   The field settings.
   */
  public function resolve(
    FieldDefinitionInterface $entity_definition_field,
    RefinableCacheableDependencyInterface $metadata,
    ResolveContext $context,
    ResolveInfo $info
  ) {
    $field_id = $entity_definition_field->getName();
    $settings = $entity_definition_field->getSettings();

    $form_display_context = $context->getContext('entity_form_display', $info);
    if ($form_display_context) {
      $content = $form_display_context->get('content');
      if (isset($content[$field_id])) {
        $form_settings = $content[$field_id]['settings'];
        $settings['form_settings'] = $form_settings;
      }
    }

    foreach ($settings as $key => $value) {
      yield [
        'key' => $key,
        'value' => $value,
      ];
    }
  }

}
