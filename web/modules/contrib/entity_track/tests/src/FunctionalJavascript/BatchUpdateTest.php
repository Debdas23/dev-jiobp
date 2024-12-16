<?php

namespace Drupal\Tests\entity_track\FunctionalJavascript;

use Drupal\user\Entity\Role;

/**
 * Tests for the batch update functionality.
 *
 * @package Drupal\Tests\entity_track\FunctionalJavascript
 *
 * @group entity_track
 */
class BatchUpdateTest extends EntityTrackJavascriptTestBase {

  /**
   * Tests the batch update.
   */
  public function testBatchUpdate() {
    $session = $this->getSession();
    $page = $session->getPage();
    $assert_session = $this->assertSession();

    // Enable tracking for base fields.
    $config = \Drupal::configFactory()->getEditable('entity_track.settings');
    $config->set('track_enabled_base_fields', TRUE);
    $config->save();

    // No permissions, you get a 403 when trying to access the batch update.
    $this->drupalGet('/admin/config/entity-track/batch-update');
    $assert_session->pageTextContains('You are not authorized to access this page');
    // Grant the logged-in the needed permission and try again.
    /** @var \Drupal\user\RoleInterface $role */
    $role = Role::load('authenticated');
    $this->grantPermissions($role, ['perform batch updates entity track']);
    $this->drupalGet('/admin/config/entity-track/batch-update');
    $assert_session->pageTextContains('Batch update');
    $assert_session->pageTextContains('This page allows you to delete and re-generate the tracking information in your system.');

    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article'])->save();

    // Create a node with a numeric, non-numeric and mixed title.
    $numeric_node = $this->drupalCreateNode(['title' => 123456, 'type' => 'article']);
    $text_node = $this->drupalCreateNode(['title' => 'abcdef', 'type' => 'article']);
    $mixed_node = $this->drupalCreateNode(['title' => 'abc123', 'type' => 'article']);

    // Go to the batch update page and check the update.
    $this->drupalGet('/admin/config/entity-track/batch-update');
    $assert_session->pageTextContains('Batch Update');
    $assert_session->pageTextContains('This page allows you to delete and re-generate the tracking information in your system.');
    $assert_session->pageTextContains('You may want to check the settings page to fine-tune what entities should be tracked, and other options.');

    // If in the settings form we have disabled tracking for nodes, the batch
    // update should have no effect.
    $config = \Drupal::configFactory()->getEditable('entity_track.settings');
    $config->set('track_enabled_entity_types', []);
    $config->save();

    $page->pressButton('Recreate tracking information');
    $this->getSession()->wait(1000);
    $this->saveHtmlOutput();
    $this->getSession()->wait(6000);
    $this->saveHtmlOutput();
    $assert_session->pageTextContains('Recreated tracking information for');

    $numeric_tracking_manager = \Drupal::service('entity_track_test.numeric_manager');

    // Since no entity types are enabled, no tracking information should be
    // found.
    $this->assertEmpty($numeric_tracking_manager->loadByEntity($numeric_node));
    $this->assertEmpty($numeric_tracking_manager->loadByEntity($text_node));
    $this->assertEmpty($numeric_tracking_manager->loadByEntity($mixed_node));

    // Enable tracking for nodes and try again.
    $config = \Drupal::configFactory()->getEditable('entity_track.settings');
    $config->set('track_enabled_entity_types', ['node']);
    $config->save();
    $this->drupalGet('/admin/config/entity-track/batch-update');
    $page->pressButton('Recreate tracking information');
    $this->getSession()->wait(1000);
    $this->saveHtmlOutput();
    $this->getSession()->wait(6000);
    $this->saveHtmlOutput();
    $assert_session->pageTextContains('Recreated tracking information for');

    // Check if the node with the numeric title is tracked, and the other nodes
    // are not.
    $this->assertCount(1, $numeric_tracking_manager->loadByEntity($numeric_node));
    $this->assertEmpty($numeric_tracking_manager->loadByEntity($text_node));
    $this->assertEmpty($numeric_tracking_manager->loadByEntity($mixed_node));
  }

}
