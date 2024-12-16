<?php

namespace Drupal\entity_track;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\TranslatableInterface;

/**
 * Class EntityTrackManager.
 *
 * @package Drupal\entity_track
 */
class EntityTrackManager {

  /**
   * The plugin manager.
   *
   * @var \Drupal\entity_track\EntityTrackPluginManager
   */
  protected $pluginManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * EntityTrackManager constructor.
   *
   * @param \Drupal\entity_track\EntityTrackPluginManager $plugin_manager
   *   The plugin manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(EntityTrackPluginManager $plugin_manager, ConfigFactoryInterface $config_factory) {
    $this->pluginManager = $plugin_manager;
    $this->config = $config_factory->get('entity_track.settings');

  }

  /**
   * Track updates on creation of entities.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity we are dealing with.
   */
  public function trackUpdateOnCreation(EntityInterface $entity) {
    if (!$this->allowEntityTracking($entity)) {
      return;
    }

    // Call all plugins that want to track entity usages. We need to call this
    // for all translations as well since Drupal stores new revisions for all
    // translations by default when saving an entity.
    if ($entity instanceof TranslatableInterface) {
      foreach ($entity->getTranslationLanguages() as $translation_language) {
        if ($entity->hasTranslation($translation_language->getId())) {
          /** @var \Drupal\Core\Entity\EntityInterface $translation */
          $translation = $entity->getTranslation($translation_language->getId());
          foreach ($this->getEnabledPlugins() as $plugin) {
            $plugin->trackOnEntityCreation($translation);
          }
        }
      }
    }
    else {
      // Not translatable, just call the plugins with the entity itself.
      foreach ($this->getEnabledPlugins() as $plugin) {
        $plugin->trackOnEntityCreation($entity);
      }
    }
  }

  /**
   * Track updates on edit / update of entities.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity we are dealing with.
   */
  public function trackUpdateOnEdition(EntityInterface $entity) {
    if (!$this->allowEntityTracking($entity)) {
      return;
    }

    // Call all plugins that want to track entity usages. We need to call this
    // for all translations as well since Drupal stores new revisions for all
    // translations by default when saving an entity.
    if ($entity instanceof TranslatableInterface) {
      foreach ($entity->getTranslationLanguages() as $translation_language) {
        if ($entity->hasTranslation($translation_language->getId())) {
          /** @var \Drupal\Core\Entity\ContentEntityInterface $translation */
          $translation = $entity->getTranslation($translation_language->getId());
          foreach ($this->getEnabledPlugins() as $plugin) {
            $plugin->trackOnEntityUpdate($translation);
          }
        }
      }
    }
    else {
      // Not translatable, just call the plugins with the entity itself.
      foreach ($this->getEnabledPlugins() as $plugin) {
        $plugin->trackOnEntityUpdate($entity);
      }
    }

  }

  /**
   * Track updates on deletion of entities.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity we are dealing with.
   * @param string $type
   *   What type of deletion is being performed:
   *   - default: The main entity (default language, default revision) is being
   *   deleted (delete also other languages and revisions).
   *   - translation: Only one translation is being deleted.
   *   - revision: Onlyone revision is being deleted.
   *
   * @throws \InvalidArgumentException
   */
  public function trackUpdateOnDeletion(EntityInterface $entity, $type = 'default') {
    if (!in_array($type, ['default', 'translation', 'revision'], TRUE)) {
      // We only accept one of the above mentioned types.
      throw new \InvalidArgumentException('EntityTrackManager::trackUpdateOnDeletion called with unkown deletion type: ' . $type);
    }

    foreach ($this->getEnabledPlugins() as $plugin) {
      $plugin->trackOnEntityDelete($entity, $type);
    }
  }

  /**
   * Check if tracking is enabled for an entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity object.
   *
   * @return bool
   *   Whether the entity can be tracked or not.
   */
  protected function allowEntityTracking(EntityInterface $entity) {
    $allow_tracking = FALSE;
    $entity_type = $entity->getEntityType();
    $enabled_entity_types = $this->config->get('track_enabled_entity_types');
    if (!is_array($enabled_entity_types) && ($entity_type->entityClassImplements('\Drupal\Core\Entity\ContentEntityInterface'))) {
      // When no settings are defined, track all content entities by default.
      $allow_tracking = TRUE;
    }
    elseif (is_array($enabled_entity_types) && in_array($entity_type->id(), $enabled_entity_types, TRUE)) {
      $allow_tracking = TRUE;
    }
    return $allow_tracking;
  }

  /**
   * Get the enabled tracking plugins, all plugins are enabled by default.
   *
   * @return \Drupal\entity_track\EntityTrackInterface[]
   *   The enabled plugin instances.
   */
  protected function getEnabledPlugins() {
    $all_plugin_ids = array_keys($this->pluginManager->getDefinitions());
    $enabled_plugins = $this->config->get('track_enabled_plugins');
    $enabled_plugin_ids = is_array($enabled_plugins) ? $enabled_plugins : $all_plugin_ids;

    $plugins = [];
    foreach (array_intersect($all_plugin_ids, $enabled_plugin_ids) as $plugin_id) {
      /** @var EntityTrackInterface $instance */
      $plugins[$plugin_id] = $this->pluginManager->createInstance($plugin_id);
    }

    return $plugins;
  }

}
