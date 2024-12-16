<?php

namespace Drupal\entity_track\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_track\EntityTrackPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to configure entity_track settings.
 */
class EntityTrackSettingsForm extends ConfigFormBase {

  /**
   * The Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The plugin manager service.
   *
   * @var \Drupal\entity_track\EntityTrackPluginManager
   */
  protected $pluginManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, EntityTrackPluginManager $plugin_manager) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entity_type_manager;
    $this->pluginManager = $plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.entity_track.track')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_track_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['entity_track.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('entity_track.settings');
    $all_entity_types = $this->entityTypeManager->getDefinitions();
    $content_entity_types = [];

    // Filter the entity types.
    /** @var \Drupal\Core\Entity\EntityTypeInterface[] $entity_type_options */
    $entity_type_options = [];
    foreach ($all_entity_types as $entity_type) {
      if (($entity_type instanceof ContentEntityTypeInterface)) {
        $content_entity_types[$entity_type->id()] = $entity_type->getLabel();
      }
      $entity_type_options[$entity_type->id()] = $entity_type->getLabel();
    }
    // Files and users shouldn't be tracked by default.
    unset($content_entity_types['file']);
    unset($content_entity_types['user']);

    // Entity types for which tracking is enabled.
    $form['track_enabled_entity_types'] = [
      '#type' => 'details',
      '#open' => FALSE,
      '#title' => $this->t('Enabled entity types'),
      '#description' => $this->t('Check which entity types should be tracked.'),
      '#tree' => TRUE,
    ];
    $form['track_enabled_entity_types']['entity_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Source entity types'),
      '#options' => $entity_type_options,
      // If no custom settings exist, content entities are tracked by default.
      '#default_value' => $config->get('track_enabled_entity_types') ?: array_keys($content_entity_types),
      '#required' => TRUE,
    ];

    // Enabled tracking plugins.
    $form['track_enabled_plugins'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Enabled tracking plugins'),
      '#description' => $this->t('The following plugins were found in the system and can provide usage tracking. Check all plugins that should be active.'),
      '#tree' => TRUE,
    ];
    $plugins = $this->pluginManager->getDefinitions();
    $plugin_options = [];
    foreach ($plugins as $plugin) {
      $plugin_options[$plugin['id']] = $plugin['label'];
    }
    $form['track_enabled_plugins']['plugins'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Tracking plugins'),
      '#options' => $plugin_options,
      '#default_value' => $config->get('track_enabled_plugins') ?: array_keys($plugin_options),
      '#required' => TRUE,
    ];
    // Add descriptions to all plugins that defined it.
    foreach ($plugins as $plugin) {
      if (!empty($plugin['description'])) {
        $form['track_enabled_plugins']['plugins'][$plugin['id']]['#description'] = $plugin['description'];
      }
    }

    // Miscellaneous settings.
    $form['generic_settings'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Generic'),
    ];
    $form['generic_settings']['track_enabled_base_fields'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Track referencing basefields'),
      '#description' => $this->t('If enabled, information stored in non-configurable fields (basefields) will also be tracked.'),
      '#default_value' => (bool) $config->get('track_enabled_base_fields'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->cleanValues();
    $config = $this->config('entity_track.settings');

    $config->set('track_enabled_base_fields', (bool) $form_state->getValue('track_enabled_base_fields'))
      ->set('track_enabled_entity_types', array_values(array_filter($form_state->getValue('track_enabled_entity_types')['entity_types'])))
      ->set('track_enabled_plugins', array_values(array_filter($form_state->getValue('track_enabled_plugins')['plugins'])))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
