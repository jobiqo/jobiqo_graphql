<?php

namespace Drupal\jobiqo_graphql\Plugin\GraphQL\Schema;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\ResolverRegistry;
use Drupal\graphql\Plugin\GraphQL\Schema\SdlExtendedSchemaPluginBase;
use Drupal\graphql\Plugin\ResolverMapPluginManager;
use Drupal\jobiqo_graphql\Discovery\GqlExtendedDiscovery;
use GraphQL\Type\Definition\ResolveInfo;
use Drupal\jobiqo_graphql\Discovery\GqlDiscovery;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Jobiqo GraphQL schema.
 *
 * @Schema(
 *   id = "jobiqo",
 *   name = "Jobiqo schema"
 * )
 */
class SdlSchemaJobiqo extends SdlExtendedSchemaPluginBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * GraphQL resolver registry map manager.
   *
   * @var \Drupal\graphql\Plugin\ResolverMapPluginManager
   */
  protected $registryMapManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, string $plugin_id, array $plugin_definition, CacheBackendInterface $astCache, $config, ModuleHandlerInterface $module_handler, ResolverMapPluginManager $registry_map_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $astCache, $config);
    $this->moduleHandler = $module_handler;
    $this->registryMapManager = $registry_map_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('cache.graphql.ast'),
      $container->getParameter('graphql.config'),
      $container->get('module_handler'),
      $container->get('plugin.manager.graphql.resolver_map')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getSchemaDefinition() {
    // Build our schema from all modules that provide a MODULE_NAME.gql file.
    $discovery = new GqlDiscovery($this->moduleHandler->getModuleDirectories());
    $schema_parts = $discovery->findAll();
    return implode("\n", $schema_parts);
  }

  /**
   * {@inheritdoc}
   */
  protected function getExtendedSchemaDefinition() {
    // Build our extended schema from all modules that provide a
    // MODULE_NAME.extend.gql file.
    $discovery = new GqlExtendedDiscovery($this->moduleHandler->getModuleDirectories());
    $schema_parts = $discovery->findAll();
    return implode("\n", $schema_parts);
  }

  /**
   * {@inheritdoc}
   */
  protected function getResolverRegistry() {
    $registry = new ResolverRegistry([], [
      __CLASS__,
      'defaultFieldResolver',
    ]);
    return $this->registryMapManager->registerResolvers($this->getPluginId(), $registry);
  }

  /**
   * The default field resolver.
   *
   * Used if no field resolver was explicitly registered.
   *
   * @param mixed $source
   *   The source (parent) value.
   * @param array $args
   *   An array of arguments.
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   *   The context object.
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *   The resolve info object.
   *
   * @return mixed
   *   The result for the field.
   */
  public static function defaultFieldResolver($source, array $args, ResolveContext $context, ResolveInfo $info) {
    $fieldName = $info->fieldName;
    $property = NULL;

    if (is_array($source) || $source instanceof \ArrayAccess) {
      if (isset($source[$fieldName])) {
        $property = $source[$fieldName];
      }
    }
    else {
      if (is_object($source) && isset($source->{$fieldName})) {
        $property = $source->{$fieldName};
      }
      // Allow methods on wrapper objects with the same name to be used as
      // callbacks to resolve the field value.
      else {
        if (is_callable([$source, $fieldName])) {
          $property = [$source, $fieldName];
        }
      }
    }

    if (is_callable($property)) {
      return $property($source, $args, $context, $info);
    }

    return $property;
  }

}
