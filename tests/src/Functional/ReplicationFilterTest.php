<?php

namespace Drupal\Tests\replication\Functional;

use Drupal\multiversion\Entity\Workspace;
use Drupal\node\Entity\Node;
use Drupal\simpletest\WebTestBase;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Tests replication filters.
 *
 * @group replication
 */
class ReplicationFilterTest extends WebTestBase {

  protected $strictConfigSchema = FALSE;

  public static $modules = [
    'multiversion',
    'node',
    'user',
    'replication',
  ];

  /**
   * @var \Drupalser\Entity\User
   *   The logged in user.
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create Basic page and Article node types.
    if ($this->profile != 'standard') {
      $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);
    }

    $this->user = $this->drupalCreateUser(['administer workspaces']);
    $this->drupalLogin($this->user);
  }

  /**
   * Test filtering the changeset by UUIDs.
   */
  public function testUuidsFilter() {
    $container = \Drupal::getContainer();
    $changes_factory = $container->get('replication.changes_factory');

    $workspace = Workspace::create(['machine_name' => 'default', 'type' => 'basic']);
    $workspace->save();

    $entity1 = Node::create([
      'type' => 'article',
      'title' => 'Test Entity 1',
      'uid' => $this->user->id(),
    ]);
    $entity1->workspace = $workspace;
    $entity1->save();

    $entity2 = Node::create([
      'type' => 'article',
      'title' => 'Test Entity 2',
      'uid' => $this->user->id(),
    ]);
    $entity2->workspace = $workspace;
    $entity2->save();

    $changes = $changes_factory->get($workspace)->uuids([$entity1->uuid()])->getNormal();
    $this->assertEqual(1, count($changes), 'Expect there is 1 entity in the changeset.');
  }

  /**
   * Test filtering the changeset with the published filter.
   */
  public function testPublishedFilter() {
    $container = \Drupal::getContainer();
    $changes_factory = $container->get('replication.changes_factory');

    $workspace = Workspace::create(['machine_name' => 'default', 'type' => 'basic']);
    $workspace->save();

    $entity1 = Node::create([
      'type' => 'article',
      'title' => 'Test Entity 1',
      'uid' => $this->user->id(),
      'status' => TRUE,
    ]);
    $entity1->workspace = $workspace;
    $entity1->save();

    $entity2 = Node::create([
      'type' => 'article',
      'title' => 'Test Entity 2',
      'uid' => $this->user->id(),
      'status' => FALSE,
    ]);
    $entity2->workspace = $workspace;
    $entity2->save();

    $changes = $changes_factory->get($workspace)->filter('published')->getNormal();
    $this->assertEqual(1, count($changes), 'Expect there is 1 entity in the changeset.');
  }

  /**
   * Test filtering the changeset with the entity type filter.
   */
  public function testEntityTypeFilter() {
    $container = \Drupal::getContainer();
    $changes_factory = $container->get('replication.changes_factory');

    $this->drupalCreateContentType(['type' => 'article2', 'name' => 'Article2']);

    $workspace = Workspace::create(['machine_name' => 'default', 'type' => 'basic']);
    $workspace->save();

    $entity1 = Node::create([
      'type' => 'article',
      'title' => 'Test Entity 1',
      'uid' => $this->user->id(),
      'status' => TRUE,
    ]);
    $entity1->workspace = $workspace;
    $entity1->save();

    $entity2 = Node::create([
      'type' => 'article2',
      'title' => 'Test Entity 2',
      'uid' => $this->user->id(),
      'status' => FALSE,
    ]);
    $entity2->workspace = $workspace;
    $entity2->save();

    $parameters = new ParameterBag(['entity_type' => 'article']);
    $changes = $changes_factory->get($workspace)->filter('entity_type')->parameters($parameters)->getNormal();
    $this->assertEqual(1, count($changes), 'Expect there is 1 entity in the changeset.');
  }

}
