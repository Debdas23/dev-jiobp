<?php

namespace Drupal\entity_track_test;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\entity_track\EntityTrackStorageInterface;

/**
 * Helper service to manage the storage for numeric_tracker entities.
 */
class NumericTrackerManager implements EntityTrackStorageInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new TrackTestStorage object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_manager) {
    $this->entityTypeManager = $entity_manager;
  }

  /**
   * Register a numeric field in an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The tracked entity.
   * @param string $field_name
   *   The name of the tracked field.
   */
  public function register($entity, $field_name) {
    $storage = $this->entityTypeManager->getStorage('numeric_tracker');
    $numeric_tracker = $storage->create([
      'entity_type' => $entity->getEntityTypeId(),
      'entity_id' => $entity->id(),
      'entity_langcode' => $entity->language()->getId(),
      'field_name' => $field_name,
    ]);
    $numeric_tracker->save();
  }

  /**
   * Delete all records for a given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The deleted entity.
   */
  public function deleteByEntity($entity) {
    $storage = $this->entityTypeManager->getStorage('numeric_tracker');
    $records = $storage->loadByProperties([
      'entity_type' => $entity->getEntityTypeId(),
      'entity_id' => $entity->id(),
    ]);
    $storage->delete($records);
  }

  /**
   * Delete all records for a given entity translation.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The deleted entity translation.
   */
  public function deleteByTranslation($entity) {
    $storage = $this->entityTypeManager->getStorage('numeric_tracker');
    $records = $storage->loadByProperties([
      'entity_type' => $entity->getEntityTypeId(),
      'entity_id' => $entity->id(),
      'entity_langcode' => $entity->language()->getId(),
    ]);
    $storage->delete($records);
  }

  /**
   * Delete all records for a given entity type and field name.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $field_name
   *   The name of the deleted field.
   */
  public function deleteByField($entity_type, $field_name) {
    $storage = $this->entityTypeManager->getStorage('numeric_tracker');
    $records = $storage->loadByProperties([
      'entity_type' => $entity_type,
      'field_name' => $field_name,
    ]);
    $storage->delete($records);
  }

  /**
   * Delete all records for a given entity and field name.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The tracked entity.
   * @param string $field_name
   *   The name of the deleted field.
   */
  public function deleteByEntityAndField($entity, $field_name) {
    $storage = $this->entityTypeManager->getStorage('numeric_tracker');
    $tracked_field = $storage->loadByProperties([
      'entity_type' => $entity->getEntityTypeId(),
      'entity_id' => $entity->id(),
      'entity_langcode' => $entity->language()->getId(),
      'field_name' => $field_name,
    ]);
    $storage->delete($tracked_field);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteTrackingInformation() {
    $storage = $this->entityTypeManager->getStorage('numeric_tracker');
    $records = $storage->loadMultiple();
    $storage->delete($records);
  }

  /**
   * Load all records for a given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The tracked entity.
   *
   * @return \Drupal\entity_track_test\Entity\NumericTracker[]
   *   The tracking records.
   */
  public function loadByEntity($entity) {
    $storage = $this->entityTypeManager->getStorage('numeric_tracker');
    /** @var \Drupal\entity_track_test\Entity\NumericTracker[] $records */
    $records = $storage->loadByProperties([
      'entity_type' => $entity->getEntityTypeId(),
      'entity_id' => $entity->id(),
    ]);
    return $records;
  }

  /**
   * Load all records for a given entity and field name.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The tracked entity.
   * @param string $field_name
   *   The name of the field.
   *
   * @return \Drupal\entity_track_test\Entity\NumericTracker[]
   *   The tracking records.
   */
  public function loadByEntityAndField($entity, $field_name) {
    $storage = $this->entityTypeManager->getStorage('numeric_tracker');
    /** @var \Drupal\entity_track_test\Entity\NumericTracker[] $records */
    $records = $storage->loadByProperties([
      'entity_type' => $entity->getEntityTypeId(),
      'entity_id' => $entity->id(),
      'field_name' => $field_name,
    ]);
    return $records;
  }

}
