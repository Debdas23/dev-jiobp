<?php

namespace Drupal\entity_track;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines the interface for entity_track plugins.
 *
 * Tracking plugins can be implemented by other modules to track information
 * stored in entities. Examples would be:
 *
 * - Tracking relations between entities (implemented by the entity_usage
 *   module).
 * - Tracking links in content (implemented by the link_tracker module).
 */
interface EntityTrackInterface extends PluginInspectionInterface {

  /**
   * Returns the tracking method unique id.
   *
   * @return string
   *   The tracking method id.
   */
  public function getId();

  /**
   * Returns the tracking method label.
   *
   * @return string
   *   The tracking method label.
   */
  public function getLabel();

  /**
   * Returns the tracking method description.
   *
   * @return string
   *   The tracking method description, or an empty string is non defined.
   */
  public function getDescription();

  /**
   * Returns the field types this plugin is capable of tracking.
   *
   * @return array
   *   An indexed array of field type names, as defined in the plugin's
   *   annotation under the key "field_types".
   */
  public function getApplicableFieldTypes();

  /**
   * Track updates on the creation of entities.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   */
  public function trackOnEntityCreation(EntityInterface $entity);

  /**
   * Track updates on the edition of entities.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   */
  public function trackOnEntityUpdate(EntityInterface $entity);

  /**
   * Track updates on the deletion of entities.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param string $type
   *   What type of deletion is being performed:
   *   - default: The main entity (default language, default revision) is being
   *     deleted (delete also other languages and revisions).
   *   - translation: Only one translation is being deleted.
   *   - revision: Only one revision is being deleted.
   */
  public function trackOnEntityDelete(EntityInterface $entity, $type);

  /**
   * Retrieve fields of the given types on an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object.
   * @param string[] $field_types
   *   A list of field types.
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface[]
   *   An array of fields that could reference to other content entities.
   */
  public function getReferencingFields(EntityInterface $entity, array $field_types);

}
