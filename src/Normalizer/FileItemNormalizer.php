<?php

namespace Drupal\replication\Normalizer;

use Drupal\file\Entity\File;
use Drupal\serialization\Normalizer\FieldItemNormalizer;

class FileItemNormalizer extends FieldItemNormalizer {

  /**
   * @var string[]
   */
  protected $supportedInterfaceOrClass = [
    'Drupal\file\Plugin\Field\FieldType\FileItem',
    'Drupal\image\Plugin\Field\FieldType\ImageItem',
  ];

  /**
   * {@inheritdoc}
   */
  public function normalize($data, $format = NULL, array $context = []) {
    $result = [];
    $definition = $data->getFieldDefinition();
    $values = $data->getValue();
    $file = isset($values['target_id']) ? File::load($values['target_id']) : NULL;
    $file_system = \Drupal::service('file_system');
    if ($file) {
      $uri = $file->getFileUri();
      $scheme = $file_system->uriScheme($uri);
      $field_name = $definition->getName();

      if (!$target = file_uri_target($uri)) {
        $target = $file->getFileName();
      }

      // Create the attachment key, the format is: field_name:delta:uuid:scheme:target_uri.
      $key = $field_name . '/' . $data->getName() . '/' . $file->uuid() . '/' . $scheme . '/' . $target;

      // @todo {@link https://www.drupal.org/node/2600354 Align file data normalization with attachment normalization.}
      $file_contents = file_get_contents($uri);
      if (in_array($file_system->uriScheme($uri), ['public', 'private']) == FALSE) {
        $file_data = '';
      }
      else {
        $file_data = base64_encode($file_contents);
      }

      // @todo {@link https://www.drupal.org/node/2600360 Add revpos and other missing properties to the result array.}
      $result = [
        $key => [
          'content_type' => $file->getMimeType(),
          'digest' => 'md5-' . base64_encode(md5($file_contents)),
          'length' => $file->getSize(),
          'data' => $file_data,
        ],
      ];

      if (!empty($values['alt'])) {
        $result[$key]['alt'] = $values['alt'];
      }
    }

    return $result;
  }

}
