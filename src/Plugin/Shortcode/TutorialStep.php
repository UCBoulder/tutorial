<?php

namespace Drupal\tutorial\Plugin\Shortcode;

use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\Core\Language\Language;
use Drupal\shortcode\Plugin\ShortcodeBase;
use Drupal\image\Entity\ImageStyle;
use Drupal\Component\Utility\Xss;

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
      $query = $connection->query("SELECT field_tut_body_image_alt FROM {node__field_tut_body_image} WHERE field_tut_body_image_target_id = :fid", [
        ':fid' => $fid,
      ]);
      $result = $query->fetchAssoc();

      if ($result) {
        $alt = $result['field_tut_body_image_alt'];
        $img_file = File::load($fid);
        if ($img_file) {
          $uri = $img_file->getFileUri();
          $image_full = Url::fromUri(file_create_url($uri))->toString();
          $image_medium = ImageStyle::load('max_325x325')->buildUrl($uri);
          $pattern = '/^https?:\/\/.*?\/(.*?)\?(.*)/i';
          $image_medium_short = preg_replace($pattern, '$1', $image_medium);
          $image_medium_size = getimagesize($image_medium_short);
          $img_html = sprintf(
            '<a href="#img-%s">
              <img src="%s" loading="lazy" %s alt="%s"></img>
            </a>
            <div id="img-%s" class="tut-modal-window">
              <div>
                <div class="header">
                  <span>%s</span>
                  <a href="#step-%s" title="Close" class="modal-close button">Close</a>
                </div>
                <img src="%s" alt="%s" loading="lazy"></img>
              </div>
            </div>',
            $fid,
            $image_medium,
            $image_medium_size[3],
            $alt,
            $fid,
            $alt,
            $fid,
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
