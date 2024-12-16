<?php

namespace Drupal\entity_track\Commands;

use Drush\Commands\DrushCommands;
use Drupal\entity_track\EntityTrackBatchManager;

/**
 * Entity Track drush commands.
 */
class EntityTrackCommands extends DrushCommands {

  /**
   * The Entity Track batch manager.
   *
   * @var \Drupal\entity_track\EntityTrackBatchManager
   */
  protected $batchManager;

  /**
   * Creates a EntityTrackCommands object.
   *
   * @param \Drupal\entity_track\EntityTrackBatchManager $batch_manager
   *   The entity usage batch manager.
   */
  public function __construct(EntityTrackBatchManager $batch_manager) {
    parent::__construct();
    $this->batchManager = $batch_manager;
  }

  /**
   * Recreate tracking information.
   *
   * @command entity-track:recreate
   * @aliases et-r,entity-track-recreate
   */
  public function recreate() {
    $this->batchManager->recreate();
    drush_backend_batch_process();
  }

}
