<?php

namespace Drupal\replication\Plugin\ReplicationFilter;

use Drupal\Core\Plugin\PluginBase;
use Drupal\replication\Plugin\ReplicationFilterInterface;

/**
 * Provides a base class for replication filters.
 *
 * Having a base class provides a central point to change the behavior of
 * replication filters, such as adding contexts.
 */
abstract class ReplicationFilterBase extends PluginBase implements ReplicationFilterInterface {

  /**
   * @var string
   */
  protected $label;

  /**
   * @var string
   */
  protected $description;

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

}
