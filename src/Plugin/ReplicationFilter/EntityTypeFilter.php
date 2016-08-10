<?php

namespace Drupal\replication\Plugin\ReplicationFilter;

use Drupal\Core\Entity\EntityInterface;
use Drupal\replication\Plugin\ReplicationFilter\ReplicationFilterBase;

/**
 * Provides a filter based on entity type.
 *
 * Supported configuration:
 *   entity_type_id: a comma delimited list of entity type id's to include
 *   bundle: a comma delimited list of bundles matching the type ids
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
  public function filter(EntityInterface $entity) {
    $entity_type_ids = $this->getDelimitedConfigurationValue(',', 'entity_type_id');
    $bundles = $this->getDelimitedConfigurationValue(',', 'bundle');

    // Ensure length of entity_type_ids and bundles are equal.
    if (count($entity_type_ids) != count($bundles)) {
      return FALSE;
    }

    $entity_type_id = $entity->getEntityTypeId();
    $bundle = $entity->bundle();

    for ($i = 0; $i < count($entity_type_ids); $i++) {
      if ($entity_type_ids[$i] == $entity_type_id && $bundles[$i] == $bundle) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
