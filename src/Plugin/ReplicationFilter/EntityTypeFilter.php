<?php

namespace Drupal\replication\Plugin\ReplicationFilter;

use Drupal\Core\Entity\EntityInterface;
use Drupal\replication\Plugin\ReplicationFilter\ReplicationFilterBase;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Provides a filter based on entity type.
 *
 * Supported parameters:
 *   entity_type: a comma delimited list of entity type id's to include
 *
 * @ReplicationFilter(
 *   id = "entity_type",
 *   label = @Translation("Filter By Entity Type"),
 *   description = @Translation("Replicate only entities that match a given type.")
 * )
 */
class EntityTypeFilter extends ReplicationFilterBase {

  /**
   * {@inheritdoc}
   */
  public function filter(EntityInterface $entity, ParameterBag $parameters) {
    if ($parameters->has('entity_type')) {
      $types = $parameters->get('entity_type');
    } else {
      $types = '';
    }
    $types = explode(',', $types);
    $types = array_filter(array_map('trim', $types));
    return in_array($entity->getEntityTypeId(), $types);
  }

}
