<?php

namespace Drupal\replication\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Class ConfirmClearReplicationQueueForm.
 *
 * @package Drupal\replication\Form
 */
class ConfirmClearReplicationQueueForm extends ConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'confirm_clear_replication_queue_form';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->clearReplicationQueue();
    $form_state->setRedirectUrl($this->getCancelUrl());
    $this->messenger()->addMessage($this->t('All the queued deployments have been marked as failed and have been removed from the replication queue.'));
  }

  /**
   * Clears the replication queue.
   */
  public function clearReplicationQueue() {
    // @todo Implement the queue clearing here.
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('This will mark as failed all the queued deployment and remove them from the replication queue. This action cannot be undone.');
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to clear the replication queue?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('replication.settings_form');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Clear queue');
  }

}
