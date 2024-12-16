<?php

namespace Drupal\Tests\entity_track\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * Base class for Entity Track Javascript functional tests.
 *
 * @package Drupal\Tests\entity_track\FunctionalJavascript
 */
abstract class EntityTrackJavascriptTestBase extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'field_ui',
    'system',
    'text',
    'path',
    'entity_track',
    'entity_track_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $account = $this->drupalCreateUser([
      'administer node fields',
      'administer node display',
      'administer nodes',
      'bypass node access',
    ]);
    $this->drupalLogin($account);
  }

  /**
   * Waits for jQuery to become ready and animations to complete.
   */
  protected function waitForAjaxToFinish() {
    $this->assertSession()->assertWaitOnAjaxRequest();
  }

  /**
   * Waits and asserts that a given element is visible.
   *
   * @param string $selector
   *   The CSS selector.
   * @param int $timeout
   *   (Optional) Timeout in milliseconds, defaults to 2000.
   * @param string $message
   *   (Optional) Message to pass to assertJsCondition().
   */
  protected function waitUntilVisible($selector, $timeout = 2000, $message = '') {
    $condition = "jQuery('" . $selector . ":visible').length > 0";
    $this->assertJsCondition($condition, $timeout, $message);
  }

  /**
   * Debugger method to save additional HTML output.
   *
   * The base class will only save browser output when accessing page using
   * ::drupalGet and providing a printer class to PHPUnit. This method
   * is intended for developers to help debug browser test failures and capture
   * more verbose output.
   */
  protected function saveHtmlOutput() {
    $out = $this->getSession()->getPage()->getContent();
    // Ensure that any changes to variables in the other thread are picked up.
    $this->refreshVariables();
    if ($this->htmlOutputEnabled) {
      $html_output = '<hr />Ending URL: ' . $this->getSession()->getCurrentUrl();
      $html_output .= '<hr />' . $out;
      $html_output .= $this->getHtmlOutputHeaders();
      $this->htmlOutput($html_output);
    }
  }

}
