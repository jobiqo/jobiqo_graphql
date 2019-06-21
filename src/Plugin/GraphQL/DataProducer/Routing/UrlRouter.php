<?php

namespace Drupal\jobiqo_graphql\Plugin\GraphQL\DataProducer\Routing;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\graphql\GraphQL\Buffers\EntityBuffer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\jobiqo_graphql\Wrappers\Response\ContentResponse;
use Drupal\jobiqo_graphql\Wrappers\Response\ErrorResponse;
use Drupal\jobiqo_graphql\Wrappers\Response\RedirectResponse;
use GraphQL\Deferred;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Generates the content response for given url if that url represents content.
 *
 * @DataProducer(
 *   id = "url_router",
 *   name = @Translation("Url router"),
 *   description = @Translation("Returns a response corresponding to the resolved url."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Response")
 *   ),
 *   consumes = {
 *     "path" = @ContextDefinition("string",
 *       label = @Translation("Path")
 *     ),
 *     "url" = @ContextDefinition("url",
 *       label = @Translation("Url"),
 *       required = FALSE
 *     )
 *   }
 * )
 */
class UrlRouter extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * List of node bundles to get the route for.
   *
   * @var string[]
   */
  protected $bundles = [
    'job_per_template',
    'job_per_link',
    'job_per_file',
    'landing_page',
  ];

  /**
   * The entity buffer service.
   *
   * @var \Drupal\graphql\GraphQL\Buffers\EntityBuffer
   */
  protected $entityBuffer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('graphql.buffer.entity')
    );
  }

  /**
   * UrlRouter constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\graphql\GraphQL\Buffers\EntityBuffer $entityBuffer
   *   The entity buffer service.
   */
  public function __construct(array $configuration, string $plugin_id, $plugin_definition, EntityBuffer $entityBuffer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityBuffer = $entityBuffer;
  }

  /**
   * Resolves the response for given url.
   *
   * @param string $path
   *   The path.
   * @param \Drupal\Core\Url $url
   *   Url object.
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *   Refinable cacheability metadata.
   *
   * @return \Drupal\jobiqo_graphql\Wrappers\ResponseInterface
   *   Either an error or a content response.
   */
  public function resolve(string $path, Url $url = NULL, RefinableCacheableDependencyInterface $metadata) {
    if (!$url) {
      return new ErrorResponse('Page not found.', 404);
    }
    $generated = $url->toString(TRUE);
    $metadata->addCacheableDependency($generated);
    $target = $generated->getGeneratedUrl();

    if ($target !== $path) {
      return new RedirectResponse($target, 301);
    }

    $name = $url->getRouteName();
    $parameters = $url->getRouteParameters();
    if ($name === 'entity.node.canonical') {
      $nid = $parameters['node'];
      $resolve = $this->entityBuffer->add('node', $nid);
      return new Deferred(function () use ($resolve, $metadata) {
        /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
        $entity = $resolve();
        $bundle = $entity->bundle();

        if (!in_array($bundle, $this->bundles)) {
          return new ErrorResponse('Page not found.', 404);
        }

        $access = $entity->access('view', NULL, TRUE);
        $metadata->addCacheableDependency($access);
        if (!$access->isAllowed()) {
          return new ErrorResponse('Access restricted.', 403);
        }

        switch ($bundle) {
          case 'landing_page':
            return new ContentResponse($entity);

          case in_array($bundle, $this->bundles):
            return new ContentResponse($entity);
        }
      });
    }

    return new ErrorResponse('Page not found.', 404);
  }

}
