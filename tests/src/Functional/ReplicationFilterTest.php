<?php

namespace Drupal\Tests\replication\Functional;

use Drupal\multiversion\Entity\Workspace;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\simpletest\BrowserTestBase;

/**
 * Tests replication filters.
 *
 * @group replication
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */ 
class ReplicationFilterTest extends BrowserTestBase {
  
  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'replication',
    'node',
    'user',
  ];
  
  /**
   * Test filtering by UUIDs.
   */
  public function testUuidFilter() {
    $container = \Drupal::getContainer();
    $workspace_manager = $container->get('workspace.manager');
    $changes_factory = $container->get('replication.changes_factory');
    $revisiondiff_factory = $container->get('replication.revisiondiff_factory');

    $workspace = Workspace::create(['machine_name' => 'default', 'type' => 'basic']);
    $workspace->save();

    $permissions = [
      'administer nodes',
      'administer workspaces',
    ];
    $user = $this->drupalCreateUser($permissions);
    $this->drupalLogin($user);

    $node_type = NodeType::create([
      'type' => 'test',
      'label' => 'Test',
    ]);
    $node_type->save();
    $node1 = Node::create([
      'type' => 'test',
      'title' => 'Test Node 1',
      'uid' => $user->id(),
    ]);
    $node1->workspace = $workspace;
    $node1->save();
    $node2 = Node::create([
      'type' => 'test',
      'title' => 'Test Node 2',
      'uid' => $user->id(),
    ]);
    $node2->workspace = $workspace;
    $node2->save();

    $changes = $changes_factory->get($workspace)->uuids([$node1->uuid()])->getNormal();
    $this->assertCount(1, $changes);
  }

  /**
   * Test filtering published entities.
   */
  public function testPublishedFilter() {
    $container = \Drupal::getContainer();
    $workspace_manager = $container->get('workspace.manager');
    $changes_factory = $container->get('replication.changes_factory');
    $revisiondiff_factory = $container->get('replication.revisiondiff_factory');
    $replication_filter_manager = $container->get('plugin.manager.replication_filter');

    $workspace = Workspace::create(['machine_name' => 'default', 'type' => 'basic']);
    $workspace->save();

    $permissions = [
      'administer nodes',
      'administer workspaces',
    ];
    $user = $this->drupalCreateUser($permissions);
    $this->drupalLogin($user);

    $node_type = NodeType::create([
      'type' => 'test',
      'label' => 'Test',
    ]);
    $node_type->save();
    $node1 = Node::create([
      'type' => 'test',
      'title' => 'Test Node 1',
      'uid' => $user->id(),
      'status' => NODE_PUBLISHED,
    ]);
    $node1->workspace = $workspace;
    $node1->save();
    $node2 = Node::create([
      'type' => 'test',
      'title' => 'Test Node 2',
      'uid' => $user->id(),
      'status' => NODE_NOT_PUBLISHED,
    ]);
    $node2->workspace = $workspace;
    $node2->save();

    $changes = $changes_factory->get($workspace)->filter('published')->getNormal();
    $this->assertCount(1, $changes, TRUE);
  }
}

