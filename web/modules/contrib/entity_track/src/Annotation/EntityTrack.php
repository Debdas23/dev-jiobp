<?php

namespace Drupal\entity_track\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a track plugin annotation object.
 *
 * @see hook_entity_track_info_alter()
 *
 * @Annotation
 */
class EntityTrack extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the tracking method.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * A brief description of the tracking method.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description = '';

  /**
   * The field types that this plugin is able to track.
   *
   * @var string[]
   */
  public $field_types = [];

}
