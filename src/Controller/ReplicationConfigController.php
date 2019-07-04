<?php

namespace Drupal\replication\Controller;

use Drupal\Core\Controller\ControllerBase;

class ReplicationConfigController extends ControllerBase {

  /**
   * Returns the replication configuration forms.
   */
  public function getForms() {
    $build['replication_unblock_button'] = $this->formBuilder()->getForm('Drupal\replication\Form\UnblockReplicationForm');
    $build['clear_queue_button'] = $this->formBuilder()->getForm('Drupal\replication\Form\ClearReplicationQueueForm');
    $build['replication_settings'] = $this->formBuilder()->getForm('Drupal\replication\Form\SettingsForm');
    return $build;
  }

}
