<?php

namespace Drupal\replication\Changes;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\multiversion\Entity\Index\SequenceIndexInterface;
use Drupal\multiversion\Entity\WorkspaceInterface;
use Drupal\replication\Plugin\ReplicationFilterManagerInterface;
// @todo where is the ParameterBagInterface???
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * {@inheritdoc}
 */
class Changes implements ChangesInterface {
  use DependencySerializationTrait;

  /**
   * @var string
   *   The workspace to generate the changeset from.
   */
  protected $workspaceId;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Symfony\Component\Serializer\SerializerInterface
   */
  protected $serializer;

  /**
   * @var \Drupal\replication\Plugin\ReplicationFilterManagerInterface
   */
  protected $filterManager;

  /**
   * @var string[string]
   *   The UUIDs to include in the changeset.
   */
  protected $uuids = [];

  /**
   * @var string
   *   The id of the filter plugin to use during replication.
   */
  protected $filterName;

  /**
   * @var ParameterBag
   *   The parameters passed to the filter function.
   */
  protected $parameters;

  /**
   * @var boolean
   *   Whether to include entities in the changeset.
   */
  protected $includeDocs = FALSE;

  /**
   * @var int
   *   The sequence ID to start including changes from. Result includes $lastSeq.
   */
  protected $lastSeq = 0;

  /**
   * @param \Drupal\multiversion\Entity\Index\SequenceIndexInterface $sequence_index
   * @param \Drupal\multiversion\Entity\WorkspaceInterface $workspace
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Symfony\Component\Serializer\SerializerInterface $serializer
   * @param \Drupal\replication\Plugin\ReplicationFilterManagerInterface $filter_manager
   */
  public function __construct(SequenceIndexInterface $sequence_index, WorkspaceInterface $workspace, EntityTypeManagerInterface $entity_type_manager, SerializerInterface $serializer, ReplicationFilterManagerInterface $filter_manager) {
    $this->sequenceIndex = $sequence_index;
    $this->workspaceId = $workspace->id();
    $this->entityTypeManager = $entity_type_manager;
    $this->serializer = $serializer;
    $this->filterManager = $filter_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function uuids(array $uuids) {
    $this->uuids = $uuids;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function filter($filter_name) {
    $this->filterName = $filter_name;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function parameters(ParameterBag $parameters) {
    $this->parameters = $parameters;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function includeDocs($include_docs) {
    $this->includeDocs = $include_docs;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function lastSeq($seq) {
    $this->lastSeq = $seq;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getNormal() {
    $sequences = $this->sequenceIndex
      ->useWorkspace($this->workspaceId)
      ->getRange($this->lastSeq, NULL);

    // Setup filter plugin.
    $filter = NULL;
    if (isset($this->filterName)) {
      $filter = $this->filterManager->createInstance($this->filterName);
    }
    $parameters = isset($this->parameters) ? $this->parameters : new ParameterBag();

    // Format the result array.
    $changes = array();
    foreach ($sequences as $sequence) {
      if (!empty($sequence['local']) || !empty($sequence['is_stub'])) {
        continue;
      }

      // Filter by UUID.
      if (!empty($this->uuids) && !in_array($sequence['entity_uuid'], $this->uuids)) {
        continue;
      }

      // Get the document.
      $revision = NULL;
      if ($this->includeDocs == TRUE || $filter !== NULL) {
        /** @var \Drupal\multiversion\Entity\Storage\ContentEntityStorageInterface $storage */
        $storage = $this->entityTypeManager->getStorage($sequence['entity_type_id']);
        $storage->useWorkspace($this->workspaceId);
        $revision = $storage->loadRevision($sequence['revision_id']);
      }

      // Filter the document.
      if ($filter !== NULL && !$filter->filter($revision, $parameters)) {
        continue;
      }

      $uuid = $sequence['entity_uuid'];
      $changes[$uuid] = array(
        'changes' => array(
          array('rev' => $sequence['rev']),
        ),
        'id' => $uuid,
        'seq' => $sequence['seq'],
      );
      if ($sequence['deleted']) {
        $changes[$uuid]['deleted'] = TRUE;
      }

      // Include the document.
      if ($this->includeDocs == TRUE) {
        $changes[$uuid]['doc'] = $this->serializer->normalize($revision);
      }
    }

    // Now when we have rebuilt the result array we need to ensure that the
    // results array is still sorted on the sequence key, as in the index.
    $return = array_values($changes);
    usort($return, function($a, $b) {
      return $a['seq'] - $b['seq'];
    });

    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function getLongpoll() {
    $no_change = TRUE;
    do {
      $change = $this->sequenceIndex
        ->useWorkspace($this->workspaceId)
        ->getRange($this->lastSeq, NULL);
      $no_change = empty($change) ? TRUE : FALSE;
    } while ($no_change);
    return $change;
  }

}
