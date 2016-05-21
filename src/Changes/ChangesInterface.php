<?php

/**
 * @file
 * Contains \Drupal\replication\Changes\ChangesInterface.
 */

namespace Drupal\replication\Changes;

use Drupal\multiversion\Entity\Index\SequenceIndexInterface;
use Drupal\multiversion\Entity\WorkspaceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

interface ChangesInterface {

  /**
   * Filter out changes only for the given UUIDs.
   *
   * Note: if the entity a UUID refers to references another entity, that
   * referenced entity's UUID must also be included in order to maintain data
   * integrity.
   *
   * @param string[string] $uuids
   *   The UUIDs to include in the change set.
   *
   * @return \Drupal\replication\Changes\ChangesInterface
   */
  public function uuids(array $uuids);

  /**
   * Set the id of the filter plugin to use to refine the changeset.
   *
   * @param string $filter_name
   *   The plugin id of a Drupal\replication\Plugin\ReplicationFilterInterface.
   *
   * @return \Drupal\replication\Changes\ChangesInterface
   */
  public function filter($filter_name);

  /**
   * Set the parameters for the filter plugin.
   *
   * @param ParameterBag $parameters
   *   The parameters passed to the filter function.
   *
   * @return ReplicationTaskInterface
   */
  public function parameters(ParameterBag $parameters);

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
