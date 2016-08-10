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
   * Parse a configuration as comma delimited values.
   *
   * @param string $configuration_key
   *   The key of the configuration to get the values for.
   *
   * @return array
   *   The configuration parsed into an array of values.
   */
  protected function parseConfigurationValues($configuration_key) {
    $values = (isset($this->configuration[$configuration_key])) ? $this->configuration[$configuration_key] : '';
    $values = explode(',', $values);
    $values = array_filter(array_map('trim', $values));
    return $values;
  }

}
