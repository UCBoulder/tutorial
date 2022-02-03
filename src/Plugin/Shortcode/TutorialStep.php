<?php

namespace Drupal\tutorial\Plugin\Shortcode;

use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\Core\Language\Language;
use Drupal\shortcode\Plugin\ShortcodeBase;
use Drupal\image\Entity\ImageStyle;
use Drupal\Component\Utility\Xss;
use Drupal\shortcode_svg\Plugin\ShortcodeIcon;

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
   * {@inheritdoc}
   */
  public function process(array $attributes, $text, $langcode = Language::LANGCODE_NOT_SPECIFIED) {
    $attributes = $this->getAttributes([
      'fid'    => '',
    ],
      $attributes
    );

    $fid = !empty($attributes['fid']) ? Xss::filter($attributes['fid']) : NULL;
    $img_html = '';

    if ($fid !== NULL) {
      $connection = \Drupal::database();
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
          $image_full = Url::fromUri(file_create_url($uri))->toString();
          $image_medium = ImageStyle::load('max_325x325')->buildUrl($uri);
          if ($width > $height) {
            $ratio = $height / $width;
            $width = 325;
            $height = 325 * $ratio;
            $height = round($height, 0);
          } else {
            $ratio = $width / $height;
            $height = 325;
            $width = 325 * $ratio;
            $width = round($width, 0);
          }
          $icon = new ShortcodeIcon();
          // Get svg icon path
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
