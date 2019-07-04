<?php

namespace Drupal\replication\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class UnblockReplicationForm.
 *
 * @package Drupal\replication\Form
 */
class UnblockReplicationForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'unblock_replication_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $last_replication_failed = \Drupal::state()->get('workspace.last_replication_failed', FALSE);
    $form['unblock'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Unblock replication (when it\'s blocked)'),
    );
    $form['unblock']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Unblock replication'),
      '#disabled' => !$last_replication_failed,
    ];
    $form['unblock']['description'] = [
      '#type' => 'markup',
      '#markup' => '<div class="description">' . $this->t('Click the button to unblock replication. This button is disabled when replication is not blocked.') . '</div>',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::state()->set('workspace.last_replication_failed', FALSE);
    $this->messenger()->addMessage($this->t('Replication blocker has been reset you can now create and run deployments.'));
  }

}
