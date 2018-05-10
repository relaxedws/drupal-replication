<?php

namespace Drupal\replication\Event;

use Drupal\multiversion\Entity\WorkspaceInterface;
use Symfony\Component\EventDispatcher\Event;

class ReplicationEvent extends Event {

  /**
   * The source workspace.
   *
   * @var \Drupal\multiversion\Entity\WorkspaceInterface
   */
  protected $sourceWorkspace;

  /**
   * The target workspace.
   *
   * @var \Drupal\multiversion\Entity\WorkspaceInterface
   */
  protected $targetWorkspace;

  /**
   * The replication status.
   *
   * @var bool
   */
  protected $status;

  /**
   * ReplicationEvent constructor.
   *
   * @param \Drupal\multiversion\Entity\WorkspaceInterface $source
   *   The source workspace.
   * @param \Drupal\multiversion\Entity\WorkspaceInterface $target
   *   The target workspace.
   */
  public function __construct(WorkspaceInterface $source, WorkspaceInterface $target) {
    $this->sourceWorkspace = $source;
    $this->targetWorkspace = $target;
  }

  /**
   * Returns the source workspace.
   *
   * @return \Drupal\multiversion\Entity\WorkspaceInterface
   */
  public function getSourceWorkspace() {
    return $this->sourceWorkspace;
  }

  /**
   * Returns the target workspace.
   *
   * @return \Drupal\multiversion\Entity\WorkspaceInterface
   */
  public function getTargetWorkspace() {
    return $this->targetWorkspace;
  }

  /**
   * Indicates whether the replication succeeded.
   *
   * @return bool
   */
  public function succeeded() {
    return $this->getStatus() === TRUE;
  }

  /**
   * Whether the replication failed.
   *
   * @return bool
   */
  public function failed() {
    return $this->getStatus() === FALSE;
  }

  /**
   * Returns the replication status.
   *
   * This will be TRUE on success, FALSE on failure, or NULL if the status is
   * indeterminate (i.e., the replication is in progress).
   *
   * @return bool|null
   */
  public function getStatus() {
    return $this->status;
  }

  /**
   * Sets the replication status.
   *
   * @param bool $status
   *   The replication status. TRUE for success, FALSE for failure.
   */
  public function setStatus($status) {
    $this->status = (bool) $status;
  }

}
