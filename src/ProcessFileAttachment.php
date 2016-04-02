<?php

namespace Drupal\replication;


use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ProcessFileAttachment {

  /** @var \Drupal\Core\Session\AccountProxyInterface  */
  protected $current_user;

  /** @var  \Drupal\Core\Entity\EntityRepositoryInterface */
  protected $entity_repository;

  /** @var  \Drupal\Core\Entity\EntityTypeManagerInterface */
  protected $entity_type_manager;

  function __construct(AccountProxyInterface $current_user, EntityRepositoryInterface $entity_repository, EntityTypeManagerInterface $entity_type_manager) {
    $this->current_user = $current_user;
    $this->entity_repository = $entity_repository;
    $this->entity_type_manager = $entity_type_manager;
  }

  /**
   * Processes a file attachment.
   *
   * Returns the file object or NULL if it can't be created.
   *
   * @param string $data
   * @param string $key
   * @param string $format
   *
   * @return \Drupal\file\FileInterface|NULL
   */
  public function process($data, $key, $format) {
    $current_user_id = $this->current_user->id();
    list(, , $file_uuid, $scheme, $target) = explode('/', $key, 5);
    $uri = "$scheme://$target";
    $stream_wrapper_name = 'stream_wrapper.' . $scheme;
    multiversion_prepare_file_destination($uri, \Drupal::service($stream_wrapper_name));
    // Check if exists a file entity with this uuid.
    /** @var FileInterface $file */
    $file = $this->entity_repository
      ->loadEntityByUuid('file', $file_uuid);
    if ($file && is_file($file->getFileUri())) {
      // Do nothing.
    }
    // If the file entity exists but the file is missing then run the
    // deserializer to create the file.
    elseif ($file && !is_file($file->getFileUri())) {
      $file_context = [
        'uri' => $uri,
        'uuid' => $file_uuid,
        'status' => FILE_STATUS_PERMANENT,
        'uid' => $current_user_id,
      ];
      $file = \Drupal::getContainer()
        ->get('serializer')
        ->deserialize($data, '\Drupal\file\FileInterface', $format, $file_context);
    }
    // Create the new entity file and the file itself.
    else {
      // Check if exists a file with this $uri, if it exists then rename the file.
      $existing_files = $this->entity_type_manager
        ->getStorage('file')
        ->loadByProperties(['uri' => $uri]);
      if (count($existing_files)) {
        $uri = file_destination($uri, FILE_EXISTS_RENAME);
      }
      $file_context = [
        'uri' => $uri,
        'uuid' => $file_uuid,
        'status' => FILE_STATUS_PERMANENT,
        'uid' => $current_user_id,
      ];
      $file = \Drupal::getContainer()
        ->get('serializer')
        ->deserialize($data, '\Drupal\file\FileInterface', $format, $file_context);
    }

    return $file;
  }

}
