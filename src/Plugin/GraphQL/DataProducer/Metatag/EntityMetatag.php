<?php

namespace Drupal\jobiqo_graphql\Plugin\GraphQL\DataProducer\Metatag;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\graphql\GraphQL\Buffers\EntityBuffer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\metatag\MetatagManagerInterface;
use GraphQL\Deferred;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Gets metatags for given entity.
 *
 * @DataProducer(
 *   id = "entity_metatag",
 *   name = @Translation("Entity Metatags"),
 *   description = @Translation("Returns a list of metatags associated with the entity. Each has a metaKey and a metaValue"),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Metatags")
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Parent entity")
 *     )
 *   }
 * )
 */
class EntityMetatag extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity buffer service.
   *
   * @var \Drupal\graphql\GraphQL\Buffers\EntityBuffer
   */
  protected $entityBuffer;

  /**
   * The metatag manager.
   *
   * @var \Drupal\metatag\MetatagManagerInterface
   */
  private $metatagManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  private $entityTypeManager;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('graphql.buffer.entity'),
      $container->get('metatag.manager'),
      $container->get('renderer')
    );
  }

  /**
   * EntityMetatag constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $plugin_id
   *   The plugin id.
   * @param array $plugin_definition
   *   The plugin definition array.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\graphql\GraphQL\Buffers\EntityBuffer $entity_buffer
   *   The entity buffer service.
   * @param \Drupal\metatag\MetatagManagerInterface $metatag_manager
   *   The metatag manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(array $configuration, string $plugin_id, array $plugin_definition, EntityTypeManager $entity_type_manager, EntityBuffer $entity_buffer, MetatagManagerInterface $metatag_manager, RendererInterface $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityBuffer = $entity_buffer;
    $this->metatagManager = $metatag_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->renderer = $renderer;
  }

  /**
   * Resolves the metatags for a given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *   Refinable cacheability metadata.
   *
   * @return \GraphQL\Deferred
   *   A promise that returns an array of metatags for the given entity.
   */
  public function resolve(EntityInterface $entity, RefinableCacheableDependencyInterface $metadata) {
    $resolver = $this->entityBuffer->add($entity->getEntityTypeId(), $entity->id());

    return new Deferred(function () use ($resolver, $metadata) {
      if (!$entity = $resolver()) {
        // If there is no entity with this id, add the list cache tags so that
        // the cache entry is purged whenever a new entity of this type is
        // saved.
        $type = $this->entityTypeManager->getDefinition('node');
        /** @var \Drupal\Core\Entity\EntityTypeInterface $type */
        $tags = $type->getListCacheTags();
        $metadata->addCacheTags($tags);
        return NULL;
      }

      if (!$entity instanceof ContentEntityInterface) {
        $metadata->addCacheableDependency($entity);
        return NULL;
      }

      $metatags = [];

      $tags = $this->metatagManager->tagsFromEntityWithDefaults($entity);

      // Avoid render error from using generateRawElements function.
      $context = new RenderContext();
      $elements = $this->renderer->executeInRenderContext($context, function () use ($tags, $entity) {
        return $this->metatagManager->generateRawElements($tags, $entity);
      });

      foreach ($elements as $element) {
        $metatag = [];

        // Key.
        if (isset($element['#tag']) && $element['#tag'] === 'meta') {
          $metatag['meta'] = isset($element['#attributes']['property']) ? $element['#attributes']['property'] : $element['#attributes']['name'];
        }
        elseif (isset($element['#tag']) && $element['#tag'] === 'link') {
          $metatag['meta'] = $element['#attributes']['rel'];
        }

        // Value.
        if (isset($element['#tag']) && $element['#tag'] === 'meta') {
          $metatag['value'] = $element['#attributes']['content'];
        }
        elseif (isset($element['#tag']) && $element['#tag'] === 'link') {
          $metatag['value'] = $element['#attributes']['href'];
        }

        // Add metatag to list to return.
        $metatags[] = $metatag;
      }

      return $metatags;
    });
  }

}
