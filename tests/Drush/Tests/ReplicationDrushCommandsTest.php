<?php

namespace Unish;

if (class_exists('Unish\CommandUnishTestCase')) {

  /*
   * @file
   * PHPUnit Tests for replication. This uses Drush's own test framework, based on PHPUnit.
   * To run the tests, use run-tests-drush.sh from the replication directory.
   */
  class ReplicationDrushCommandsTest extends CommandUnishTestCase {

    /**
     * Default idle timeout for commands.
     *
     * @var int
     */
    private $defaultIdleTimeout = 120;

    /**
     * Idle timeouts for commands.
     *
     * Reset to $defaultIdleTimeout after executing a command.
     *
     * @var int
     */
    protected $idleTimeout = 120;

    public function testReplicationCommands() {
      // Specify '8' just in case user has not set UNISH_DRUPAL_MAJOR_VERSION env variable.
      $sites = $this->setUpDrupal(1, TRUE, '8', 'standard');
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

      $this->drush('replication-start', array('', ''), array('replicator' => ''));
      $output = $this->getOutput();
      $this->assertContains('status', $output, 'Message.');
    }
  }

}
