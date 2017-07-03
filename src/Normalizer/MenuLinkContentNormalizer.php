<?php

namespace Drupal\replication\Normalizer;

use Drupal\menu_link_content\MenuLinkContentInterface;
use Drupal\multiversion\Entity\WorkspaceInterface;

class MenuLinkContentNormalizer extends ContentEntityNormalizer {

  /**
   * @var string[]
   */
  protected $supportedInterfaceOrClass = ['\Drupal\menu_link_content\Entity\MenuLinkContent'];

  /**
   * {@inheritdoc}
   */
  public function denormalize($data, $class, $format = NULL, array $context = []) {
    if (isset($data['parent']) && !empty($data['parent']) && strpos($data['parent'], 'menu_link_content') === 0) {
      list($type, $uuid, $id) = explode(':', $data['parent']);
      if ($type === 'menu_link_content' && $uuid && is_numeric($id)) {
        $storage = $this->entityManager->getStorage('menu_link_content');
        $parent = $storage->loadByProperties(['uuid' => $uuid]);
        $parent = reset($parent);
        if ($parent instanceof MenuLinkContentInterface && $parent->id() && $parent->id() != $id) {
          $data['parent'] = $type . ':' . $uuid . ':' . $parent->id();
        }
        elseif (!$parent) {
          // Create a new menu link as stub.
          $parent = $storage->create([
            'uuid' => $uuid,
            'link' => 'internal:/',
          ]);
          // Set the target workspace if we have it in context.
          if (isset($context['workspace'])
            && ($context['workspace'] instanceof WorkspaceInterface)) {
            $parent->workspace->target_id = $context['workspace']->id();
          }
          // Indicate that this revision is a stub.
          $parent->_rev->is_stub = TRUE;
          $parent->save();
          $data['parent'] = $type . ':' . $uuid . ':' . $parent->id();
        }
      }
    }

    return parent::denormalize($data, $class, $format, $context);
  }

}
