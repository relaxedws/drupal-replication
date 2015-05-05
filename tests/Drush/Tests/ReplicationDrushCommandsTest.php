<?php

namespace Unish;

if (class_exists('Unish\CommandUnishTestCase')) {

  /*
   * @file
   * PHPUnit Tests for replication. This uses Drush's own test framework, based on PHPUnit.
   */
  class ReplicationDrushCommandsTest extends CommandUnishTestCase {

    public function testReplicationCommands() {
      // Specify '8' just in case user has not set UNISH_DRUPAL_MAJOR_VERSION env variable.
      $sites = $this->setUpDrupal(1, TRUE, '8', 'minimal');
      $target = dirname(__DIR__);
      \symlink($target, $this->webroot() . '/modules/replication');
      \symlink($target . '/../../replication.drush.inc', UNISH_SANDBOX . '/share/drush/commands/replication.drush.inc');
      $options = array(
        'yes' => NULL,
        'pipe' => NULL,
        'root' => $this->webroot(),
        'uri' => key($sites),
        'cache' => NULL,
        'strict' => 0,
      );

      $modules = array('key_value', 'multiversion', 'relaxed');
      $this->drush('pm-download', $modules, $options);
      $this->drush('pm-enable', $modules, $options);
      $this->drush('updb', $modules, $options);
      $this->drush('updb', $modules, $options);
      $this->drush('updb', $modules, $options);

      // Test replication-start when source and target parameters are missing.
      $this->drush('replication-start', array('', ''), array('replicator' => ''), NULL, NULL, self::EXIT_ERROR);
      $output = $this->getErrorOutput();
      $this->assertContains('Missing required arguments: source, target.', $output, 'Missing required arguments: source, target.');

      // Test replication-start when source and target sites doesn't exist.
      $this->drush('replication-start', array('fake_site1', 'fake_site2'), array('replicator' => ''), NULL, NULL, self::EXIT_ERROR);
      $output = $this->getErrorOutput();
      $this->assertContains('Source database not found.', $output, 'Source database not found.');
      $this->assertContains('Target database not found.', $output, 'Target database not found.');

      // Test replication-stop when source and target parameters are missing.
      $this->drush('replication-stop', array('', ''), array('replicator' => ''), NULL, NULL, self::EXIT_ERROR);
      $output = $this->getErrorOutput();
      $this->assertContains('Missing required arguments: source, target.', $output, 'Missing required arguments: source, target.');

      // Test replication-stop when source and target sites doesn't exist.
      $this->drush('replication-stop', array('fake_site1', 'fake_site2'), array('replicator' => ''), NULL, NULL, self::EXIT_ERROR);
      $output = $this->getErrorOutput();
      $this->assertContains('Source database not found.', $output, 'Source database not found.');
      $this->assertContains('Target database not found.', $output, 'Target database not found.');

      // Test replication-active when the replicator is not specified.
      $this->drush('replication-active', array('', ''), array('replicator' => ''), NULL, NULL, self::EXIT_ERROR);
      $output = $this->getErrorOutput();
      $this->assertContains('Could not connect to server', $output, 'Could not connect to server');
    }
  }

}
