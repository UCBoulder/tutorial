<?php

namespace Drupal\tutorial\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for theme registration.
 */
class ThemeHooks {

  /**
   * Implements hook_theme().
   *
   * @return array<string, array<string, mixed>>
   *   The theme registry definitions.
   */
  #[Hook('theme')]
  public function theme(): array {
    return [
      'tutorial_step_image' => [
        'variables' => [
          'fid' => NULL,
          'image_medium' => NULL,
          'image_full' => NULL,
          'width' => NULL,
          'height' => NULL,
          'alt' => NULL,
          'icon' => NULL,
        ],
      ],
    ];
  }

}
