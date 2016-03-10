<?php

/**
 * @file
 * Contains \Drupal\replication\RevisionDiff\RevisionDiffInterface.
 */

namespace Drupal\replication\RevisionDiff;

use Drupal\multiversion\Entity\Index\RevisionIndexInterface;
use Drupal\multiversion\Entity\WorkspaceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

interface RevisionDiffInterface {

  /**
   * @param array $revision_ids
   */
  public function setRevisionIds(array $revision_ids);

  /**
   * @return array
   */
  public function getRevisionIds();

  /**
   * Returns missing revisions ids.
   * @return array
   */
  public function getMissing();

}
