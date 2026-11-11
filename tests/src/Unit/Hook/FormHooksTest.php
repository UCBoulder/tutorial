<?php

namespace Drupal\Tests\tutorial\Unit\Hook;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\tutorial\Hook\FormHooks;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * Unit tests for the FormHooks hook implementations.
 */
#[Group('tutorial')]
#[CoversClass(FormHooks::class)]
#[CoversMethod(FormHooks::class, 'fieldWidgetSingleElementFormAlter')]
class FormHooksTest extends UnitTestCase {

  /**
   * Builds a widget context for the given field identity.
   */
  protected function buildContext(string $entity_type, ?string $bundle, string $field_name): array {
    $definition = $this->createMock(FieldDefinitionInterface::class);
    $definition->method('getTargetEntityTypeId')->willReturn($entity_type);
    $definition->method('getTargetBundle')->willReturn($bundle);
    $definition->method('getName')->willReturn($field_name);

    $items = $this->createMock(FieldItemListInterface::class);
    $items->method('getFieldDefinition')->willReturn($definition);

    return ['items' => $items];
  }

  /**
   * Tests the library is attached only to the tutorial body image widget.
   */
  #[DataProvider('providerFieldWidget')]
  public function testFieldWidgetSingleElementFormAlter(string $entity_type, ?string $bundle, string $field_name, bool $expected): void {
    $element = [];
    $form_state = $this->createMock(FormStateInterface::class);

    (new FormHooks())->fieldWidgetSingleElementFormAlter($element, $form_state, $this->buildContext($entity_type, $bundle, $field_name));

    if ($expected) {
      $this->assertSame(['tutorial/tutorial'], $element['#attached']['library']);
    }
    else {
      $this->assertSame([], $element);
    }
  }

  /**
   * Data provider for testFieldWidgetSingleElementFormAlter().
   */
  public static function providerFieldWidget(): array {
    return [
      'tutorial body image' => ['node', 'tutorial', 'field_tut_body_image', TRUE],
      'other field on tutorial' => ['node', 'tutorial', 'body', FALSE],
      'same field other bundle' => ['node', 'page', 'field_tut_body_image', FALSE],
      'same field other entity type' => ['media', 'tutorial', 'field_tut_body_image', FALSE],
      'base field without bundle' => ['node', NULL, 'title', FALSE],
    ];
  }

  /**
   * Tests existing attachments on the element are preserved.
   */
  public function testExistingLibrariesPreserved(): void {
    $element = ['#attached' => ['library' => ['core/drupal']]];
    $form_state = $this->createMock(FormStateInterface::class);

    (new FormHooks())->fieldWidgetSingleElementFormAlter($element, $form_state, $this->buildContext('node', 'tutorial', 'field_tut_body_image'));

    $this->assertSame(['core/drupal', 'tutorial/tutorial'], $element['#attached']['library']);
  }

}
