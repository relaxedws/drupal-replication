<?php

/**
 * @file
 * Drush integration for the replication module.
 */
use Doctrine\CouchDB\CouchDBClient;

/**
 * Implements of hook_drush_command().
 */
function replication_drush_command() {
  $items = array();

  $default_replicator = 'http://localhost:5984';
  $items['replication-start'] = array(
    'bootstrap' => DRUSH_BOOTSTRAP_NONE,
    'description' => 'Start a replication.',
    'arguments' => array(
      'source' => dt('Source database.'),
      'target' => dt('Target database.'),
    ),
    'required-arguments' => TRUE,
    'options' => array(
      'continuous' => array(
        'description' => dt('Continuous replication.'),
        'default' => FALSE,
      ),
      'replicator' => array(
        'description' => dt('The used replicator.'),
        'default' => $default_replicator,
      ),
    ),
    'outputformat' => array(
      'default' => 'key-value',
      'pipe-format' => 'json',
      'field-labels' => array(
        'ok' => 'Status',
        'no_changes' => 'No changes',
        'session_id' => 'Session ID',
        'source_last_seq' => 'Last sequence number',
        'replication_id_version' => 'Replication protocol version',
      ),
      'output-data-type' => 'format-list',
    ),
  );
  $items['replication-stop'] = array(
    'bootstrap' => DRUSH_BOOTSTRAP_NONE,
    'description' => 'Stop a replication.',
    'arguments' => array(
      'source' => dt('Source database.'),
      'target' => dt('Target database.'),
    ),
    'required-arguments' => TRUE,
    'options' => array(
      'continuous' => array(
        'description' => dt('Continuous replication.'),
        'default' => FALSE,
      ),
      'replicator' => array(
        'description' => dt('The used replicator.'),
        'default' => $default_replicator,
      ),
    ),
    'outputformat' => array(
      'default' => 'key-value',
      'pipe-format' => 'json',
      'field-labels' => array(
        'ok' => 'Status',
        'no_changes' => 'No changes',
        'session_id' => 'Session ID',
        'source_last_seq' => 'Last sequence number',
        'replication_id_version' => 'Replication protocol version',
      ),
      'output-data-type' => 'format-list',
    ),
  );
  $items['replication-active'] = array(
    'bootstrap' => DRUSH_BOOTSTRAP_NONE,
    'description' => 'Prints information about the specific active replication between target and source databases.',
    'arguments' => array(
      'source' => dt('Source database.'),
      'target' => dt('Target database.'),
    ),
    'options' => array(
      'replicator' => array(
        'description' => dt('The used replicator.'),
        'default' => $default_replicator,
      ),
    ),
    'outputformat' => array(
      'default' => 'key-value-list',
      'pipe-format' => 'json',
      'field-labels' => array(
        'source' => 'Source',
        'target' => 'Target',
        'started_on' => 'Started on',
        'progress' => 'Progress',
        'docs_read' => 'Documents read',
        'docs_written' => 'Documents written',
        'revisions_checked' => 'Revisions checked',
        'doc_write_failures' => 'Write failures',
        'pid' => 'Process ID',
      ),
    ),
  );

  return $items;
}

/**
 * Implements drush_hook_COMMAND_validate().
 */
function drush_replication_start_validate() {
  // Array of "Callback arguments" and "command line args".
  $params = func_get_args();
  replication_command_validate($params);
}

/**
 * Implements drush_hook_COMMAND_validate().
 */
function drush_replication_stop_validate() {
  // Array of "Callback arguments" and "command line args".
  $params = func_get_args();
  replication_command_validate($params);
}

/**
 * Implements drush_hook_COMMAND_validate().
 */
function drush_replication_active_validate() {
  // Array of "Callback arguments" and "command line args".
  $params = func_get_args();
  if (isset($params[0]) || isset($params[1])) {
    replication_command_validate($params);
  }
}

/**
 * Implements drush_hook_COMMAND().
 */
function drush_replication_start($source, $target) {
  try {
    $client = replication_client_factory();
    $continuous = drush_get_option('continuous');
    return $client->replicate($source, $target, NULL, $continuous);
  }
  catch (\Exception $e) {
    drush_set_error($e->getMessage());
  }
}

/**
 * Implements drush_hook_COMMAND().
 */
function drush_replication_stop($source, $target) {
  try {
    $client = replication_client_factory();
    $continuous = drush_get_option('continuous');
    return $client->replicate($source, $target, TRUE, $continuous);
  }
  catch (\Exception $e) {
    drush_set_error($e->getMessage());
  }
}

/**
 * Implements drush_hook_COMMAND().
 *
 * Prints information about the specific active replication between target and
 * source databases.
 */
function drush_replication_active($source = NULL, $target = NULL) {
  try {
    $client = replication_client_factory();
    $results = $client->getActiveTasks();
    foreach ($results as $key => $result) {
      $results[$key]['started_on'] = date('D, j M Y, H:i:s e', $result['started_on']);
      if ($source && $target && is_array($results)) {
        $source_diff = array_diff(replication_get_url_parts($result['source']), replication_get_url_parts($source));
        $target_diff = array_diff(replication_get_url_parts($result['target']), replication_get_url_parts($target));
        if (empty($source_diff) && empty($target_diff)) {
          // Return information about one active replication.
          return array($results[$key]);
        }
        else {
          drupal_set_message('No active replication.', 'status');
          return;
        }
      }
    }
    if (!empty($results)) {
      // Return information about all active replications.
      return $results;
    }
    else {
      drupal_set_message('No active replications.', 'status');
    }
  }
  catch (\Exception $e) {
    drush_set_error($e->getMessage());
  }
}

/**
 * Helper function for command validation.
 *
 * @param $params
 */
function replication_command_validate($params) {
  $replicator = drush_get_option('replicator');
  if ($replicator && replication_get_http_response_code($replicator) != 200) {
    drush_set_error(dt('Replicator not found.'));
  }
  if (replication_get_http_response_code($params['0']) != 200) {
    drush_set_error(dt('Source database not found.'));
  }
  if (replication_get_http_response_code($params['1']) != 200) {
    drush_set_error(dt('Target database not found.'));
  }
}

/**
 * Returns the CouchDBClient() object.
 */
function replication_client_factory() {
  $options = array('dbname' => '');
  $replicator = drush_get_option('replicator');
  $url_parts = replication_get_url_parts($replicator, TRUE);
  $options = array_merge($options, $url_parts);
  return CouchDBClient::create($options);
}

/**
 * Returns the response code for a request.
 *
 * @param $url
 *
 * @return string
 */
function replication_get_http_response_code($url) {
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_exec($ch);
  $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  return $httpcode;
}

/**
 * Returns url parts (host, port, path, user and pass).
 *
 * @param $replicator
 * @param bool $credentials
 *
 * @return array
 */
function replication_get_url_parts($replicator, $credentials = FALSE) {
  $url_parts = parse_url($replicator);
  $options = array(
    'host' => $url_parts['host'],
    'port' => $url_parts['port'],
    'path' => trim($url_parts['path'], '/'),
  );
  if ($credentials) {
    $options['user'] = $url_parts['user'];
    $options['password'] = $url_parts['pass'];
  }
  return $options;
}
