<?php

namespace Drupal\jobiqo_graphql\Plugin\GraphQL\ResolverMap;

use Drupal\Core\Plugin\PluginBase;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistry;
use Drupal\graphql\Plugin\ResolverMapPluginInterface;
use Drupal\jobiqo_graphql\Wrappers\Resolver\ArrayValueResolver;
use Drupal\jobiqo_graphql\Wrappers\Resolver\DateTimeResolver;
use Drupal\jobiqo_graphql\Wrappers\Resolver\TermIdResolver;
use Drupal\jobiqo_graphql\Wrappers\Resolver\TermLabelResolver;
use Drupal\jobiqo_graphql\Wrappers\Resolver\TermParentResolver;
use Drupal\jobiqo_graphql\Wrappers\Resolver\TypeResolver;

/**
 * Registers common field/type resolvers.
 *
 * @ResolverMap(
 *   id = "jobiqo_graphql_common_resolvers",
 *   schema = "jobiqo",
 * )
 */
class CommonResolvers extends PluginBase implements ResolverMapPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistry $registry, ResolverBuilder $builder) {
    // Route query.
    $registry->addFieldResolver('Query', 'route', $builder->compose(
      $builder->produce('route_load', [
        'mapping' => [
          'path' => $builder->fromArgument('path'),
        ],
      ]),
      $builder->produce('url_router', [
        'mapping' => [
          'path' => $builder->fromArgument('path'),
          'url' => $builder->fromParent(),
        ],
      ])
    ));
    // Entity definition query.
    $registry->addFieldResolver('Query', 'entityDefinition',
      $builder->produce('entity_definition', [
        'mapping' => [
          'entity_type' => $builder->fromArgument('entity_type'),
          'bundle' => $builder->fromArgument('bundle'),
          'field_types' => $builder->fromArgument('field_types'),
        ],
      ])
    );
    // Term tree query.
    $registry->addFieldResolver('Query', 'termTree',
      $builder->produce('taxonomy_load_tree', [
        'mapping' => [
          'vid' => $builder->fromArgument('vid'),
          'parent' => $builder->fromValue(0),
          'max_depth' => $builder->fromArgument('maxDepth'),
        ],
      ])
    );
    // Menu query.
    $registry->addFieldResolver('Query', 'menu',
      $builder->produce('entity_load', [
        'mapping' => [
          'type' => $builder->fromValue('menu'),
          'id' => $builder->fromArgument('name'),
        ],
      ])
    );

    // DateTime value.
    $registry->addFieldResolver('DateTime', 'value', new DateTimeResolver());

    // Link item url.
    $registry->addFieldResolver('LinkItem', 'url', new ArrayValueResolver('uri'));
    // Link item title.
    $registry->addFieldResolver('LinkItem', 'text', new ArrayValueResolver('title'));

    // Term id.
    $registry->addFieldResolver('Term', 'tid', new TermIdResolver());
    // Term label.
    $registry->addFieldResolver('Term', 'label', new TermLabelResolver());
    // Term parent.
    $registry->addFieldResolver('Term', 'parentId', new TermParentResolver());

    // Menu name.
    $registry->addFieldResolver('Menu', 'name',
      $builder->produce('property_path', [
        'mapping' => [
          'type' => $builder->fromValue('entity:menu'),
          'value' => $builder->fromParent(),
          'path' => $builder->fromValue('label'),
        ],
      ])
    );
    // Menu items.
    $registry->addFieldResolver('Menu', 'items',
      $builder->produce('menu_links', [
        'mapping' => [
          'menu' => $builder->fromParent(),
        ],
      ])
    );
    // Menu title.
    $registry->addFieldResolver('MenuItem', 'title',
      $builder->produce('menu_link_label', [
        'mapping' => [
          'link' => $builder->produce('menu_tree_link', [
            'mapping' => [
              'element' => $builder->fromParent(),
            ],
          ]),
        ],
      ])
    );
    // Menu children.
    $registry->addFieldResolver('MenuItem', 'children',
      $builder->produce('menu_tree_subtree', [
        'mapping' => [
          'element' => $builder->fromParent(),
        ],
      ])
    );
    // Menu url.
    $registry->addFieldResolver('MenuItem', 'url',
      $builder->produce('menu_link_url', [
        'mapping' => [
          'link' => $builder->produce('menu_tree_link', [
            'mapping' => [
              'element' => $builder->fromParent(),
            ],
          ]),
        ],
      ])
    );

    // Entity definition fields.
    $registry->addFieldResolver('EntityDefinition', 'label',
      $builder->produce('entity_definition_label', [
        'mapping' => [
          'entity_definition' => $builder->fromParent(),
        ],
      ])
    );
    $registry->addFieldResolver('EntityDefinition', 'fields',
      $builder->produce('entity_definition_fields', [
        'mapping' => [
          'entity_definition' => $builder->fromParent(),
        ],
      ])
    );
    $registry->addFieldResolver('EntityDefinitionField', 'id',
      $builder->produce('entity_definition_field_id', [
        'mapping' => [
          'entity_definition_field' => $builder->fromParent(),
        ],
      ])
    );
    $registry->addFieldResolver('EntityDefinitionField', 'label',
      $builder->produce('entity_definition_field_label', [
        'mapping' => [
          'entity_definition_field' => $builder->fromParent(),
        ],
      ])
    );
    $registry->addFieldResolver('EntityDefinitionField', 'description',
      $builder->produce('entity_definition_field_description', [
        'mapping' => [
          'entity_definition_field' => $builder->fromParent(),
        ],
      ])
    );
    $registry->addFieldResolver('EntityDefinitionField', 'type',
      $builder->produce('entity_definition_field_type', [
        'mapping' => [
          'entity_definition_field' => $builder->fromParent(),
        ],
      ])
    );
    $registry->addFieldResolver('EntityDefinitionField', 'required',
      $builder->produce('entity_definition_field_required', [
        'mapping' => [
          'entity_definition_field' => $builder->fromParent(),
        ],
      ])
    );
    $registry->addFieldResolver('EntityDefinitionField', 'multiple',
      $builder->produce('entity_definition_field_multiple', [
        'mapping' => [
          'entity_definition_field' => $builder->fromParent(),
        ],
      ])
    );
    $registry->addFieldResolver('EntityDefinitionField', 'maxNumItems',
      $builder->produce('entity_definition_field_max_num_items', [
        'mapping' => [
          'entity_definition_field' => $builder->fromParent(),
        ],
      ])
    );
    $registry->addFieldResolver('EntityDefinitionField', 'status',
      $builder->produce('entity_definition_field_status', [
        'mapping' => [
          'entity_definition_field' => $builder->fromParent(),
        ],
      ])
    );
    $registry->addFieldResolver('EntityDefinitionField', 'defaultValue',
      $builder->produce('entity_definition_field_default_value', [
        'mapping' => [
          'entity_definition_field' => $builder->fromParent(),
        ],
      ])
    );
    $registry->addFieldResolver('EntityDefinitionField', 'isReference',
      $builder->produce('entity_definition_field_reference', [
        'mapping' => [
          'entity_definition_field' => $builder->fromParent(),
        ],
      ])
    );
    $registry->addFieldResolver('EntityDefinitionField', 'isHidden',
      $builder->produce('entity_definition_field_hidden', [
        'mapping' => [
          'entity_definition_field' => $builder->fromParent(),
        ],
      ])
    );
    $registry->addFieldResolver('EntityDefinitionField', 'weight',
      $builder->produce('entity_definition_field_weight', [
        'mapping' => [
          'entity_definition_field' => $builder->fromParent(),
        ],
      ])
    );
    $registry->addFieldResolver('EntityDefinitionField', 'settings',
      $builder->produce('entity_definition_field_settings', [
        'mapping' => [
          'entity_definition_field' => $builder->fromParent(),
        ],
      ])
    );

    // Response type resolver.
    $registry->addTypeResolver('Response', [TypeResolver::class, 'resolveResponse']);
    // Content type resolver.
    $registry->addTypeResolver('Content', [TypeResolver::class, 'resolveContent']);
    // Paragraph type resolver.
    $registry->addTypeResolver('Paragraph', [TypeResolver::class, 'resolveParagraph']);
  }

}
