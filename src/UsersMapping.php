<?php

namespace Drupal\replication;

use Drupal\Core\Config\ConfigFactoryInterface;

class UsersMapping {

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  public function getUid() {
    return $this->configFactory->get('replication.settings')->get('uid');
  }

}
