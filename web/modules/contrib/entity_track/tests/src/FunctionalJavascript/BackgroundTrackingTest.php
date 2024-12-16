<?php

namespace Drupal\Tests\entity_track\FunctionalJavascript;

use Drupal\Tests\entity_track\Traits\EntityTrackLastEntityQueryTrait;

/**
 * Tests tracking entities in the background.
 *
 * @package Drupal\Tests\entity_track\FunctionalJavascript
 *
 * @group entity_track
 */
class BackgroundTrackingTest extends EntityTrackJavascriptTestBase {

  use EntityTrackLastEntityQueryTrait;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article'])->save();
    // Enable background tracking and tracking for base fields.
    $config = \Drupal::configFactory()->getEditable('entity_track.settings');
    $config->set('track_enabled_base_fields', TRUE);
    $config->set('background_tracking', TRUE);
    $config->save();
  }

  /**
   * Tests the tracking of nodes and revisions.
   */
  public function testRevisionsTracking() {
    $session = $this->getSession();
    $page = $session->getPage();
    $assert_session = $this->assertSession();

    $numeric_tracking_manager = \Drupal::service('entity_track_test.numeric_manager');

    // Create a node.
    $this->drupalGet('/node/add/article');
    $page->fillField('title[0][value]', '123456');
    $page->pressButton('Save');
    $session->wait(500);
    $this->saveHtmlOutput();
    $assert_session->pageTextContains('Article 123456 has been created.');
    /** @var \Drupal\node\NodeInterface $node1 */
    $node = $this->getLastEntityOfType('node', TRUE);
    $this->assertCount(1, $numeric_tracking_manager->loadByEntity($node));

    // Change to a non-numeric title to stop tracking.
    $this->drupalGet("/node/{$node->id()}/edit");
    $page->fillField('title[0][value]', 'abcdef');
    $page->pressButton('Save');
    $session->wait(500);
    $this->saveHtmlOutput();
    $assert_session->pageTextContains('Article abcdef has been updated.');
    $this->assertEmpty($numeric_tracking_manager->loadByEntity($node));

    // Change back to a numeric title.
    $this->drupalGet("/node/{$node->id()}/edit");
    $page->fillField('title[0][value]', '123456');
    $page->pressButton('Save');
    $session->wait(500);
    $this->saveHtmlOutput();
    $assert_session->pageTextContains('Article 123456 has been updated.');
    $this->assertCount(1, $numeric_tracking_manager->loadByEntity($node));

    // Test if deleting the entity removes the tracking information.
    $this->drupalGet("/node/{$node->id()}/delete");
    $page->pressButton('Delete');
    $session->wait(500);
    $this->saveHtmlOutput();
    $this->assertEmpty($numeric_tracking_manager->loadByEntity($node));
  }

}
