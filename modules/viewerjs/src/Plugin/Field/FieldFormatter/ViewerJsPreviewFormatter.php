<?php

/**
 * @file
 * Contains Drupal\viewerjs\Plugin\Field\FieldFormatter\ViewerJsPreviewFormatter.
 */

namespace Drupal\viewerjs\Plugin\Field\FieldFormatter;

use Drupal\image\Plugin\Field\FieldFormatter\ImageFormatterBase;
use Drupal\image\Entity\ImageStyle;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'viewer_js' formatter.
 *
 * @FieldFormatter(
 *   id = "viewerjs_preview",
 *   module = "viewerjs",
 *   label = @Translation("ViewerJS Preview embedded"),
 *   field_types = {
 *     "file", "image"
 *   }
 * )
 */
class ViewerJsPreviewFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'template' => 'index.html',
      'overlay_size' => '1000x500',
      'max_image_size' => 'viewerjs',
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $image_styles = image_style_options(FALSE);


    $element['template'] = array(
      '#title' => t('ViewerJs Template file'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('template'),
      '#required' => TRUE,
      '#description' => t('The template html file to use. Default index.html. The template MUST be in the Viewerjs library folder.'),
    );
    $element['overlay_size'] = array(
      '#title' => t('Preview Size'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('overlay_size'),
      '#required' => TRUE,
      '#description' => t('The overlay size in the format widthxheight, for example 400x300.'),
    );
    $element['max_image_size'] = array(
      '#title' => t('Image max size'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('max_image_size'),
      '#required' => TRUE,
      '#options' => $image_styles,
      '#description' => t('Only for images: when the image is bigger than "Max size of the preview", this image style will be used to resize it.'),
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();

    $summary[] = t('Using template @template;', array('@template' => $this->getSetting('template')));

    $size = array(
      '@size' => $this->getSetting('preview_max_size'),
      '@style' => $this->getSetting('max_image_size'),
    );

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    global $base_url;
    $elements = array(
      '#type' => 'markup',
      '#markup' => '',
      '#prefix' => '<div class="viewerjs-formatter-field">',
      '#suffix' => '</div>',
    );

    list($ow, $oh) = explode('x', $this->getSetting('overlay_size'));

    foreach ($items as $delta => $item) {

      $filename = $item->entity->getFilename();
      $splited_uri = explode('.', $filename);
      $extension = end($splited_uri);
      $size = format_size($item->entity->getSize());

      $viewerjs_path = viewerjs_get_viewerjs_path();
      $download_url = file_create_url($item->entity->getFileUri());
      $path = $base_url . '/' . $viewerjs_path . '/' . $this->getSetting('template') . '#' . $download_url;

      $preview_link = FALSE;
      if (in_array(strtolower($extension), $this->viewerjsSupportedExtensions())) {
        $preview_class = array('preview');
        $preview_link = array(
          'path' => $path,
          'width' => $ow,
          'height' => $oh,
          'id' => $item->entity->id(),
        );
      }
      if ($item->isDisplayed() && $item->entity) {
        $elements[$delta] = array(
          '#theme' => 'viewerjs_preview',
          '#file' => $item->entity,
          '#description' => $item->description,
          '#size' => $size,
          '#download_link' => $download_url,
          '#preview_link' => $preview_link,
          '#attributes' => array(),
        );
      }

    }

    if (!empty($elements)) {
      $elements['#attached']['library'][] = 'viewerjs/viewerjs';
    }

    return $elements;
  }

  /**
   * Return a list of extension supported by Viewerjs's PluginLoader.
   */
  protected function viewerjsSupportedExtensions() {
    return array(
      'pdf',
      'odt',
      'odp',
      'ods',
      'fodt',
      'jpg',
      'jpeg',
      'png',
      'gif',
    );
  }

}
