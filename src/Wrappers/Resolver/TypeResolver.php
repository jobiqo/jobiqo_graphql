<?php

namespace Drupal\jobiqo_graphql\Wrappers\Resolver;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\jobiqo_auth\Wrappers\Response\UserLoginResponse;
use Drupal\jobiqo_auth\Wrappers\Response\UserRegisterResponse;
use Drupal\jobiqo_graphql\Wrappers\Response\ContentResponse;
use Drupal\jobiqo_graphql\Wrappers\Response\ErrorResponse;
use Drupal\jobiqo_graphql\Wrappers\Response\RedirectResponse;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Contains the methods to resolve the specific types.
 */
class TypeResolver {

  /**
   * Prettifies the machine name by removing underscores and capitalizing words.
   *
   * Eg. "machine_name_example" is converted to "MachineNameExample". This is
   * used for automatic conversion of some entity types to their respective
   * GraphQL types.
   *
   * @param string $machine_name
   *   The machine name.
   *
   * @return string
   *   Prettified machine name.
   */
  protected static function prettifyMachineName(string $machine_name) {
    return str_replace('_', '', ucwords($machine_name, '_'));
  }

  /**
   * Resolves the GraphQL type for given content entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   Entity.
   *
   * @return string
   *   Resolved GraphQL type for content entity.
   */
  protected static function resolveContentType(ContentEntityInterface $entity) {
    $entity_type = $entity->getEntityTypeId();
    switch ($entity_type) {
      case 'node':
        // For job bundles just return Job.
        if (strpos($entity->bundle(), 'job_') === 0) {
          return 'Job';
        }
        break;
    }
    // For everything else just use prettified bundle.
    return self::prettifyMachineName($entity->bundle());
  }

  /**
   * Resolves the response type.
   *
   * @param mixed $object
   *   Response object.
   *
   * @return string
   *   Content response type.
   *
   * @throws \Exception
   *   Invalid response type.
   */
  public static function resolveResponse($object) {
    switch (TRUE) {
      case $object instanceof RedirectResponse:
        return 'RedirectResponse';

      case $object instanceof ContentResponse:
        return 'ContentResponse';

      case $object instanceof ErrorResponse:
        return 'ErrorResponse';

      case $object instanceof UserRegisterResponse:
        return 'UserRegisterResponse';

      case $object instanceof UserLoginResponse:
        return 'UserLoginResponse';
    }

    throw new \Exception('Invalid response type.');
  }

  /**
   * Resolves the content type.
   *
   * @param mixed $object
   *   Content object.
   *
   * @return string
   *   Resolved content type.
   *
   * @throws \Exception
   *   Invalid content type.
   */
  public static function resolveContent($object) {
    if ($object instanceof ContentEntityInterface) {
      // For content just use bundle and turn to camel case.
      return self::resolveContentType($object);
    }
    throw new \Exception('Invalid content type.');
  }

  /**
   * Resolves the paragraph type.
   *
   * @param mixed $object
   *   Paragraph object.
   *
   * @return string
   *   Resolved paragraph type.
   *
   * @throws \Exception
   *   Invalid paragraph type.
   */
  public static function resolveParagraph($object) {
    // For paragraphs just use bundle and turn to camel case with "Paragraph"
    // suffix. Eg call_to_action_banner becomes CallToActionBannerParagraph.
    if ($object instanceof ParagraphInterface) {
      /** @var \Drupal\paragraphs\ParagraphInterface $object */
      return self::prettifyMachineName($object->bundle()) . 'Paragraph';
    }
    throw new \Exception('Invalid paragraph type.');
  }

}
