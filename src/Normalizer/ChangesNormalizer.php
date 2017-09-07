<?php

namespace Drupal\replication\Normalizer;

use Drupal\serialization\Normalizer\NormalizerBase;

class ChangesNormalizer extends NormalizerBase {

  protected $supportedInterfaceOrClass = ['Drupal\replication\Changes\ChangesInterface'];

  /**
   * {@inheritdoc}
   */
  public function normalize($changes, $format = NULL, array $context = []) {
    /** @var \Drupal\replication\Changes\ChangesInterface $changes */
    if (isset($context['query']['filter'])) {
      $changes->filter($context['query']['filter']);
    }
    if (isset($context['query']['parameters'])) {
      $changes->parameters($context['query']['parameters']);
    }
    if (isset($context['query']['limit'])) {
      $changes->setLimit($context['query']['limit']);
    }
    $since = (isset($context['query']['since']) && is_numeric($context['query']['since'])) ? $context['query']['since'] : 0;
    $changes->setSince($since);

    $results = array_map(function ($result) use ($format, $context) {
      // Normalize the doc if it's there. It would be easier to check if
      // includeDocs is TRUE but there is no accessor for that property. Could
      // add one.
      if (isset($result['doc'])) {
        $result['doc'] = $this->serializer->normalize($result['doc'], $format, $context);
      }

      return $result;
    }, $changes->getNormal());

    $last_result = end($results);
    $last_seq = isset($last_result['seq']) ? $last_result['seq'] : 0;

    return [
      'last_seq' => $last_seq,
      'results' => $results,
    ];
  }

}
