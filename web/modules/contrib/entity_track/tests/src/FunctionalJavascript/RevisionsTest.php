<?php

namespace Drupal\Tests\entity_track\FunctionalJavascript;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\entity_track\Traits\EntityTrackLastEntityQueryTrait;
use Drupal\user\Entity\Role;

/**
 * Tests tracking for revisionable entities.
 *
 * @package Drupal\Tests\entity_track\FunctionalJavascript
 *
 * @group entity_track
 */
class RevisionsTest extends EntityTrackJavascriptTestBase {

  use EntityTrackLastEntityQueryTrait;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article'])->save();
    // Enable tracking for base fields.
    $config = \Drupal::configFactory()->getEditable('entity_track.settings');
    $config->set('track_enabled_base_fields', TRUE);
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

    // Create a ndoe.
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

    // Change back to a numeric title and create new revision, to check if we
    // are not adding duplicate tracking records.
    $this->drupalGet("/node/{$node->id()}/edit");
    $page->fillField('title[0][value]', '123456');
    $page->pressButton('Save');
    $session->wait(500);
    $this->saveHtmlOutput();
    $assert_session->pageTextContains('Article 123456 has been updated.');
    $this->assertCount(1, $numeric_tracking_manager->loadByEntity($node));
    $this->drupalGet("/node/{$node->id()}/edit");
    $page->fillField('title[0][value]', '1234567890');
    $revision_tab = $page->find('css', 'a[href="#edit-revision-information"]');
    $revision_tab->click();
    $page->checkField('Create new revision');
    $assert_session->checkboxChecked('Create new revision');
    $page->pressButton('Save');
    $session->wait(500);
    $this->saveHtmlOutput();
    $assert_session->pageTextContains('Article 1234567890 has been updated.');
    $this->assertCount(1, $numeric_tracking_manager->loadByEntity($node));

    // Removing an old revision should not remove tracking.
    $this->drupalGet("/node/{$node->id()}/revisions/{$node->getRevisionId()}/delete");
    $page->pressButton('Delete');
    $session->wait(500);
    $this->saveHtmlOutput();
    $this->assertCount(1, $numeric_tracking_manager->loadByEntity($node));

    // Test if deleting the entity removes the tracking information.
    $this->drupalGet("/node/{$node->id()}/delete");
    $page->pressButton('Delete');
    $session->wait(500);
    $this->saveHtmlOutput();
    $this->assertEmpty($numeric_tracking_manager->loadByEntity($node));
  }

}