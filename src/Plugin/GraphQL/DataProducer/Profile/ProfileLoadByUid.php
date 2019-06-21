<?php

namespace Drupal\jobiqo_graphql\Plugin\GraphQL\DataProducer\Profile;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Retrieves the profile for particular user ID.
 *
 * @DataProducer(
 *   id = "profile_load_by_uid",
 *   name = @Translation("Load a profile by its user"),
 *   description = @Translation("Loads a profile entity by a user id."),
 *   produces = @ContextDefinition("profile",
 *     label = @Translation("Profile")
 *   ),
 *   consumes = {
 *     "uid" = @ContextDefinition("int",
 *       label = @Translation("User id")
 *     ),
 *     "bundles" = @ContextDefinition("array",
 *       label = @Translation("Entity bundle(s)")
 *     )
 *   }
 * )
 */
class ProfileLoadByUid extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

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
   * @param string $pluginId
   *   The plugin id.
   * @param array $pluginDefinition
   *   The plugin definition array.
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The entity type manager service.
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    array $configuration,
    string $pluginId,
    array $pluginDefinition,
    EntityTypeManager $entityTypeManager
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Resolves the profile for given user ID.
   *
   * @param int $uid
   *   The user id.
   * @param array $bundles
   *   The list of profile bundles to load.
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *   The metadata object for caching.
   *
   * @return \GraphQL\Deferred
   *   The loaded profile entity.
   *
   * @throws \Exception
   *   Any caught exception.
   */
  public function resolve(int $uid, array $bundles, RefinableCacheableDependencyInterface $metadata) {
    $profile = NULL;
    $profiles_loaded = $this->entityTypeManager->getStorage('profile')
      ->loadByProperties(['uid' => $uid, 'type' => $bundles]);

    if (count($profiles_loaded) > 0) {
      $profile = array_values($profiles_loaded)[0];
    }

    if (!$profile) {
      $type = $this->entityTypeManager->getDefinition('profile');
      /** @var \Drupal\Core\Entity\EntityTypeInterface $type */
      $tags = $type->getListCacheTags();
      $metadata->addCacheTags($tags);
      return NULL;
    }

    if (isset($bundles) && !in_array($profile->bundle(), $bundles)) {
      // If the entity is not among the allowed bundles, don't return it.
      $metadata->addCacheableDependency($profile);
      return NULL;
    }

    $access = $profile->access('view', NULL, TRUE);
    if (!$access->isAllowed()) {
      // Do not return the entity if access is denied.
      $metadata->addCacheableDependency($profile);
      $metadata->addCacheableDependency($access);
      return NULL;
    }

    return $profile;
  }

}
