<?php

namespace Drupal\replication;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\multiversion\Entity\Index\SequenceIndexInterface;
use Drupal\multiversion\Entity\WorkspaceInterface;
use Drupal\replication\Changes\Changes;
use Drupal\replication\Plugin\ReplicationFilterManagerInterface;

class ChangesFactory implements ChangesFactoryInterface {

  /**
   * @var \Drupal\multiversion\Entity\Index\SequenceIndexInterface
   */
  protected $sequenceIndex;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\replication\Plugin\ReplicationFilterManagerInterface
   */
  protected $filterManager;

  /**
   * @var \Drupal\replication\Changes\Changes[]
   */
  protected $instances = [];

  /**
   * @param \Drupal\multiversion\Entity\Index\SequenceIndexInterface $sequence_index
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\replication\Plugin\ReplicationFilterManagerInterface $filter_manager
   */
  public function __construct(SequenceIndexInterface $sequence_index, EntityTypeManagerInterface $entity_type_manager, ReplicationFilterManagerInterface $filter_manager) {
    $this->sequenceIndex = $sequence_index;
    $this->entityTypeManager = $entity_type_manager;
    $this->filterManager = $filter_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function get(WorkspaceInterface $workspace) {
    if (!isset($this->instances[$workspace->id()])) {
      $this->instances[$workspace->id()] = new Changes(
        $this->sequenceIndex,
        $workspace,
        $this->entityTypeManager,
        $this->filterManager
      );
    }
    return $this->instances[$workspace->id()];
  }

}
