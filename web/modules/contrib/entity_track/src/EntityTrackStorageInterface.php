<?php

namespace Drupal\entity_track;

/**
 * Interface for entity track storage services.
 *
 * Each plugin defined for entity track is responsible for storing the tracked
 * information. It is generally advised to create a storage manager service for
 * custom plugins to handle the storage. When an entity is deleted, we call a
 * method on all the services tagged with "entity_track_storage" to allow any
 * obsolete tracking information to be deleted as well.
 *
 * An example of a storage manager can be found in the entity_track_test module.
 *
 * @see \Drupal\entity_track_test\NumericTrackerManager
 */
interface EntityTrackStorageInterface {

  /**
   * Delete all tracking information.
   */
  public function deleteTrackingInformation();

}
