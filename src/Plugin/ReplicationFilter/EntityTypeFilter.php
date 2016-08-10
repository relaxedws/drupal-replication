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
    $type_ids = $this->parseParameterValues($parameters, 'entity_type_id');
    $bundles = $this->parseParameterValues($parameters, 'bundle');
    return in_array($entity->getEntityTypeId(), $type_ids) && in_array($entity->bundle(), $bundles);
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
