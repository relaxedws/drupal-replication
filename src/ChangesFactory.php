<?php

namespace Drupal\replication;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\multiversion\Entity\Index\SequenceIndexInterface;
use Drupal\multiversion\Entity\WorkspaceInterface;
use Drupal\replication\Changes\Changes;
use Symfony\Component\Serializer\SerializerInterface;

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
   * @var \Symfony\Component\Serializer\SerializerInterface
   */
  protected $serializer;

  /**
   * @var \Drupal\replication\Changes\Changes[]
   */
  protected $instances = [];

  /**
   * @param \Drupal\multiversion\Entity\Index\SequenceIndexInterface $sequence_index
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Symfony\Component\Serializer\SerializerInterface $serializer
   */
  public function __construct(SequenceIndexInterface $sequence_index, EntityTypeManagerInterface $entity_type_manager, SerializerInterface $serializer) {
    $this->sequenceIndex = $sequence_index;
    $this->entityTypeManager = $entity_type_manager;
    $this->serializer = $serializer;
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
        $this->serializer
      );
    }
    return $this->instances[$workspace->id()];
  }

}
