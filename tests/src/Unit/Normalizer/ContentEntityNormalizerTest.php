<?php

namespace Drupal\Tests\replication\Unit\Normalizer;

use Drupal\Component\Utility\SafeMarkup;
use Drupal\entity_test\Entity\EntityTestMulRev;
use Drupal\language\Entity\ConfigurableLanguage;

/**
 * Tests the content serialization format.
 *
 * @group replication
 */
class ContentEntityNormalizerTest extends NormalizerTestBase {

  protected $entityClass = 'Drupal\entity_test\Entity\EntityTest';

  protected function setUp() {
    parent::setUp();

    // Create a test entity to serialize.
    $this->values = [
      'name' => $this->randomMachineName(),
      'user_id' => 1,
      'field_test_text' => [
        'value' => $this->randomMachineName(),
        'format' => 'full_html',
      ],
    ];
    ConfigurableLanguage::createFromLangcode('ro')->save();

    $this->entity = EntityTestMulRev::create($this->values);
    $this->entity->save();

    $this->romanian = $this->entity->addTranslation('ro');
    $this->romanian->name->value = $this->entity->name->value . '_ro';
    $this->romanian->field_test_text->value = $this->values['field_test_text']['value'] . '_ro';
    $this->romanian->save();
  }

  public function testNormalizer() {
    $revs = EntityTestMulRev::load($this->entity->id())->_rev->revisions;
    // Test normalize.
    $expected = [
      '@context' => [
        '_id' => '@id',
        '@language' => 'en'
      ],
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
          ],
        ],
      ],
      'ro' => [
        '@context' => [
          '@language' => 'ro',
        ],
        'langcode' => [
          ['value' => 'ro'],
        ],
        'name' => [
          ['value' => $this->values['name'] . '_ro'],
        ],
        'type' => [
          ['value' => 'entity_test_mulrev'],
        ],
        'created' => [
          ['value' => $this->romanian->created->value],
        ],
        'default_langcode' => [
          ['value' => FALSE],
        ],
        'user_id' => [
          ['target_id' => $this->values['user_id']],
        ],
        '_rev' => [
          ['value' => $this->romanian->_rev->value],
        ],
        'non_rev_field' => [],
        'field_test_text' => [
          [
            'value' => $this->values['field_test_text']['value'] . '_ro',
            'format' => NULL,
          ],
        ],
      ],
      '_id' => $this->entity->uuid(),
      '_rev' => $this->entity->_rev->value,
      '_revisions' => [
        'start' => 2,
        'ids' => $revs,
      ],
    ];

    $normalized = $this->serializer->normalize($this->entity);

    foreach (array_keys($expected) as $key) {
      $this->assertEquals($expected[$key], $normalized[$key], "Field $key is normalized correctly.");
    }
    $this->assertEquals(array_diff_key($normalized, $expected), [], 'No unexpected data is added to the normalized array.');

    // Test normalization when is set the revs query parameter.
    $revs = $this->entity->_rev->revisions;
    $expected['_revisions'] = [
      'ids' => $revs,
      'start' => 2,
    ];

    $normalized = $this->serializer->normalize($this->entity, NULL, ['query' => ['revs' => TRUE]]);

    foreach (array_keys($expected) as $key) {
      $this->assertEquals($expected[$key], $normalized[$key], "Field $key is normalized correctly.");
    }
    $this->assertTrue($expected['_revisions']['start'] === $normalized['_revisions']['start'], "Correct data type for the start field.");
    $this->assertEquals(array_diff_key($normalized, $expected), [], 'No unexpected data is added to the normalized array.');

    // @todo {@link https://www.drupal.org/node/2600460 Test context switches.}

    // Test serialize.
    $normalized = $this->serializer->normalize($this->entity);
    $expected = json_encode($normalized);
    // Paranoid test because JSON serialization is tested elsewhere.
    $actual = $this->serializer->serialize($this->entity, 'json');
    $this->assertSame($actual, $expected, 'Entity serializes correctly to JSON.');

    // Test denormalize.
    $denormalized = $this->serializer->denormalize($normalized, $this->entityClass, 'json');
    $this->assertTrue($denormalized instanceof $this->entityClass, SafeMarkup::format('Denormalized entity is an instance of @class', ['@class' => $this->entityClass]));
    $this->assertSame($denormalized->getEntityTypeId(), $this->entity->getEntityTypeId(), 'Expected entity type found.');
    $this->assertSame($denormalized->bundle(), $this->entity->bundle(), 'Expected entity bundle found.');
    $this->assertSame($denormalized->uuid(), $this->entity->uuid(), 'Expected entity UUID found.');

    // @todo {@link https://www.drupal.org/node/2600460 Test context switches.}
  }

}
