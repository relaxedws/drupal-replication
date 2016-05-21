<?php

namespace Drupal\replication\Plugin\ReplicationFilter;

use Drupal\Core\Entity\EntityInterface;
use Drupal\replication\Plugin\ReplicationFilter\ReplicationFilterBase;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Provides a filter for published entities.
 *
 * @ReplicationFilter(
 *   id = "published",
 *   label = @Translation("Filter Published Entities"),
 *   description = @Translation("Replicate only entities that are published.")
 * )
 */
class PublishedFilter extends ReplicationFilterBase {

  /**
   * {@inheritdoc}
   */
  public function filter(EntityInterface $entity = NULL, ParameterBag $parameters) {
    // @todo If entity is NULL, include it?
    return NULL == $entity || NODE_PUBLISHED == $entity->status;
  }

}
