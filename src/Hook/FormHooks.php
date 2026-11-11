<?php

namespace Drupal\tutorial\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for form alterations.
 */
class FormHooks {

  /**
   * Implements hook_field_widget_single_element_form_alter().
   *
   * Attaches the tutorial library to the tutorial body image widget.
   */
  #[Hook('field_widget_single_element_form_alter')]
  public function fieldWidgetSingleElementFormAlter(array &$element, FormStateInterface $form_state, array $context): void {
    $field_definition = $context['items']->getFieldDefinition();
    if ($field_definition->getTargetEntityTypeId() === 'node'
      && $field_definition->getTargetBundle() === 'tutorial'
      && $field_definition->getName() === 'field_tut_body_image') {
      $element['#attached']['library'][] = 'tutorial/tutorial';
    }
  }

}
