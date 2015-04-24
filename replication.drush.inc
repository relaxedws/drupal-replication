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
      'continuous' => dt('Continuous replication.'),
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
      'output-data-type' => 'format-table',
    ),
  );
  $items['stop-replication'] = array(
    'description' => 'Stop a replication.',
    'arguments' => array(
      'source' => dt('Source database.'),
      'target' => dt('Target database.'),
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
  _replication_command_validate($params);
}

/**
 * Implements drush_hook_COMMAND().
 *
 * @param $source
 * @param $target
 * @param $continuous
 *
 * @return array
 */
function drush_replication_start_replication($source, $target, $continuous = NULL) {
  try {
    $client = CouchDBClient::create(array('dbname' => ''));
    return $client->replicate($source, $target, NULL, $continuous);
  }
  catch (\Exception $e) {
    drush_set_error($e->getMessage());
  }
}

/**
 * Implements drush_hook_COMMAND().
 *
 * @param $source
 * @param $target
 *
 * @return array
 */
function drush_replication_stop_replication($source, $target) {
  try {
    $client = CouchDBClient::create(array('dbname' => ''));
    return $client->replicate($source, $target, TRUE);
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
 *
 * @param $source
 * @param $target
 */
function drush_replication_active_replication($source, $target) {
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
