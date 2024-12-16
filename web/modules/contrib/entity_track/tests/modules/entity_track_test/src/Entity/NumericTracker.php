<?php

namespace Drupal\entity_track_test\Entity;

use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines the numeric_tracker entity.
 *
 * @ContentEntityType(
 *   id = "numeric_tracker",
 *   label = @Translation("Numeric tracker"),
 *   base_table = "numeric_tracker",
 *   translatable = FALSE,
 *   revisionable = FALSE,
 *   entity_keys = {
 *     "id" = "id",
 *   }
 * )
 */
class NumericTracker extends ContentEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['entity_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Entity ID'))
      ->setDescription(t('The type of the tracked entity.'));

    $fields['entity_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Entity ID'))
      ->setDescription(t('The ID of the tracked entity.'));

    $fields['entity_langcode'] = BaseFieldDefinition::create('language')
      ->setLabel(t('Entity ID'))
      ->setDescription(t('The langcode of the tracked entity.'));

    $fields['field_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Field'))
      ->setDescription(t('The field name where the tracking information was found.'));

    return $fields;
  }

}
