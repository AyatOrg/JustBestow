<?php

defined('ABSPATH') || exit;

use FluentForm\App\Services\FormBuilder\BaseFieldManager;
use FluentForm\Framework\Helpers\ArrayHelper;

/**
 * Registers "Just Bestow Donation" as a field an admin can add to a FluentForm
 * form (from the Payment fields group), the same way they'd add FluentForm's
 * own "Custom Payment Amount" or "Payment Summary" fields.
 *
 * This field carries no submitted value of its own - it just renders the
 * Just Bestow widget container + loader script at the position it's dropped
 * in the form. The actual amount/email sync, submit gating, and charge
 * handling is done client-side by includes/js/justbestow-fluentform.js.
 */
class Justbestow_FluentForm_Field extends BaseFieldManager
{
  public function __construct(
    $key = 'justbestow_widget',
    $title = 'Just Bestow Donation',
    $tags = ['payment', 'donation', 'justbestow'],
    $position = 'payments'
  ) {
    parent::__construct($key, $title, $tags, $position);
  }

  public function getComponent()
  {
    return array(
      'index'          => 8,
      'element'        => $this->key,
      'attributes'     => array(),
      'settings'       => array(
        'html_codes'         => '<p>' . __('Just Bestow donation form will be shown here.', 'just-bestow') . '</p>',
        'conditional_logics' => array(),
        'container_class'    => '',
      ),
      'editor_options' => array(
        'title'      => __('Just Bestow Donation', 'just-bestow'),
        'icon_class' => 'ff-edit-html',
        'template'   => 'customHTML',
      ),
    );
  }

  public function getGeneralEditorElements()
  {
    return [];
  }

  public function generalEditorElement()
  {
    return [];
  }

  public function getAdvancedEditorElements()
  {
    return [
      'conditional_logics',
      'container_class',
    ];
  }

  /**
   * Renders the field. Deliberately does NOT go through FluentForm's
   * CustomHtml::compile()/fluentform_sanitize_html(), since that strips
   * <script> tags - the widget markup is plugin-generated (trusted), not
   * user-authored content, so it mirrors CustomHtml's wrapper logic without
   * the sanitization step.
   */
  public function render($data, $form)
  {
    $elementName = $data['element'];
    $data = apply_filters('fluentform/rendering_field_data_' . $elementName, $data, $form);

    $hasConditions = $this->hasConditions($data) ? 'has-conditions ' : '';
    $cls = trim($this->getDefaultContainerClass() . ' ff-' . $elementName . ' ' . $hasConditions . ' jb-ff-widget');
    if ($containerClass = ArrayHelper::get($data, 'settings.container_class')) {
      $cls .= ' ' . $containerClass;
    }
    $atts = $this->buildAttributes(
      ArrayHelper::except($data['attributes'], 'name')
    );

    $widgetContent = '<div class="jb-widget-loader">' . esc_html__('Loading payment form…', 'just-bestow') . '</div>'
      . justbestow_get_widget_markup();

    $html = "<div class='" . esc_attr($cls) . "' tabindex='-1' {$atts}>{$widgetContent}</div>";

    $html = apply_filters('fluentform/rendering_field_html_' . $elementName, $html, $data, $form);

    $this->printContent('fluentform/rendering_field_html_' . $elementName, $html, $data, $form);
  }
}
