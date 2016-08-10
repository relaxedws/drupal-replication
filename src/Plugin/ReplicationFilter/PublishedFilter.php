<?php

namespace Drupal\replication\Plugin\ReplicationFilter;

use Drupal\Core\Entity\EntityInterface;
use Drupal\node\NodeInterface;
use Drupal\replication\Plugin\ReplicationFilter\ReplicationFilterBase;

/**
 * Provides a filter for published entities.
 *
 * Note: this filter will only work with NodeTypes.
 *
 * @ReplicationFilter(
 *   id = "published",
 *   label = @Translation("Filter Published Nodes"),
 *   description = @Translation("Replicate only nodes that are published.")
 * )
 */
class PublishedFilter extends ReplicationFilterBase {

  /**
   * {@inheritdoc}
   */
  public function filter(EntityInterface $entity) {
    if (!$entity instanceof NodeInterface) {
      return FALSE;
    }
    return $entity->isPublished();
  }

}
