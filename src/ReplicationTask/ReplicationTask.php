<?php

namespace Drupal\replication\ReplicationTask;

use Drupal\replication\ReplicationTask\ReplicationTaskInterface;

/**
 * {@inheritdoc}
 */
class ReplicationTask implements ReplicationTaskInterface {

  /**
   * The ID of the filter plugin to use during replication.
   *
   * @var string
   */
  protected $filterName;

  /**
   * The parameters passed to the filter function.
   *
   * @var array
   */
  protected $parameters;

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
  public function setParameters(array $parameters = NULL) {
    if ($parameters == NULL) {
      $parameters = [];
    }
    $this->parameters = $parameters;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setParameter($name, $value) {
    if (!is_array($this->parameters)) {
      $this->setParameters([]);
    }
    $this->parameters[$name] = $value;
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
