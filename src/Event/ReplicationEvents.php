<?php

namespace Drupal\replication\Event;

final class ReplicationEvents {

  /**
   * Event fired before a replication begins.
   *
   * @var string
   */
  const PRE_REPLICATION = 'replication.pre_replication';

  /**
   * Event fired after a replication is completed.
   *
   * This event is fired regardless of whether the replication succeeded or
   * failed.
   *
   * @var string
   */
  const POST_REPLICATION = 'replication.post_replication';

}
