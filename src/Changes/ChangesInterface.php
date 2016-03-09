<?php

/**
 * @file
 * Contains \Drupal\replication\Changes\ChangesInterface.
 */

namespace Drupal\replication\Changes;

use Drupal\multiversion\Entity\Index\SequenceIndexInterface;
use Drupal\multiversion\Entity\WorkspaceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

interface ChangesInterface {

  /**
   * Factory method.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param \Drupal\multiversion\Entity\Index\SequenceIndexInterface $sequence_index
   * @param \Drupal\multiversion\Entity\WorkspaceInterface
   * @return \Drupal\replication\Changes\ChangesInterface
   */
  static public function createInstance(ContainerInterface $container, SequenceIndexInterface $sequence_index, WorkspaceInterface $workspace);

  /**
   * @param boolean $include_docs
   * @return \Drupal\replication\Changes\ChangesInterface
   */
  public function includeDocs($include_docs);

  /**
   * Sets from what sequence number to check for changes.
   *
   * @param int $seq
   * @return \Drupal\replication\Changes\ChangesInterface
   */
  public function lastSeq($seq);

  /**
   * Return the changes in a 'normal' way.
   */
  public function getNormal();

  /**
   * Return the changes with a 'longpoll'.
   *
   * We can implement this method later.
   *
   * @see https://www.drupal.org/node/2282295
   */
  public function getLongpoll();

}
