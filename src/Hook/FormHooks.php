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
   *
   * @param array<string, mixed> $element
   *   The field widget form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array<string, mixed> $context
   *   The widget context, including 'items' (the field item list).
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
