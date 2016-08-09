<?php

namespace Drupal\Tests\replication\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\replication\Entity\ReplicationSettings;

/**
 * Test operations on ReplicationSettings config entity.
 *
 * @group replication
 */
class ReplicationSettingsTest extends KernelTestBase {

  protected $strictConfigSchema = FALSE;

  public static $modules = [
    'user',
    'serialization',
    'key_value',
    'multiversion',
    'replication',
  ];

  /**
   * Test creation of ReplicationSettings config entity.
   */
  public function testCreation() {
    $this->installEntitySchema('replication_settings');
    $entityTypeManager = $this->container->get('entity_type.manager');
    $entity = $entityTypeManager->getStorage('replication_settings')->create([
      'id' => 'test',
      'label' => 'Replication settings test',
      'filter_id' => 'entity_type',
      'parameters' => ['entity_type' => 'article'],
    ]);
    $this->assertTrue($entity instanceof ReplicationSettings, 'Replication Settings entity was created.');
    $entity->save();

    $entity = $entityTypeManager->getStorage('replication_settings')->load('test');

    $this->assertEquals($entity->id(), 'test', 'Test replication settings config entity successfully saved.');
  }

}
