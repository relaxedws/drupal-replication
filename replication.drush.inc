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

  $items['start-replication'] = array(
    'description' => 'Start a replication.',
    'arguments' => array(
      'source' => dt('Source database.'),
      'target' => dt('Target database.'),
    ),
    'options' => array(
      'continuous' => array(
        'description' => dt('Continuous replication.'),
        'default' => FALSE,
      ),
      'replicator' => array(
        'description' => dt('The used replicator.'),
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
  $items['stop-replication'] = array(
    'description' => 'Stop a replication.',
    'arguments' => array(
      'source' => dt('Source database.'),
      'target' => dt('Target database.'),
    ),
    'options' => array(
      'replicator' => array(
        'description' => dt('The used replicator.'),
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
  $items['active-replication'] = array(
    'description' => 'Prints information about the specific active replication between target and source databases.',
    'arguments' => array(
      'source' => dt('Source database.'),
      'target' => dt('Target database.'),
    ),
    'options' => array(
      'replicator' => array(
        'description' => dt('The used replicator.'),
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
function drush_replication_start_replication_validate() {
  // Array of "Callback arguments" and "command line args".
  $params = func_get_args();
  _replication_command_validate($params);
}

/**
 * Implements drush_hook_COMMAND_validate().
 */
function drush_replication_stop_replication_validate() {
  // Array of "Callback arguments" and "command line args".
  $params = func_get_args();
  _replication_command_validate($params);
}

/**
 * Implements drush_hook_COMMAND_validate().
 */
function drush_replication_active_replication_validate() {
  // Array of "Callback arguments" and "command line args".
  $params = func_get_args();
  if (isset($params[0]) || isset($params[1])) {
    _replication_command_validate($params);
  }
}

/**
 * Implements drush_hook_COMMAND().
 */
function drush_replication_start_replication($source, $target) {
  try {
    $options = array('dbname' => '');
    if ($replicator = drush_get_option('replicator')) {
      $url_parts = _get_url_parts($replicator, TRUE);
      $options = array_merge($options, $url_parts);
    }
    $client = CouchDBClient::create($options);
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
function drush_replication_stop_replication($source, $target) {
  try {
    $options = array('dbname' => '');
    if ($replicator = drush_get_option('replicator')) {
      $url_parts = _get_url_parts($replicator, TRUE);
      $options = array_merge($options, $url_parts);
    }
    $client = CouchDBClient::create($options);
    return $client->replicate($source, $target, TRUE, TRUE);
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
function drush_replication_active_replication($source = NULL, $target = NULL) {
  try {
    $options = array('dbname' => '');
    if ($replicator = drush_get_option('replicator')) {
      $url_parts = _get_url_parts($replicator, TRUE);
      $options = array_merge($options, $url_parts);
    }
    $client = CouchDBClient::create($options);
    $results = $client->getActiveTasks();
    foreach ($results as $key => $result) {
      $results[$key]['started_on'] = date('D, j M Y, H:i:s e', $result['started_on']);
      if ($source && $target && is_array($results)) {
        $source_diff = array_diff(_get_url_parts($result['source']), _get_url_parts($source));
        $target_diff = array_diff(_get_url_parts($result['target']), _get_url_parts($target));
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
function _replication_command_validate($params) {
  if (_get_http_response_code('http://localhost:5984') != 200) {
    drush_set_error(dt('CouchDB not found. Please install CouchDB.'));
  }
  if (_get_http_response_code($params['0']) != 200) {
    drush_set_error(dt('Source database not found.'));
  }
  if (_get_http_response_code($params['1']) != 200) {
    drush_set_error(dt('Target database not found.'));
  }
}

/**
 * Returns the response code for a request.
 *
 * @param $url
 *
 * @return string
 */
function _get_http_response_code($url) {
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
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
function _get_url_parts($replicator, $credentials = FALSE) {
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
