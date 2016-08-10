<?php

namespace Drupal\replication\Plugin\ReplicationFilter;

use Drupal\Core\Entity\EntityInterface;
use Drupal\replication\Plugin\ReplicationFilter\ReplicationFilterBase;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Provides a filter based on entity type.
 *
 * Supported parameters:
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
  public function filter(EntityInterface $entity, ParameterBag $parameters) {
    $entity_type_ids = $this->parseParameterValues($parameters, 'entity_type_id');
    $bundles = $this->parseParameterValues($parameters, 'bundle');

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

  /**
   * Parse a parameter's comma delimiated values.
   *
   * @param string $parameter_name
   *   The name of the parameter to get the values for.
   *
   * @return array
   *   The parsed parameter values.
   */
  protected function parseParameterValues(ParameterBag $parameters, $parameter_name) {
    if ($parameters->has($parameter_name)) {
      $values = $parameters->get($parameter_name);
    }
    else {
      $values = '';
    }
    $values = explode(',', $values);
    $values = array_filter(array_map('trim', $values));
    return $values;
  }

}
