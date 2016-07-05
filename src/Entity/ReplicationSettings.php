<?php

namespace Drupal\replication\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\replication\Entity\ReplicationSettingsInterface;

/**
 * Defines the replication settings entity.
 *
 * The replication settings are attached to a Workspace to define how that
 * Workspace should be replicated.
 *
 * @ConfigEntityType(
 *   id = "replication_settings",
 *   label = @Translation("Replication settings"),
 *   config_prefix = "replication_settings",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "filter_id" = "filter_id"
 *   }
 * )
 */
class ReplicationSettings extends ConfigEntityBase implements ReplicationSettingsInterface {

  /**
   * An identifier for this replication settings.
   *
   * @var string
   */
  protected $id;

  /**
   * The human readable name for this replication settings.
   *
   * @var string
   */
  protected $label;

  /**
   * The plugin ID of a replication filter.
   *
   * @var string
   */
  protected $filter_id;

  /**
   * {@inheritdoc}
   */
  public function getFilterId() {
    return $this->filter_id;
  }
}
