<?php

namespace Drupal\entity_usage;

use Drupal\entity_track\EntityTrackInterface;

/**
 * Defines the interface for tracking plugins that embed entities in WYSIWYG.
 */
interface EmbedTrackInterface extends EntityTrackInterface {

  /**
   * Parse an HTML snippet looking for embedded entities.
   *
   * @param string $text
   *   The partial (X)HTML snippet to load. Invalid markup will be corrected on
   *   import.
   *
   * @return array
   *   An array of all embedded entities found, where keys are the uuids and the
   *   values are the entity types.
   */
  public function parseEntitiesFromText($text);

}
