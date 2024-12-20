<?php

namespace Drupal\entity_usage;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\entity_track\EntityTrackBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base implementation for track plugins.
 */
abstract class EntityUsageBase extends EntityTrackBase {

  /**
   * The usage tracking service.
   *
   * @var \Drupal\entity_usage\EntityUsageInterface
   */
  protected $usageService;

  /**
   * The EntityRepository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The usage config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $usageConfig;

  /**
   * Plugin constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\entity_usage\EntityUsageInterface $usage_service
   *   The usage storage service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The EntityTypeManager service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The EntityFieldManager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The EntityRepositoryInterface service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityUsageInterface $usage_service, EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager, ConfigFactoryInterface $config_factory, EntityRepositoryInterface $entity_repository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $entity_field_manager, $config_factory, $entity_repository);
    $this->configuration += $this->defaultConfiguration();
    $this->usageService = $usage_service;
    $this->entityRepository = $entity_repository;
    $this->usageConfig = $config_factory->get('entity_usage.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_usage.usage'),
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('config.factory'),
      $container->get('entity.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function trackOnEntityCreation(EntityInterface $source_entity) {
    $trackable_field_types = $this->getApplicableFieldTypes();
    $fields = array_keys($this->getReferencingFields($source_entity, $trackable_field_types));
    foreach ($fields as $field_name) {
      if ($source_entity->hasField($field_name) && !$source_entity->{$field_name}->isEmpty()) {
        /** @var \Drupal\Core\Field\FieldItemInterface $field_item */
        foreach ($source_entity->{$field_name} as $field_item) {
          // The entity is being created with value on this field, so we just
          // need to add a tracking record.
          $target_entities = $this->getTargetEntities($field_item);
          foreach ($target_entities as $target_entity) {
            list($target_type, $target_id) = explode("|", $target_entity);
            $source_vid = ($source_entity instanceof RevisionableInterface && $source_entity->getRevisionId()) ? $source_entity->getRevisionId() : 0;
            $this->usageService->registerUsage($target_id, $target_type, $source_entity->id(), $source_entity->getEntityTypeId(), $source_entity->language()->getId(), $source_vid, $this->pluginId, $field_name);
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function trackOnEntityUpdate(EntityInterface $source_entity) {
    $trackable_field_types = $this->getApplicableFieldTypes();
    $fields = array_keys($this->getReferencingFields($source_entity, $trackable_field_types));
    foreach ($fields as $field_name) {
      if (($source_entity instanceof RevisionableInterface) &&
        $source_entity->getRevisionId() != $source_entity->original->getRevisionId() &&
        $source_entity->hasField($field_name) &&
        !$source_entity->{$field_name}->isEmpty()) {

        $this->trackOnEntityCreation($source_entity);
        return;
      }

      // We are updating an existing revision, compare target entities to see if
      // we need to add or remove tracking records.
      $current_targets = [];
      if ($source_entity->hasField($field_name) && !$source_entity->{$field_name}->isEmpty()) {
        foreach ($source_entity->{$field_name} as $field_item) {
          $target_entities = $this->getTargetEntities($field_item);
          foreach ($target_entities as $target_entity) {
            $current_targets[] = $target_entity;
          }
        }
      }

      $original_targets = [];
      if ($source_entity->original->hasField($field_name) && !$source_entity->original->{$field_name}->isEmpty()) {
        foreach ($source_entity->original->{$field_name} as $field_item) {
          $target_entities = $this->getTargetEntities($field_item);
          foreach ($target_entities as $target_entity) {
            $original_targets[] = $target_entity;
          }
        }
      }

      // If a field references the same target entity, we record only one usage.
      $original_targets = array_unique($original_targets);
      $current_targets = array_unique($current_targets);

      $added_ids = array_diff($current_targets, $original_targets);
      $removed_ids = array_diff($original_targets, $current_targets);

      foreach ($added_ids as $added_entity) {
        list($target_type, $target_id) = explode('|', $added_entity);
        $source_vid = ($source_entity instanceof RevisionableInterface && $source_entity->getRevisionId()) ? $source_entity->getRevisionId() : 0;
        $this->usageService->registerUsage($target_id, $target_type, $source_entity->id(), $source_entity->getEntityTypeId(), $source_entity->language()->getId(), $source_vid, $this->pluginId, $field_name);
      }
      foreach ($removed_ids as $removed_entity) {
        list($target_type, $target_id) = explode('|', $removed_entity);
        $source_vid = ($source_entity instanceof RevisionableInterface && $source_entity->getRevisionId()) ? $source_entity->getRevisionId() : 0;
        $this->usageService->registerUsage($target_id, $target_type, $source_entity->id(), $source_entity->getEntityTypeId(), $source_entity->language()->getId(), $source_vid, $this->pluginId, $field_name, 0);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function trackOnEntityDelete(EntityInterface $source_entity, $type) {
    // When an entity is being deleted the logic is much simpler and we just
    // delete the records that affect this entity both as target and source.
    switch ($type) {
      case 'revision':
        $this->usageService->deleteBySourceEntity($source_entity->id(), $source_entity->getEntityTypeId(), NULL, $source_entity->getRevisionId());
        break;

      case 'translation':
        $this->usageService->deleteBySourceEntity($source_entity->id(), $source_entity->getEntityTypeId(), $source_entity->language()->getId());
        break;

      case 'default':
        $this->usageService->deleteBySourceEntity($source_entity->id(), $source_entity->getEntityTypeId());
        $this->usageService->deleteByTargetEntity($source_entity->id(), $source_entity->getEntityTypeId());
        break;
    }
  }

  /**
   * Retrieve the target entity(ies) from a field item value.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The field item to get the target entity(ies) from.
   *
   * @return string[]
   *   An indexed array of strings where each target entity type and ID are
   *   concatenated with a "|" character. Will return an empty array if no
   *   target entity could be retrieved from the received field item value.
   */
  abstract public function getTargetEntities(FieldItemInterface $item);

}
