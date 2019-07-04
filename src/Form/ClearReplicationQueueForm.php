<?php

namespace Drupal\replication\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ClearReplicationQueueForm.
 *
 * @package Drupal\replication\Form
 */
class ClearReplicationQueueForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'clear_replication_queue_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['clear_queue'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Clear replication queue'),
    );
    $form['clear_queue']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Clear queue'),
    ];
    $form['clear_queue']['description'] = [
      '#type' => 'markup',
      '#markup' => '<div class="description">' . $this->t('Click the button to clear the replication queue. It will mark all queued deployment as failed and will remove them from the queue. If there is a deployment in progress, then it will stay in the queue until it will be done or fail.') . '</div>',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('replication.confirm_clear_replication_queue');
  }

}
