<?php

namespace Drupal\replication\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Defines a replication filter.
 *
 * Replication filters are used to filter out entities from a changeset during
 * replication.
 */
interface ReplicationFilterInterface extends PluginInspectionInterface {

  /**
   * Get the label for the filter.
   *
   * @return string
   */
  public function getLabel();

  /**
   * Get the description of what the filter does.
   *
   * @return string
   */
  public function getDescription();

  /**
   * Filter the given entity.
   *
   * @param EntityInterface $entity
   *   The entity to filter.
   * @param ParameterBag $parameters
   *   The parameters passed to the filter function.
   *
   * @return bool
   *   Return TRUE if it should be included, else FALSE.
   */
  public function filter(EntityInterface $entity, ParameterBag $parameters);

}
