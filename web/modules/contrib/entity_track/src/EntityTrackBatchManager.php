<?php

namespace Drupal\entity_track;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Manages Entity Track integration with Batch API.
 */
class EntityTrackBatchManager implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity usage configuration.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Creates a EntityTrackBatchManager object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, TranslationInterface $string_translation, ConfigFactoryInterface $config_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->stringTranslation = $string_translation;
    $this->config = $config_factory->get('entity_track.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('string_translation'),
      $container->get('config.factory')
    );
  }

  /**
   * Generate a batch to recreate the tracking information for all entities.
   */
  public function recreate() {
    $batch = $this->generateBatch();
    batch_set($batch);
  }

  /**
   * Create a batch to process the entity types in bulk.
   *
   * @return array
   *   The batch array.
   */
  public function generateBatch() {
    // Delete current tracking information.
    \Drupal::service('entity_track.storage_collector')->deleteTrackingInformation();

    // Generate tracking information per entity type.
    $operations = [];
    $to_track = $to_track = $this->config->get('track_enabled_entity_types');
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
      // Only look for entities enabled for tracking on the settings form.
      $track_this_entity_type = FALSE;
      if (!is_array($to_track) && ($entity_type->entityClassImplements('\Drupal\Core\Entity\ContentEntityInterface'))) {
        // When no settings are defined, track all content entities by default,
        // except for Files and Users.
        if (!in_array($entity_type_id, ['file', 'user'])) {
          $track_this_entity_type = TRUE;
        }
      }
      elseif (is_array($to_track) && in_array($entity_type_id, $to_track, TRUE)) {
        $track_this_entity_type = TRUE;
      }
      if ($track_this_entity_type) {
        $operations[] = ['\Drupal\entity_track\EntityTrackBatchManager::trackingBatchWorker', [$entity_type_id]];
      }
    }

    $batch = [
      'operations' => $operations,
      'finished' => '\Drupal\entity_track\EntityTrackBatchManager::batchFinished',
      'title' => $this->t('Updating tracking information.'),
      'progress_message' => $this->t('Processed @current of @total entity types.'),
      'error_message' => $this->t('This batch encountered an error.'),
    ];

    return $batch;
  }

  /**
   * Batch operation worker for recreating tracking information for entities.
   *
   * @param string $entity_type_id
   *   The entity type id, for example 'node'.
   * @param mixed $context
   *   Batch context.
   */
  public static function trackingBatchWorker($entity_type_id, &$context) {
    $entity_storage = \Drupal::entityTypeManager()->getStorage($entity_type_id);
    $entity_type = \Drupal::entityTypeManager()->getDefinition($entity_type_id);
    $entity_type_key = $entity_type->getKey('id');

    if (empty($context['sandbox']['total'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_id'] = -1;
      $context['sandbox']['total'] = (int) $entity_storage->getQuery()
        ->accessCheck(FALSE)
        ->count()
        ->execute();
    }

    $entity_ids = $entity_storage->getQuery()
      ->condition($entity_type_key, $context['sandbox']['current_id'], '>')
      ->range(0, 1)
      ->accessCheck(FALSE)
      ->sort($entity_type_key)
      ->execute();

    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $entity_storage->load(reset($entity_ids));
    if ($entity) {
      if ($entity->getEntityType()->isRevisionable()) {
        // Track all revisions and translations of the entity.
        $result = $entity_storage->getQuery()->allRevisions()
          ->condition($entity->getEntityType()->getKey('id'), $entity->id())
          ->sort($entity->getEntityType()->getKey('revision'), 'DESC')
          ->execute();
        $revision_ids = array_keys($result);

        foreach ($revision_ids as $revision_id) {
          /** @var \Drupal\Core\Entity\EntityInterface $entity_revision */
          if (!$entity_revision = $entity_storage->loadRevision($revision_id)) {
            continue;
          }

          \Drupal::service('entity_track.manager')->trackUpdateOnCreation($entity_revision);
        }
      }
      else {
        // Sources are tracked as if they were new entities.
        \Drupal::service('entity_track.manager')->trackUpdateOnCreation($entity);
      }

      $context['sandbox']['progress']++;
      $context['sandbox']['current_id'] = $entity->id();
      $context['results'][] = $entity_type_id . ':' . $entity->id();
    }

    if ($context['sandbox']['progress'] < $context['sandbox']['total']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['total'];
    }
    else {
      $context['finished'] = 1;
    }

    $context['message'] = t('Updating entity usage for @entity_type: @current of @total', [
      '@entity_type' => $entity_type_id,
      '@current' => $context['sandbox']['progress'],
      '@total' => $context['sandbox']['total'],
    ]);
  }

  /**
   * Finish callback for our batch processing.
   *
   * @param bool $success
   *   Whether the batch completed successfully.
   * @param array $results
   *   The results array.
   * @param array $operations
   *   The operations array.
   */
  public static function batchFinished($success, array $results, array $operations) {
    if ($success) {
      \Drupal::messenger()->addMessage(t('Recreated tracking information for @count entities.', ['@count' => count($results)]));
    }
    else {
      // An error occurred.
      // $operations contains the operations that remained unprocessed.
      $error_operation = reset($operations);
      \Drupal::messenger()->addMessage(
        t('An error occurred while processing @operation with arguments : @args',
          [
            '@operation' => $error_operation[0],
            '@args' => print_r($error_operation[0], TRUE),
          ]
        )
      );
    }
  }

}
