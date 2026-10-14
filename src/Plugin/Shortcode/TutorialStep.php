<?php

namespace Drupal\tutorial\Plugin\Shortcode;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Database\Connection;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\shortcode\Attribute\Shortcode;
use Drupal\shortcode\Plugin\ShortcodeBase;
use Drupal\shortcode_svg\ShortcodeIcon;

/**
 * Provides a shortcode for tutorial steps.
 */
#[Shortcode(
  id: 'step',
  title: new TranslatableMarkup('Tutorial'),
  description: new TranslatableMarkup('Shortcode for adding steps to Tutorials'),
)]
class TutorialStep extends ShortcodeBase {

  /**
   * Iterate.
   *
   * @var int
   */
  protected $i = 0;

  /**
   * Constructs a new Shortcode plugin.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Database\Connection $connection
   *   Establish database connection.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $fileUrlGenerator
   *   File URL generator.
   * @param \Drupal\shortcode_svg\ShortcodeIcon $shortcodeSvgIcon
   *   Call shortcode svg icon.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    RendererInterface $renderer,
    protected Connection $connection,
    protected FileUrlGeneratorInterface $fileUrlGenerator,
    protected ShortcodeIcon $shortcodeSvgIcon,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $renderer);
  }

  /**
   * {@inheritdoc}
   */
  #[\Override]
  public function process(array $attributes, string $text, string $langcode = LanguageInterface::LANGCODE_NOT_SPECIFIED): string {
    $attributes = $this->getAttributes(
      [
        'fid'    => '',
        'reset'  => FALSE,
      ],
      $attributes
    );

    $reset = !empty($attributes['reset']) ? Xss::filter($attributes['reset']) : NULL;

    if ($reset == "TRUE") {
      $this->i = 1;
    }
    else {
      $this->i++;
    }

    $even_odd = ($this->i % 2 == 0) ? 'even' : 'odd';

    $fid = !empty($attributes['fid']) ? Xss::filter($attributes['fid']) : NULL;
    $img_html = '';

    if ($fid !== NULL) {
      $connection = $this->connection;
      $query = $connection->query("SELECT * FROM {node__field_tut_body_image} WHERE field_tut_body_image_target_id = :fid", [
        ':fid' => $fid,
      ]);
      $result = $query->fetchAssoc();

      if ($result) {
        $alt = $result['field_tut_body_image_alt'];
        $width = $result['field_tut_body_image_width'];
        $height = $result['field_tut_body_image_height'];
        $img_file = File::load($fid);
        if ($img_file) {
          $uri = $img_file->getFileUri();
          $image_full = $this->fileUrlGenerator->transformRelative($this->fileUrlGenerator->generateAbsoluteString($uri));
          $image_medium = ImageStyle::load('max_325x325')->buildUrl($uri);
          if ($width > $height) {
            $ratio = $height / $width;
            $width = 325;
            $height = 325 * $ratio;
            $height = round($height, 0);
          }
          else {
            $ratio = $width / $height;
            $height = 325;
            $width = 325 * $ratio;
            $width = round($width, 0);
          }
          $icon = $this->shortcodeSvgIcon;
          // Build the close icon markup for the modal window.
          $icon_markup = $icon->setIcon('x-fat', '20', 'fff');

          $img_html = [
            '#theme' => 'tutorial_step_image',
            '#fid' => $fid,
            '#image_medium' => $image_medium,
            '#image_full' => $image_full,
            '#width' => $width,
            '#height' => $height,
            '#alt' => $alt,
            '#icon' => $icon_markup,
          ];
          $img_html = $this->render($img_html);
        }
        else {
          $img_html = $this->t('Missing Image or incorrect fid set');
        }
      }
    }
    $step['string'] = [
      '#type' => 'inline_template',
      '#template' => "
        <div class='step-{{ increment }} tutorial-step flex-item flex-one-half {{ even_odd }}'>
          <h2 class='step'>{{ step_text }} {{ step_increment }}</h2>
          <div class='step-inner'>
            <div class='step-text'>{{ text|raw }}</div>
            <div class='prog-img'>{{ image|raw }}</div>
          </div>
        </div>",
      '#context' => [
        'increment' => $this->i,
        'even_odd' => $even_odd,
        'step_text' => $this->t('Step'),
        'step_increment' => $this->i,
        'text' => $text,
        'image' => $img_html,
      ],
    ];

    return $this->render($step);
  }

  /**
   * {@inheritdoc}
   */
  #[\Override]
  public function tips($long = FALSE) {
    $output = [];
    $output[] = '<p><strong>' . $this->t('[step fid="3" reset]Other HTML content here [/step]') . '</strong> ';
    return implode(' ', $output);
  }

}
