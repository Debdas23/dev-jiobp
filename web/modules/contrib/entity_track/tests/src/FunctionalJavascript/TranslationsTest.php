<?php

namespace Drupal\Tests\entity_track\FunctionalJavascript;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\Tests\entity_track\Traits\EntityTrackLastEntityQueryTrait;
use Drupal\user\Entity\Role;

/**
 * Tests tracking for translatable entities.
 *
 * @package Drupal\Tests\entity_track\FunctionalJavascript
 *
 * @group entity_track
 */
class TranslationsTest extends EntityTrackJavascriptTestBase {

  use EntityTrackLastEntityQueryTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'language',
    'content_translation',
  ];

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
   * Tests the tracking of nodes with translations.
   */
  public function testTranslationsTracking() {
    $session = $this->getSession();
    $page = $session->getPage();
    $assert_session = $this->assertSession();

    $numeric_tracking_manager = \Drupal::service('entity_track_test.numeric_manager');

    // Create some additional languages.
    foreach (['es', 'nl'] as $langcode) {
      ConfigurableLanguage::createFromLangcode($langcode)->save();
    }

    // Let the logged-in user do multi-lingual stuff.
    /** @var \Drupal\user\RoleInterface $authenticated_role */
    $authenticated_role = Role::load('authenticated');
    $authenticated_role->grantPermission('administer content translation');
    $authenticated_role->grantPermission('translate any entity');
    $authenticated_role->grantPermission('create content translations');
    $authenticated_role->grantPermission('administer languages');
    $authenticated_role->grantPermission('administer entity track');
    $authenticated_role->save();

    // Set our content type as translatable.
    $this->drupalGet('/admin/config/regional/content-language');
    $page->checkField('entity_types[node]');
    $page->checkField('settings[node][article][translatable]');
    $page->pressButton('Save configuration');
    $session->wait(500);
    $this->saveHtmlOutput();
    $assert_session->pageTextContains('Settings successfully updated.');

    // Create a node in EN.
    $this->drupalGet('/node/add/article');
    $page->fillField('title[0][value]', '123456');
    $assert_session->elementExists('css', 'select[name="langcode[0][value]"]');
    $page->selectFieldOption('langcode[0][value]', 'en');
    $page->pressButton('Save');
    $session->wait(500);
    $this->saveHtmlOutput();
    $assert_session->pageTextContains('Article 123456 has been created.');
    /** @var \Drupal\node\NodeInterface $node1 */
    $node = $this->getLastEntityOfType('node', TRUE);
    $this->assertCount(1, $numeric_tracking_manager->loadByEntity($node));

    // Translate the node to ES and assure the translation is stored separate.
    $this->drupalGet("/node/{$node->id()}/translations/add/en/es");
    $page->fillField('title[0][value]', '789012');
    $page->pressButton('Save (this translation)');
    $session->wait(500);
    $this->saveHtmlOutput();
    $assert_session->pageTextContains('Article 789012 has been updated.');
    $this->assertCount(2, $numeric_tracking_manager->loadByEntity($node));

    // Delete the translation and verify only the original language is still
    // tracked.
    $this->drupalGet("/es/node/{$node->id()}/delete");
    $page->pressButton('Delete Spanish translation');
    $session->wait(500);
    $this->saveHtmlOutput();
    $assert_session->pageTextContains('Spanish translation of the Article 789012 has been deleted.');
    $this->assertCount(1, $numeric_tracking_manager->loadByEntity($node));
  }

}
