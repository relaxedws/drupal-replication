<?php

namespace Drupal\replication;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\multiversion\Entity\Index\SequenceIndexInterface;
use Drupal\multiversion\Entity\WorkspaceInterface;
use Drupal\replication\Changes\Changes;
use Symfony\Component\Serializer\SerializerInterface;

class ChangesFactory implements ChangesFactoryInterface {

  /** @var SequenceIndexInterface  */
  protected $sequenceIndex;

  /** @var  EntityTypeManagerInterface */
  protected $entityTypeManager;

  /** @var  SerializerInterface */
  protected $serializer;

  /** @var Changes []  */
  protected $changes = [];

  function __construct(SequenceIndexInterface $sequence_index, EntityTypeManagerInterface $entity_type_manager, SerializerInterface $serializer) {
    $this->sequenceIndex = $sequence_index;
    $this->entityTypeManager = $entity_type_manager;
    $this->serializer = $serializer;
  }

  /**
   * {@inheritdoc}
   */
  public function get(WorkspaceInterface $workspace) {
    if (!isset($this->changes[$workspace->id()])) {
      $this->changes[$workspace->id()] = new Changes(
        $this->sequenceIndex,
        $workspace,
        $this->entityTypeManager,
        $this->serializer
      );
    }
    return $this->changes[$workspace->id()];
  }

}