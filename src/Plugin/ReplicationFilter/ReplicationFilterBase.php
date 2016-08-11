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

  /**
   * {@inheritdoc}
   *
   * For replication filters, the plugin configuration contains what would be
   * passed as "query_params" to a CouchDB filter function.
   */
  public function getConfiguration() {
    if ($this->configuration == NULL) {
      return $this->defaultConfiguration();
    }
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return array();
  }

}
