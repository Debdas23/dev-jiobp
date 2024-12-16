<?php

namespace Drupal\entity_track_test\Plugin\EntityTrack\Track;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\Core\Entity\TranslatableRevisionableInterface;
use Drupal\entity_track\EntityTrackBase;
use Drupal\entity_track_test\NumericTrackerManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Test plugin that tracks numeric fields in entities.
 *
 * @EntityTrack(
 *   id = "numeric",
 *   label = @Translation("Numeric"),
 *   field_types = {"string", "text"},
 * )
 */
class Numeric extends EntityTrackBase {

  /**
   * The numeric field tracking storage.
   *
   * @var \Drupal\entity_track_test\NumericTrackerManager
   */
  protected $trackingStorage;

  /**
   * Plugin constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The EntityTypeManager service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The EntityFieldManager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\entity_track_test\NumericTrackerManager $tracking_storage
   *   The numeric field tracking storage.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager, ConfigFactoryInterface $config_factory, NumericTrackerManager $tracking_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $entity_field_manager, $config_factory);
    $this->trackingStorage = $tracking_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('config.factory'),
      $container->get('entity_track_test.numeric_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function trackOnEntityCreation(EntityInterface $entity) {
    $trackable_field_types = $this->getApplicableFieldTypes();
    $fields = array_keys($this->getReferencingFields($entity, $trackable_field_types));
    foreach ($fields as $field_name) {
      if ($entity->hasField($field_name) && !$entity->{$field_name}->isEmpty()) {
        /** @var \Drupal\Core\Field\FieldItemInterface $field_item */
        foreach ($entity->{$field_name} as $field_item) {
          $item_value = $field_item->getValue();
          // For our test we track all fields that contain only numbers.
          if (!empty($item_value['value']) && is_numeric($item_value['value'])) {
            $this->trackingStorage->register($entity, $field_name);
            // Only record 1 record per field.
            break;
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function trackOnEntityUpdate(EntityInterface $entity) {
    $trackable_field_types = $this->getApplicableFieldTypes();
    $fields = array_keys($this->getReferencingFields($entity, $trackable_field_types));
    foreach ($fields as $field_name) {

      // Load the original entity to compare.
      $original = $entity->original;
      if ($original instanceof TranslatableInterface) {
        $langcode = $entity->language()->getId();
        $original = $original->hasTranslation($langcode) ? $original->getTranslation($langcode) : NULL;
      }

      // Check if the original entity was tracked.
      $is_tracked = FALSE;
      if ($original && $original->hasField($field_name) && !$original->{$field_name}->isEmpty()) {
        foreach ($original->{$field_name} as $field_item) {
          $item_value = $field_item->getValue();
          if (!empty($item_value['value']) && is_numeric($item_value['value'])) {
            $is_tracked = TRUE;
            break;
          }
        }
      }

      if ($entity->hasField($field_name) && !$entity->{$field_name}->isEmpty()) {
        foreach ($entity->{$field_name} as $field_item) {
          $item_value = $field_item->getValue();

          // For our test we track all fields that contain only numbers.
          if (!empty($item_value['value']) && is_numeric($item_value['value'])) {
            // If usage was already recorded we can skip don't have to do
            // anything.
            if ($is_tracked) {
              break;
            }
            $this->trackingStorage->register($entity, $field_name);
            break;
          }

          if ($is_tracked) {
            // If usage was recorded and no longer nessecary, we need to remove
            // it.
            $this->trackingStorage->deleteByEntityAndField($entity, $field_name);
          }
        }
      }

    }
  }

  /**
   * {@inheritdoc}
   */
  public function trackOnEntityDelete(EntityInterface $entity, $type) {
    // When an entity is being deleted the logic is much simpler and we just
    // delete the records that affect this entity.
    switch ($type) {
      case 'translation':
        $this->trackingStorage->deleteByTranslation($entity);
        break;

      case 'revision':
        if ($entity instanceof TranslatableRevisionableInterface && $entity->isDefaultRevision()) {
          $this->trackingStorage->deleteByEntity($entity);
        }
        break;

      default:
        $this->trackingStorage->deleteByEntity($entity);
        break;
    }
  }

}
