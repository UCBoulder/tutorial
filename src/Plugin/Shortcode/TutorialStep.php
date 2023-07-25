<?php

namespace Drupal\tutorial\Plugin\Shortcode;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Database\Connection;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Render\RendererInterface;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;
use Drupal\shortcode\Plugin\ShortcodeBase;
use Drupal\shortcode_svg\Plugin\ShortcodeIcon;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a shortcode for tutorial steps.
 *
 * @Shortcode(
 *   id = "step",
 *   title = @Translation("Tutorial"),
 *   description = @Translation("Shortcode for adding steps to Tutorials")
 * )
 */
class TutorialStep extends ShortcodeBase {

  /**
   * Run Database query.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * File Url Generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * Call shortcode svg icon.
   *
   * @var \Drupal\shortcode_svg\Plugin\ShortcodeIcon
   */
  protected $shortcodeSvgIcon;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

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
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $file_url_generator
   *   File URL generator.
   * @param \Drupal\shortcode_svg\Plugin\ShortcodeIcon $shortcode_svg_icon
   *   Call shortcode svg icon.
   */
  public function __construct(
    array $configuration,
          $plugin_id,
          $plugin_definition,
    RendererInterface $renderer,
    Connection $connection,
    FileUrlGeneratorInterface $file_url_generator,
    ShortcodeIcon $shortcode_svg_icon
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $renderer);
    $this->connection = $connection;
    $this->fileUrlGenerator = $file_url_generator;
    $this->shortcodeSvgIcon = $shortcode_svg_icon;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('renderer'),
      $container->get('database'),
      $container->get('file_url_generator'),
      $container->get('shortcode_svg.icon')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process(array $attributes, $text, $langcode = Language::LANGCODE_NOT_SPECIFIED) {
    $attributes = $this->getAttributes(
      [
        'fid'    => '',
      ],
      $attributes
    );

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
          // Get svg icon path.
          $icon = $icon->getSvg();
          $img_html = sprintf(
            '<a href="#img-%s">
              <img src="%s" width="%s" height="%s" loading="lazy" alt="%s"></img>
            </a>
            <div id="img-%s" class="tut-modal-window">
              <div>
                <div class="header">
                  <span>%s</span>
                  <a href="#step-%s" title="Close" class="modal-close">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 34 34" class="svg-icon x-fat" width="20">
                      <use fill="#fff" xlink:href="%s#x-fat"></use>
                    </svg>
                  </a>
                </div>
                <img src="%s" alt="%s" loading="lazy"></img>
              </div>
            </div>',
            $fid,
            $image_medium,
            $width,
            $height,
            $alt,
            $fid,
            $alt,
            $fid,
            $icon,
            $image_full,
            $alt
          );
        }
        else {
          $img_html = $this->t('Missing Image or incorrect fid set');
        }
      }
    }
    $step = sprintf(
      "<div id='step-%s' class='tutorial-step flex-item flex-one-half'>
      <h2 class='step'>%s</h2>
      <div class='step-inner'>
        <div class='step-text'>%s</div>
        <div class='prog-img'>%s</div>
      </div>
    </div>",
      $fid,
      $this->t("Step"),
      $text,
      $img_html
    );
    return $step;
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    $output = [];
    $output[] = '<p><strong>' . $this->t('[step fid="3"]Other HTML content here [/step]') . '</strong> ';
    return implode(' ', $output);
  }

}
