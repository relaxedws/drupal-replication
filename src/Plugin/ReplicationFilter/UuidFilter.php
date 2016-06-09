<?php

namespace Drupal\replication\Plugin\ReplicationFilter;

use Drupal\Core\Entity\EntityInterface;
use Drupal\replication\Plugin\ReplicationFilter\ReplicationFilterBase;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Provides filtering by UUID.
 *
 * Note: if the entity a UUID refers to references another entity, that
 * referenced entity's UUID must also be included in order to maintain data
 * integrity.
 *
 * @ReplicationFilter(
 *   id = "uuid",
 *   label = @Translation("Filter UUIDs"),
 *   description = @Translation("Replicate only entities in the set of UUIDs.")
 * )
 */
class UuidFilter extends ReplicationFilterBase {

  /**
   * {@inheritdoc}
   */
  public function filter(EntityInterface $entity, ParameterBag $parameters) {
    $uuids = $parameters->get('uuids') ?: '';
    $uuids = explode(',', $uuids);
    $uuids = array_map('trim', $uuids);
    return in_array($entity->uuid(), $uuids);
  }

}
