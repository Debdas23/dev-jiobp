<?php

namespace Drupal\entity_track;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base implementation for track plugins.
 */
abstract class EntityTrackBase extends PluginBase implements EntityTrackInterface, ContainerFactoryPluginInterface {

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The track config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Plugin constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The EntityTypeManager service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The EntityFieldManager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configuration += $this->defaultConfiguration();
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->config = $config_factory->get('entity_track.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return $this->pluginDefinition['id'];
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->pluginDefinition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->pluginDefinition['description'] ?: '';
  }

  /**
   * {@inheritdoc}
   */
  public function getApplicableFieldTypes() {
    return $this->pluginDefinition['field_types'] ?: [];
  }

  /**
   * {@inheritdoc}
   */
  public function getReferencingFields(EntityInterface $entity, array $field_types) {
    $entity_type_id = $entity->getEntityTypeId();

    $all_fields_on_bundle = $this->entityFieldManager->getFieldDefinitions($entity_type_id, $entity->bundle());

    $referencing_fields_on_bundle = [];
    foreach ($all_fields_on_bundle as $field_name => $field) {
      if (in_array($field->getType(), $field_types)) {
        $referencing_fields_on_bundle[$field_name] = $field;
      }
    }

    if (!$this->config->get('track_enabled_base_fields')) {
      foreach ($referencing_fields_on_bundle as $key => $referencing_field_on_bundle) {
        if ($referencing_field_on_bundle->getFieldStorageDefinition()->isBaseField()) {
          unset($referencing_fields_on_bundle[$key]);
        }
      }
    }

    return $referencing_fields_on_bundle;
  }

}
