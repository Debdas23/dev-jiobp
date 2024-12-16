<?php

namespace Drupal\jiobp_jobs\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides a Jioev page controller.
 */
class JioevController extends ControllerBase {
  /**
   * Returns the content for the Jioev page.
   *
   * @return array
   *   A render array containing the page markup.
   */
  public function content() {
    return [
      '#markup' => '<h1>Welcome to the Jioev Page</h1>',
    ];
  }
}

