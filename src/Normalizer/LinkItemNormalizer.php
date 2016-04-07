<?php

/**
 * @file
 * Contains \Drupal\replication\Normalizer\LinkItemNormalizer.
 */

namespace Drupal\replication\Normalizer;

use Drupal\serialization\Normalizer\NormalizerBase;

class LinkItemNormalizer extends NormalizerBase {

  /**
   * The interface or class that this Normalizer supports.
   *
   * @var string
   */
  protected $supportedInterfaceOrClass = 'Drupal\link\LinkItemInterface';

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []) {
    $attributes = [];
    foreach ($object->getProperties(TRUE) as $name => $field) {
      $attributes[$name] = $this->serializer->normalize($field, $format, $context);
    }

    // Add the 'entity_type_id' and 'target_uuid' values if the uri has the
    // 'entity' scheme. These entities will be used later to denormalize this
    // field and set the uri to the correct entity.
    if (isset($attributes['uri'])) {
      $scheme = parse_url($attributes['uri'], PHP_URL_SCHEME);
      if ($scheme === 'entity') {
        list($entity_type, $entity_id) = explode('/', substr($attributes['uri'], 7), 2);
        $entity_manager = \Drupal::entityTypeManager();
        if ($entity = $entity_manager->getStorage($entity_type)->load($entity_id)) {
          $attributes['entity_type_id'] = $entity_type;
          $attributes['target_uuid'] = $entity->uuid();
        }
      }
    }
    return $attributes;
  }

}
