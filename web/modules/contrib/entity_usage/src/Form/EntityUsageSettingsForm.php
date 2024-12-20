<?php

namespace Drupal\entity_usage\Form;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteBuilderInterface;
use Drupal\entity_usage\Controller\ListUsageController;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to configure entity_usage settings.
 */
class EntityUsageSettingsForm extends ConfigFormBase {

  /**
   * The Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The router builder.
   *
   * @var \Drupal\Core\Routing\RouteBuilderInterface
   */
  protected $routerBuilder;

  /**
   * The Cache Render.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheRender;

  /**
   * {@inheritdoc}
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, RouteBuilderInterface $router_builder, CacheBackendInterface $cache_render) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entity_type_manager;
    $this->routerBuilder = $router_builder;
    $this->cacheRender = $cache_render;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('router.builder'),
      $container->get('cache.render')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_usage_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['entity_usage.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('entity_usage.settings');
    $all_entity_types = $this->entityTypeManager->getDefinitions();
    $content_entity_types = [];

    // Filter the entity types.
    /** @var \Drupal\Core\Entity\EntityTypeInterface[] $entity_type_options */
    $entity_type_options = [];
    $tabs_options = [];
    foreach ($all_entity_types as $entity_type) {
      if (($entity_type instanceof ContentEntityTypeInterface)) {
        $content_entity_types[$entity_type->id()] = $entity_type->getLabel();
      }
      $entity_type_options[$entity_type->id()] = $entity_type->getLabel();
      if ($entity_type->hasLinkTemplate('canonical') || $entity_type->hasLinkTemplate('edit-form')) {
        $tabs_options[$entity_type->id()] = $entity_type->getLabel();
      }
    }
    // Files and users shouldn't be tracked by default.
    unset($content_entity_types['file']);
    unset($content_entity_types['user']);

    // Tabs configuration.
    $form['local_task_enabled_entity_types'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Enabled local tasks'),
      '#description' => $this->t('Check in which entity types there should be a tab (local task) linking to the usage page.'),
      '#tree' => TRUE,
    ];
    $form['local_task_enabled_entity_types']['entity_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Local task entity types'),
      '#options' => $tabs_options,
      '#default_value' => $config->get('local_task_enabled_entity_types') ?: [],
    ];

    // Entity types (target).
    $form['track_enabled_target_entity_types'] = [
      '#type' => 'details',
      '#open' => FALSE,
      '#title' => $this->t('Enabled target entity types'),
      '#description' => $this->t('Check which entity types should be tracked when target.'),
      '#tree' => TRUE,
    ];
    $form['track_enabled_target_entity_types']['entity_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Target entity types'),
      '#options' => $entity_type_options,
      // If no custom settings exist, content entities are tracked by default.
      '#default_value' => $config->get('track_enabled_target_entity_types') ?: array_keys($content_entity_types),
      '#required' => TRUE,
    ];

    // Edit warning message.
    $form['edit_warning_message_entity_types'] = [
      '#type' => 'details',
      '#open' => FALSE,
      '#title' => $this->t('Warning message on edit form'),
      '#description' => $this->t('Check which entity types should show a message when being edited with recorded references to it.'),
      '#tree' => TRUE,
    ];
    $form['edit_warning_message_entity_types']['entity_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Entity types to show warning on edit form'),
      '#options' => $entity_type_options,
      '#default_value' => $config->get('edit_warning_message_entity_types') ?: [],
      '#required' => FALSE,
    ];

    // Delete warning message.
    $form['delete_warning_message_entity_types'] = [
      '#type' => 'details',
      '#open' => FALSE,
      '#title' => $this->t('Warning message on delete form'),
      '#description' => $this->t('Check which entity types should show a message when being deleted with recorded references to it.'),
      '#tree' => TRUE,
    ];
    $form['delete_warning_message_entity_types']['entity_types'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Entity types to show warning on delete form'),
      '#options' => $entity_type_options,
      '#default_value' => $config->get('delete_warning_message_entity_types') ?: [],
      '#required' => FALSE,
    ];

    // Only allow to opt-in on target entities being tracked.
    foreach (array_keys($entity_type_options) as $entity_type_id) {
      $form['edit_warning_message_entity_types']['entity_types'][$entity_type_id]['#states'] = [
        'enabled' => [
          ':input[name="track_enabled_target_entity_types[entity_types][' . $entity_type_id . ']"]' => ['checked' => TRUE],
        ],
      ];
      $form['delete_warning_message_entity_types']['entity_types'][$entity_type_id]['#states'] = [
        'enabled' => [
          ':input[name="track_enabled_target_entity_types[entity_types][' . $entity_type_id . ']"]' => ['checked' => TRUE],
        ],
      ];
    }

    // Miscellaneous settings.
    $form['generic_settings'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Generic'),
    ];
    $form['generic_settings']['site_domains'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Domains for this website'),
      '#description' => $this->t("A comma or new-line separated list of domain names for this website. Absolute URL's in content will be checked against these domains to allow usage tracking."),
      '#default_value' => implode("\r\n", $config->get('site_domains') ?: []),
    ];
    $form['generic_settings']['usage_controller_items_per_page'] = [
      '#type' => 'number',
      '#title' => $this->t('Items per page'),
      '#description' => $this->t('Define here the number of items per page that should be shown on the usage page.'),
      '#default_value' => $config->get('usage_controller_items_per_page') ?: ListUsageController::ITEMS_PER_PAGE_DEFAULT,
      '#min' => 1,
      '#step' => 1,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->cleanValues();
    $config = $this->config('entity_usage.settings');

    $local_tasks_updated = array_filter($config->get('local_task_enabled_entity_types')) !== array_filter($form_state->getValue('local_task_enabled_entity_types')['entity_types']);

    $site_domains = preg_replace('/[\s, ]/', ',', $form_state->getValue('site_domains'));
    $site_domains = array_values(array_filter(explode(',', $site_domains)));

    $config->set('local_task_enabled_entity_types', array_values(array_filter($form_state->getValue('local_task_enabled_entity_types')['entity_types'])))
      ->set('track_enabled_target_entity_types', array_values(array_filter($form_state->getValue('track_enabled_target_entity_types')['entity_types'])))
      ->set('edit_warning_message_entity_types', array_values(array_filter($form_state->getValue('edit_warning_message_entity_types')['entity_types'])))
      ->set('delete_warning_message_entity_types', array_values(array_filter($form_state->getValue('delete_warning_message_entity_types')['entity_types'])))
      ->set('site_domains', $site_domains)
      ->set('usage_controller_items_per_page', $form_state->getValue('usage_controller_items_per_page'))
      ->save();

    if ($local_tasks_updated) {
      $this->routerBuilder->rebuild();
      $this->cacheRender->invalidateAll();
    }

    parent::submitForm($form, $form_state);
  }

}
