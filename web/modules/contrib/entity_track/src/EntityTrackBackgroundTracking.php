<?php

namespace Drupal\entity_track;

use Drupal\Core\DestructableInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Track entity usage in the background.
 */
class EntityTrackBackgroundTracking implements DestructableInterface {

  /**
   * Key for the insert operation.
   */
  const OPERATION_INSERT = 'insert';

  /**
   * Key for the update operation.
   */
  const OPERATION_UPDATE = 'update';

  /**
   * Key for the predelete operation.
   */
  const OPERATION_PREDELETE = 'predelete';

  /**
   * Key for the translation delete operation.
   */
  const OPERATION_TRANSLATION_DELETE = 'translation_delete';

  /**
   * Key for the revision delete operation.
   */
  const OPERATION_REVISION_DELETE = 'revision_delete';

  /**
   * A list of entities that should be tracked per operation.
   *
   * The nested array is keyed by operation and has arrays of entities to index
   * for the specific operation as values.
   *
   * @var \Drupal\Core\Entity\EntityInterface[][]
   */
  protected $entity_operations = [];

  /**
   * The entity track manager.
   *
   * @var \Drupal\entity_track\EntityTrackManager
   */
  protected $entityTrackManager;

  /**
   * Constructs a new background tracking service.
   *
   * @param \Drupal\entity_track\EntityTrackManager $entity_track_update_manager
   *   The entity track manager.
   */
  public function __construct(EntityTrackManager $entity_track_manager) {
    $this->entityTrackManager = $entity_track_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function destruct() {
    foreach ($this->entity_operations as $operation => $entities) {
      foreach ($entities as $entity) {
        switch ($operation) {
          case static::OPERATION_INSERT:
            $this->entityTrackManager->trackUpdateOnCreation($entity);
            break;

          case static::OPERATION_UPDATE:
            $this->entityTrackManager->trackUpdateOnEdition($entity);
            break;

          case static::OPERATION_PREDELETE:
            $this->entityTrackManager->trackUpdateOnDeletion($entity);
            break;

          case static::OPERATION_TRANSLATION_DELETE:
            $this->entityTrackManager->trackUpdateOnDeletion($entity, 'translation');
            break;

          case static::OPERATION_REVISION_DELETE:
            $this->entityTrackManager->trackUpdateOnDeletion($entity, 'revision');
            break;
        }
      }
    }
  }

  /**
   * Add an entity for background tracking.
   *
   * @param string $operation
   *   The operation causing changes in entity usage.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to track.
   */
  public function registerEntity($operation, EntityInterface $entity) {
    $this->entity_operations[$operation][] = clone $entity;
  }

}
