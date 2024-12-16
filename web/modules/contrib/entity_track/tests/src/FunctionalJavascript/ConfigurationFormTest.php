<?php

namespace Drupal\Tests\entity_track\FunctionalJavascript;

use Drupal\Core\Entity\ContentEntityTypeInterface;

/**
 * Tests the configuration form.
 *
 * @package Drupal\Tests\entity_track\FunctionalJavascript
 *
 * @group entity_track
 */
class ConfigurationFormTest extends EntityTrackJavascriptTestBase {

  /**
   * Tests the config form.
   */
  public function testConfigForm() {
    $session = $this->getSession();
    $page = $session->getPage();
    $assert_session = $this->assertSession();

    $numeric_tracking_manager = \Drupal::service('entity_track_test.numeric_manager');

    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article'])->save();

    // Check the form is using the expected permission-based access.
    $this->drupalGet('/admin/config/entity-track');
    $assert_session->pageTextContains('You are not authorized to access this page');
    $this->drupalLogin($this->drupalCreateUser([
      'bypass node access',
      'administer entity track',
    ]));
    $this->drupalGet('/admin/config/entity-track');
    $session->getPage()->findButton('Save configuration');

    // Create a list of (content) entity types.
    $all_entity_types = \Drupal::entityTypeManager()->getDefinitions();
    $content_entity_types = [];
    /** @var \Drupal\Core\Entity\EntityTypeInterface[] $entity_types */
    $entity_types = [];
    foreach ($all_entity_types as $entity_type) {
      if (($entity_type instanceof ContentEntityTypeInterface)) {
        $content_entity_types[$entity_type->id()] = $entity_type->getLabel();
      }
      $entity_types[$entity_type->id()] = $entity_type->getLabel();
    }
    unset($content_entity_types['file']);
    unset($content_entity_types['user']);

    // Test generic settings.
    $summary = $assert_session->elementExists('css', '#edit-generic-settings summary');
    $this->assertEquals('Generic', $summary->getText());
    $assert_session->fieldExists('track_enabled_base_fields');
    // It should be off by default.
    $assert_session->checkboxNotChecked('track_enabled_base_fields');
    $assert_session->pageTextContains('Track referencing basefields');
    $assert_session->pageTextContains('If enabled, information stored in non-configurable fields (basefields) will also be tracked.');

    // Test enabled entity types config.
    $this->drupalGet('/admin/config/entity-track');
    $summary = $assert_session->elementExists('css', '#edit-track-enabled-entity-types summary');
    $this->assertEquals('Enabled entity types', $summary->getText());
    $entity_types_details = $page->find('css', '#edit-track-enabled-entity-types');
    $entity_types_details->click();
    $assert_session->pageTextContains('Check which entity types should be tracked.');
    foreach ($entity_types as $entity_type_id => $entity_type) {
      $field_name = "track_enabled_entity_types[entity_types][$entity_type_id]";
      $assert_session->fieldExists($field_name);
      // By default all content entity types are tracked.
      if (in_array($entity_type_id, array_keys($content_entity_types))) {
        $assert_session->checkboxChecked($field_name);
      }
      else {
        $assert_session->checkboxNotChecked($field_name);
      }
    }

    // Creating a node with a numeric title should not be tracked since the
    // title is a basefield. All entity types and plugins are enabled by
    // default.
    $node1 = $this->drupalCreateNode(['title' => 123456, 'type' => 'article']);
    $this->assertEmpty($numeric_tracking_manager->loadByEntity($node1));

    // Enabled base field tracking.
    $page->checkField('track_enabled_base_fields');
    $page->pressButton('Save configuration');
    $this->saveHtmlOutput();
    drupal_flush_all_caches();
    $assert_session->pageTextContains('The configuration options have been saved.');

    // Nodes with numeric titles should be tracked now.
    $node2 = $this->drupalCreateNode(['title' => 123456, 'type' => 'article']);
    $this->assertCount(1, $numeric_tracking_manager->loadByEntity($node2));
    $node2->delete();
    $this->assertEmpty($numeric_tracking_manager->loadByEntity($node2));

    // Disabling node should prevent the nodes from being tracked.
    $entity_types_details = $page->find('css', '#edit-track-enabled-entity-types');
    $entity_types_details->click();
    $page->uncheckField('track_enabled_entity_types[entity_types][node]');
    $page->pressButton('Save configuration');
    $this->saveHtmlOutput();
    drupal_flush_all_caches();
    $assert_session->pageTextContains('The configuration options have been saved.');
    $node3 = $this->drupalCreateNode(['title' => 123456, 'type' => 'article']);
    $this->assertEmpty($numeric_tracking_manager->loadByEntity($node3));

    // Enable back node and start tracking again.
    $entity_types_details = $page->find('css', '#edit-track-enabled-entity-types');
    $entity_types_details->click();
    $page->checkField('track_enabled_entity_types[entity_types][node]');
    $page->pressButton('Save configuration');
    $this->saveHtmlOutput();
    drupal_flush_all_caches();
    $assert_session->pageTextContains('The configuration options have been saved.');
    $node4 = $this->drupalCreateNode(['title' => 123456, 'type' => 'article']);
    $this->assertCount(1, $numeric_tracking_manager->loadByEntity($node4));
    $node4->delete();
    $this->assertEmpty($numeric_tracking_manager->loadByEntity($node4));

    // Test enabled plugins.
    $this->drupalGet('/admin/config/entity-track');
    $summary = $assert_session->elementExists('css', '#edit-track-enabled-plugins summary');
    $this->assertEquals('Enabled tracking plugins', $summary->getText());
    $assert_session->pageTextContains('The following plugins were found in the system and can provide usage tracking. Check all plugins that should be active.');
    $plugins = \Drupal::service('plugin.manager.entity_track.track')->getDefinitions();
    foreach ($plugins as $plugin_id => $plugin) {
      $field_name = "track_enabled_plugins[plugins][$plugin_id]";
      $assert_session->fieldExists($field_name);
      $assert_session->pageTextContains($plugin['label']);
      if (!empty($plugin['description'])) {
        $assert_session->pageTextContains($plugin['description']);
      }
      // By default all plugins are active.
      $assert_session->checkboxChecked($field_name);
    }

    // Disable "numeric" and check usage is not tracked.
    $summary = $assert_session->elementExists('css', '#edit-track-enabled-plugins summary');
    $this->assertEquals('Enabled tracking plugins', $summary->getText());
    $summary = $assert_session->elementExists('css', '#edit-track-enabled-plugins');
    $summary->click();
    $page->uncheckField('track_enabled_plugins[plugins][numeric]');
    $page->pressButton('Save configuration');
    $this->saveHtmlOutput();
    drupal_flush_all_caches();
    $assert_session->pageTextContains('The configuration options have been saved.');
    $node5 = $this->drupalCreateNode(['title' => 123456, 'type' => 'article']);
    $this->assertEmpty($numeric_tracking_manager->loadByEntity($node5));
  }

}
