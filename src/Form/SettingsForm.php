<?php

namespace Drupal\replication\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SettingsForm.
 *
 * @package Drupal\replication\Form
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'replication.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'replication_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('replication.settings');

    $options = [
      'uid' => $this->t('Map by UID'),
      'anonymous' => $this->t('Map to Anonymous'),
      'uid_1' => $this->t('Map to UID 1'),
    ];

    $form['mapping_type'] = array(
      '#type' => 'select',
      '#title' => $this->t('Users mapping type'),
      '#default_value' => $config->get('mapping_type'),
      '#options' => $options,
      '#description' => $this->t("Select how users will be mapped when they can't be mapped by username or email."),
    );

    $form['uid'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('UID'),
      '#default_value' => $config->get('mapping_type') === 'uid' ? $config->get('uid') : '',
      '#maxlength' => 60,
      '#size' => 30,
      '#states' => [
        'visible' => [
          'select[name="mapping_type"]' => ['value' => 'uid'],
        ],
      ],
    );


    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $storage = \Drupal::entityTypeManager()->getStorage('user');
    $uid = trim($form_state->getValue('uid'));
    if ($form_state->getValue('mapping_type') === 'uid' && is_numeric($uid)) {
      if (!$storage->load($uid)) {
        $form_state->setErrorByName('uid', "Provided UID doesn't exist.");
      }
    }
    elseif ($form_state->getValue('mapping_type') === 'uid' && !is_numeric($uid)) {
      $form_state->setErrorByName('uid', 'Empty or wrong format for the UID field.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $config = $this->config('replication.settings');
    $mapping_type = $form_state->getValue('mapping_type');
    switch ($mapping_type) {
      case 'uid':
        $uid = $form_state->getValue('uid');
        break;
      case 'anonymous':
        $uid = 0;
        break;
      case 'uid_1':
        $uid = 1;
        break;
      default:
        $uid = NULL;
    }

    $config
      ->set('mapping_type', $mapping_type)
      ->set('uid', trim($uid))
      ->save();
  }

}
