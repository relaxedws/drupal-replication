<?php

namespace Drupal\replication\ReplicationTask;

use Drupal\replication\ReplicationTask\ReplicationTaskInterface;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * {@inheritdoc}
 */
class ReplicationTask implements ReplicationTaskInterface {

  /**
   * @var string
   *   The id of the filter plugin to use during replication.
   */
  protected $filterName;

  /**
   * @var ParameterBag
   *   The parameters passed to the filter function.
   */
  protected $parameters;

  /**
   * ReplicationTask constructor.
   */
  public function __construct() {
    $this->parameters = new ParameterBag();
  }

  /**
   * {@inheritdoc}
   */
  public function setFilter($filter_name = NULL) {
    $this->filterName = $filter_name;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getFilter() {
    return $this->filterName;
  }

  /**
   * {@inheritdoc}
   */
  public function setParameters(ParameterBag $parameters) {
    $this->parameters = $parameters;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setParametersByArray(array $parameters_array = array()) {
    $parameters = new ParameterBag($parameters_array);
    return $this->setParameters($parameters);
  }

  /**
   * {@inheritdoc}
   */
  public function setParameter($name, $value) {
    if (!$this->parameters instanceof ParameterBag) {
      $parameters = new ParameterBag();
      $this->setParameters($parameters);
    }
    $this->parameters->set($name, $value);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getParameters() {
    return $this->parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function getParametersAsArray() {
    return $this->parameters->all();
  }

  /**
   * {@inheritdoc}
   */
  public function getParameter($name) {
    return $this->parameters->get($name);
  }

}
