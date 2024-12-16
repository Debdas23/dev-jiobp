<?php

/**
 * @file
 * Defines drush commands for the Entity Track module.
 */

/**
 * Implements hook_drush_command().
 */
function entity_track_drush_command() {
  $items = [];

  $items['entity-track-recreate'] = [
    'description' => 'Recreate tracking information.',
    'aliases' => ['et-r'],
  ];

  return $items;
}

/**
 * Command callback for 'entity-track-recreate'.
 */
function drush_entity_track_recreate() {
  \Drupal::service('entity_track.batch_manager')->recreate();
  drush_backend_batch_process();
}
