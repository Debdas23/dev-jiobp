<?php

namespace Drupal\entity_track_test\Plugin\EntityTrack\Track;

use Drupal\Core\Entity\EntityInterface;
use Drupal\entity_track\EntityTrackBase;

/**
 * Test plugin that doesn't really do anything.
 *
 * @EntityTrack(
 *   id = "foo",
 *   label = @Translation("Foo"),
 *   field_types = {"string", "text"},
 * )
 */
class Foo extends EntityTrackBase {

  /**
   * {@inheritdoc}
   */
  public function trackOnEntityCreation(EntityInterface $entity) {
  }

  /**
   * {@inheritdoc}
   */
  public function trackOnEntityUpdate(EntityInterface $entity) {
  }

  /**
   * {@inheritdoc}
   */
  public function trackOnEntityDelete(EntityInterface $entity, $type) {
  }

}
