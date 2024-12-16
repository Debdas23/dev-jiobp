<?php

namespace Drupal\entity_track;

/**
 * A collector class for entity track storage services.
 */
class EntityTrackStorageCollector implements EntityTrackStorageInterface {

  /**
   * Array of all registered storage services.
   *
   * @var \Drupal\entity_track\EntityTrackStorageInterface[]
   */
  protected $storage;

  /**
   * Adds an entity track storage service.
   *
   * @param \Drupal\entity_track\EntityTrackStorageInterface $storage
   *   The storage service.
   */
  public function addStorage(EntityTrackStorageInterface $storage) {
    $this->storage[] = $storage;
  }

  /**
   * Delete tracking information for a specific storage service.
   */
  public function deleteTrackingInformation() {
    foreach ($this->storage as $service) {
      $service->deleteTrackingInformation();
    }
  }

}
