<?php

namespace Drupal\replication\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

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
   * @param EntityInterface|NULL $entity
   *   The entity to filter, else NULL if the entity was not found.
   *
   * @param ParameterBag $parameters
   *   The parameters passed to the filter function.
   *
   * @return bool
   *   Return TRUE if it should be included, else FALSE.
   */
  public function filter(EntityInterface $entity = NULL, ParameterBag $parameters);

}
