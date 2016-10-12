<?php

namespace Drupal\Tests\replication\Unit\Normalizer;

use Drupal\Component\Utility\SafeMarkup;
use Drupal\entity_test\Entity\EntityTestMulRev;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Tests the link serialization format.
 *
 * @group replication
 */
class LinkItemNormalizerTest extends NormalizerTestBase {

  protected $entityClass = 'Drupal\entity_test\Entity\EntityTest';

  protected function setUp() {
    parent::setUp();

    // Create a Link field for testing.
    FieldStorageConfig::create([
      'field_name' => 'field_test_link',
      'entity_type' => 'entity_test_mulrev',
      'type' => 'link',
      'cardinality' => 2,
      'translatable' => FALSE,
    ])->save();
    FieldConfig::create([
      'entity_type' => 'entity_test_mulrev',
      'field_name' => 'field_test_link',
      'bundle' => 'entity_test_mulrev',
      'label' => 'Test link-field',
      'widget' => [
        'type' => 'link',
        'weight' => 0,
      ],
    ])->save();
  }

  /**
   * Tests field link normalization.
   */
  public function testLinkFieldNormalization() {
    // Create two entities that will be used as references fot the link field.
    $referenced_entity1 = EntityTestMulRev::create(
      [
        'name' => $this->randomMachineName(),
        'user_id' => 1,
      ]
    );
    $referenced_entity1->save();
    $referenced_entity2 = EntityTestMulRev::create(
      [
        'name' => $this->randomMachineName(),
        'user_id' => 1,
      ]
    );
    $referenced_entity2->save();

    // Create a test entity to serialize.
    $this->values = [
      'name' => $this->randomMachineName(),
      'user_id' => 1,
      'field_test_text' => [
        'value' => $this->randomMachineName(),
        'format' => 'full_html',
      ],
      'field_test_link' => [
        [
          'uri' => 'entity:entity_test_mulrev/' . $referenced_entity1->id(),
          'options' => [],
        ],
        [
          'uri' => 'entity:entity_test_mulrev/' . $referenced_entity2->id(),
          'options' => [],
        ],
      ],
    ];
    $this->entity = EntityTestMulRev::create($this->values);
    $this->entity->save();

    $expected = array(
      '@context' => array(
        '_id' => '@id',
        '@language' => 'en'
      ),
      '@type' => 'entity_test_mulrev',
      'en' => [
        '@context' => [
          '@language' => 'en',
        ],
        'langcode' => [
          ['value' => 'en'],
        ],
        'name' => [
          ['value' => $this->values['name']],
        ],
        'type' => [
          ['value' => 'entity_test_mulrev'],
        ],
        'created' => [
          ['value' => $this->entity->created->value],
        ],
        'default_langcode' => [
          ['value' => TRUE],
        ],
        'user_id' => [
          ['target_id' => $this->values['user_id']],
        ],
        '_rev' => [
          ['value' => $this->entity->_rev->value],
        ],
        'non_rev_field' => [],
        'field_test_text' => [
          [
            'value' => $this->values['field_test_text']['value'],
            'format' => $this->values['field_test_text']['format'],
            'processed' => ''
          ],
        ],
        'field_test_link' => [
          [
            'uri' => 'entity:entity_test_mulrev/' . $referenced_entity1->id(),
            'title' => NULL,
            'options' => [],
            'entity_type_id' => 'entity_test_mulrev',
            'target_uuid' => $referenced_entity1->uuid(),
          ],
          [
            'uri' => 'entity:entity_test_mulrev/' . $referenced_entity2->id(),
            'title' => NULL,
            'options' => [],
            'entity_type_id' => 'entity_test_mulrev',
            'target_uuid' => $referenced_entity2->uuid(),
          ],
        ],
      ],
      '_id' => $this->entity->uuid(),
      '_rev' => $this->entity->_rev->value,
    );

    // Test normalize.
    $normalized = $this->serializer->normalize($this->entity);
    foreach (array_keys($expected) as $key) {
      $this->assertEquals($expected[$key], $normalized[$key], "Field $key is normalized correctly.");
    }
    $this->assertEquals(array_diff_key($normalized, $expected), [], 'No unexpected data is added to the normalized array.');

    // Test denormalize.
    $denormalized = $this->serializer->denormalize($normalized, $this->entityClass, 'json');
    $this->assertTrue($denormalized instanceof $this->entityClass, SafeMarkup::format('Denormalized entity is an instance of @class', ['@class' => $this->entityClass]));
    $this->assertSame($denormalized->getEntityTypeId(), $this->entity->getEntityTypeId(), 'Expected entity type found.');
    $this->assertSame($denormalized->bundle(), $this->entity->bundle(), 'Expected entity bundle found.');
    $this->assertSame($denormalized->uuid(), $this->entity->uuid(), 'Expected entity UUID found.');
    $expected_link_field_values = [
      [
        'uri' => 'entity:entity_test_mulrev/' . $referenced_entity1->id(),
        'title' => NULL,
        'options' => [],
      ],
      [
        'uri' => 'entity:entity_test_mulrev/' . $referenced_entity2->id(),
        'title' => NULL,
        'options' => [],
      ],
    ];
    foreach ($denormalized->get('field_test_link')->getValue() as $key => $item) {
      $this->assertEquals($expected_link_field_values[$key], $item, "Field $key is normalized correctly.");
    }

    // Change uri for link fields with wrong IDs, then denormalize.
    // On entity denormalization it should return the correct uri for link
    // fields even if the ID is wrong, this because on denormalization we use
    // the uuid to get the entity ID.
    $normalized2 = $normalized;
    $normalized2['en']['field_test_link'][0]['uri'] = 'entity:entity_test_mulrev/111';
    $normalized2['en']['field_test_link'][1]['uri'] = 'entity:entity_test_mulrev/222';

    // Test denormalize.
    $denormalized2 = $this->serializer->denormalize($normalized2, $this->entityClass, 'json');
    $expected_link_field_values = [
      [
        'uri' => 'entity:entity_test_mulrev/' . $referenced_entity1->id(),
        'title' => NULL,
        'options' => [],
      ],
      [
        'uri' => 'entity:entity_test_mulrev/' . $referenced_entity2->id(),
        'title' => NULL,
        'options' => [],
      ],
    ];
    foreach ($denormalized2->get('field_test_link')->getValue() as $key => $item) {
      $this->assertEquals($expected_link_field_values[$key], $item, "Field $key is normalized correctly.");
    }
  }

}
