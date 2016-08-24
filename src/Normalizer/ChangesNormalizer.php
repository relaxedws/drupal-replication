<?php

/**
 * @file
 * Contains \Drupal\replication\Normalizer\ChangesNormalizer.
 */

namespace Drupal\replication\Normalizer;

use Drupal\serialization\Normalizer\NormalizerBase;

class ChangesNormalizer extends NormalizerBase {

  protected $supportedInterfaceOrClass = array('Drupal\replication\Changes\ChangesInterface');

  /**
   * @var string
   */
  protected $format = array('json');

  /**
   * {@inheritdoc}
   */
  public function normalize($changes, $format = NULL, array $context = array()) {
    /** @var \Drupal\replication\Changes\ChangesInterface $changes */
    $results = $changes->getNormal();
    $last_result = end($results);
    $last_seq = isset($last_result['seq']) ? $last_result['seq'] : 0;

    // Check if 'since' parameter is set.
    $since = (isset($context['query']['since']) && is_numeric($context['query']['since'])) ? $context['query']['since'] : 0;

    // Check if 'limit' parameter is set.
    $limit = (isset($context['query']['limit']) && is_numeric($context['query']['limit'])) ? $context['query']['limit'] : NULL;

    // Note that using 0 here has the same effect as 1.
    // This is specified in CouchDB documentation.
    $limit = (!is_null($limit) && $limit == 0) ? 1 : $limit;

    $filtered_results = array();
    if ($since == 0) {
      $filtered_results = $results;
    }
    else {
      foreach ($results as $result) {
        if ($result['seq'] > $since) {
          $filtered_results[] = $result;
        }
      }
    }

    // Limit the number of results if the limit is set.
    if ($limit) {
      $filtered_results = array_slice($filtered_results, 0, $limit);
    }

    return array(
      'last_seq' => $last_seq,
      'results' => $filtered_results,
    );
  }

}
