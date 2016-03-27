<?php

namespace Drupal\replication;

use Drupal\multiversion\Entity\WorkspaceInterface;

interface BulkDocsFactoryInterface {

  /**
   * Constructs a new BulkDocs instance.
   *
   * @param \Drupal\multiversion\Entity\WorkspaceInterface $workspace
   *
   * @return \Drupal\replication\RevisionDiff\RevisionDiff
   */
  public function get(WorkspaceInterface $workspace);

}
