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
   * @dataProvider filterTestProvider
   */
  public function testFilter($entity_type, $parameter_value, $expected) {
    // Use a mock builder for the class under test to eliminate the need to
    // mock all the dependencies. This is OK since the method under test is a
    // pure function, i.e. does not use the state createdy by the constructor.
    $filter = $this->getMockBuilder(EntityTypeFilter::class)
      ->disableOriginalConstructor()
      ->setMethods(NULL)
      ->getMock();
    $entity = $this->getMock(EntityInterface::class);
    $entity->method('getEntityTypeId')
      ->willReturn('article');
    $parameters = new ParameterBag(['entity_type' => $parameter_value]);

    $value = $filter->filter($entity, $parameters);

    $this->assertEquals($expected, $value);
  }

  /**
   * Provide test cases for the "entity_type" parameter.
   */
  public function filterTestProvider() {
    return [
      // Test singular parameter values.
      ['article', 'article', TRUE],
      ['article', 'page', FALSE],
      // Test multiple parameter values.
      ['article', 'page,article', TRUE],
      ['article', 'article,page', TRUE],
      ['article', 'page,news', FALSE],
      // Test bad data that might be entered into the parameters:
      ['article', '', FALSE],
      ['article', NULL, FALSE],
      ['article', FALSE, FALSE],
      ['article', TRUE, FALSE],
      ['article', 0, FALSE],
    ];
  }

}
