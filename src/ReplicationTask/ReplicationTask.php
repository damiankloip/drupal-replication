<?php

namespace Drupal\replication\ReplicationTask;

/**
 * {@inheritdoc}
 */
class ReplicationTask implements ReplicationTaskInterface {

  /**
   * The ID of the filter plugin to use during replication.
   *
   * @var string
   */
  protected $filter;

  /**
   * The parameters passed to the filter function.
   *
   * @var array
   */
  protected $parameters;

  /**
   * Number of items to return.
   *
   * @var int
   *   The limit of items.
   */
  protected $limit = 1000;

  /**
   * Number of items to send pe BulkDocs request.
   *
   * @var int
   *   The limit of items.
   */
  private $bulkDocsLimit = 100;

  /**
   * {@inheritdoc}
   */
  public function setFilter($filter = NULL) {
    $this->filter = $filter;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getFilter() {
    return $this->filter;
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
  public function setLimit($limit) {
    $this->limit = $limit;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setBulkDocsLimit($bulkDocsLimit) {
    $this->bulkDocsLimit = $bulkDocsLimit;
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

  /**
   * {@inheritdoc}
   */
  public function getLimit() {
    return $this->limit;
  }

  /**
   * {@inheritdoc}
   */
  public function getBulkDocsLimit() {
    return $this->bulkDocsLimit;
  }

}
