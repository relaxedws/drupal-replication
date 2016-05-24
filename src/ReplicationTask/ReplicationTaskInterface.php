<?php

namespace Drupal\replication\ReplicationTask;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * The replication settings between source and target.
 *
 * This interface defines the optional settings to be used during a replication
 * task between a source and a target workspace. These are based on CouchDB's
 * replication specifications.
 *
 * @see http://docs.couchdb.org/en/latest/json-structure.html#replication-settings
 */
interface ReplicationTaskInterface {

  /**
   * Set the id of the filter plugin to use during replication.
   *
   * @param string $filter_name
   *   The plugin id of a ReplicationFilterInterface.
   *
   * @return ReplicationTaskInterface
   *   Returns $this.
   */
  public function setFilter($filter_name);

  /**
   * Get the id of the filter plugin to use during replication.
   *
   * @return string
   *   The plugin id of a ReplicationFilterInterface.
   */
  public function getFilter();

  /**
   * Set the UUIDs to include during replication.
   *
   * @param array $uuids
   *   The UUIDs to include during replication.
   *
   * @return ReplicationTaskInterface
   *   Returns $this.
   */
  public function setUuids(array $uuids);

  /**
   * Get the UUIDs to use during replication.
   *
   * @return string[string]
   *   The UUIDs to include during replication.
   */
  public function getUuids();

  /**
   * Set the parameters for the filter plugin.
   *
   * @param ParameterBag $parameters
   *   The parameters passed to the filter function.
   *
   * @return ReplicationTaskInterface
   *   Returns $this.
   */
  public function setParameters(ParameterBag $parameters);

  /**
   * Set the parameters for the filter plugin using an array.
   *
   * The array input is converted to a ParameterBag and then passed to
   * setParameters.
   *
   * @param array $parameters_array
   *   An associative array of name-value parameters.
   *
   * @return ReplicationTaskInterface
   *   Returns $this.
   */
  public function setParametersByArray(array $parameters_array);

  /**
   * Set a parameter for the filter plugin.
   *
   * If no parameter bag yet exists, an empty parameter bag will be created.
   *
   * @param string $name
   *   The parameter name to set.
   * @param string $value
   *   The value for the parameter.
   *
   * @return ReplicationTaskInterface
   *   Returns $this.
   */
  public function setParameter($name, $value);

  /**
   * Get the parameters for the filter plugin.
   *
   * @return ParameterBag
   *   The parameters passed to the filter plugin.
   */
  public function getParameters();

  /**
   * Converts the parameter bag to an associative array and returns the array.
   *
   * @return string[string]
   *   An associative array of parameters passed to the filter fn.
   */
  public function getParametersAsArray();

  /**
   * Get a parameter's value.
   *
   * @param string $name
   *   The parameter name.
   *
   * @return mixed
   *   The parameter value.
   *
   * @throws \Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException
   *   If the parameter is not defined.
   */
  public function getParameter($name);

}
