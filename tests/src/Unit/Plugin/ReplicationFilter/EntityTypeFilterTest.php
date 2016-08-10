<?php

namespace Drupal\Tests\replication\Unit\Plugin\ReplicationFilter;

use Drupal\Core\Entity\EntityInterface;
use Drupal\replication\Plugin\ReplicationFilter\EntityTypeFilter;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Tests that the entity type filter parses parameters correctly.
 *
 * @group replication
 */
class EntityTypeFilterTest extends \PHPUnit_Framework_TestCase {

  /**
   * Test filtering entity types.
   *
   * @param string $entity_type_id
   *   The entity type ID filter parameter.
   * @param string $bundle
   *   The bundle filter parameter.
   * @param string $expected
   *   The expected return value from the filter method.
   *
   * @dataProvider filterTestProvider
   */
  public function testFilter($entity_type_id, $bundle, $expected) {
    // Use a mock builder for the class under test to eliminate the need to
    // mock all the dependencies. The method under test uses the $configuration
    // set by the constructor, but is retrieved via a get method we can stub.
    $filter = $this->getMockBuilder(EntityTypeFilter::class)
      ->disableOriginalConstructor()
      ->setMethods(['getConfiguration'])
      ->getMock();
    $configuration = [
      'entity_type_id' => $entity_type_id,
      'bundle' => $bundle,
    ];
    $filter->method('getConfiguration')
      ->willReturn($configuration);
    $entity = $this->getMock(EntityInterface::class);
    $entity->method('getEntityTypeId')
      ->willReturn('node');
    $entity->method('bundle')
      ->willReturn('article');

    $value = $filter->filter($entity);

    $this->assertEquals($expected, $value);
  }

  /**
   * Provide test cases for the "entity_type_id" and "bundle" parameters.
   */
  public function filterTestProvider() {
    return [
      // Test singular parameter values.
      ['node', 'article', TRUE],
      ['node', 'page', FALSE],
      // Test multiple parameter values.
      ['node,node', 'page,article', TRUE],
      ['node,node', 'article,page', TRUE],
      ['node,node', 'page,news', FALSE],
      // Test mismatched multiple parameter values.
      ['node', 'page,article', FALSE],
      ['node,node', 'node', FALSE],
      // Test bad data that might be entered into the parameters:
      ['', '', FALSE],
      [NULL, NULL, FALSE],
      [FALSE, FALSE, FALSE],
      [TRUE, TRUE, FALSE],
      [0, 0, FALSE],
    ];
  }

}
