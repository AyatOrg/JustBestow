<?php

defined('ABSPATH') || exit;

use FluentFormPro\Payments\PaymentMethods\BaseProcessor;
use FluentForm\Framework\Helpers\ArrayHelper;

/**
 * Registers "Just Bestow" as a selectable FluentForm payment method (mirrors
 * fluentformpro's own Offline gateway, the simplest existing reference) so
 * FluentForm's native payment status, method label, and transaction records
 * work correctly for donations made through the Just Bestow widget field.
 *
 * The actual card charge already happens client-side via the widget's own
 * JS (see includes/js/justbestow-fluentform.js) before FluentForm's own
 * submission AJAX call is even allowed through - this class only records
 * that already-completed outcome into FluentForm's own payment system.
 */
class Justbestow_FluentForm_PaymentMethod
{
  protected $key = 'justbestow';

  public function init()
  {
    add_filter('fluentform/available_payment_methods', [$this, 'pushPaymentMethodToForm']);

    add_filter('fluentform/payment_method_public_name_' . $this->key, function () {
      return __('Just Bestow', 'just-bestow');
    });

    /* This is what makes the widget render inline under the "Payment Method"
     * field once Just Bestow is the selected/only method - the same
     * mechanism Stripe/Square use for their own card fields, so admins add
     * just the one "Payment Method" field, not a separate Just Bestow field. */
    add_filter('fluentform/payment_method_contents_' . $this->key, [$this, 'renderWidgetContent'], 10, 4);

    (new Justbestow_FluentForm_Processor())->init();
  }

  public function pushPaymentMethodToForm($methods)
  {
    $methods[$this->key] = [
      'title'        => __('Just Bestow', 'just-bestow'),
      'enabled'      => 'yes',
      'method_value' => $this->key,
      'settings'     => [
        'option_label' => [
          'type'     => 'text',
          'template' => 'inputText',
          'value'    => __('Just Bestow', 'just-bestow'),
          'label'    => __('Method Label', 'just-bestow'),
        ],
        'campaign_id' => [
          'type'      => 'text',
          'template'  => 'inputText',
          'value'     => '',
          'label'     => __('Campaign ID', 'just-bestow'),
          'help_text' => __('Optional. The Just Bestow campaign this form\'s donations should apply to. Leave blank to use the client\'s default campaign.', 'just-bestow'),
        ],
      ],
    ];

    return $methods;
  }

  public function renderWidgetContent($content, $method, $data, $form)
  {
    $loader = '<div class="jb-widget-loader" aria-busy="true" aria-label="'
      . esc_attr__('Loading payment form…', 'just-bestow') . '">'
      . '<span class="jb-skeleton-line jb-skeleton-toggle"></span>'
      . '<span class="jb-skeleton-line jb-skeleton-card"></span>'
      . '</div>';

    $campaignId = ArrayHelper::get($method, 'settings.campaign_id.value', '');

    return $content . '<div class="jb-ff-widget">' . $loader . justbestow_get_widget_markup('tap2pay-widget', $campaignId) . '</div>';
  }
}

/**
 * Records the already-completed justbestow charge into FluentForm's own
 * payment tables: marks the submission "paid" and inserts a transaction row
 * carrying the justbestow transaction id as the charge_id, so it shows up
 * as a proper reference in FluentForm's own Payments/Entries view.
 */
class Justbestow_FluentForm_Processor extends BaseProcessor
{
  protected $method = 'justbestow';

  public function handlePaymentAction($submissionId, $submissionData, $form, $methodSettings, $hasSubscriptions, $totalPayable)
  {
    $this->setSubmissionId($submissionId);

    $chargeId = '';
    if (!empty($_POST['data']) && is_string($_POST['data'])) {
      parse_str(wp_unslash($_POST['data']), $rawFields);
      if (!empty($rawFields['payment_id'])) {
        $chargeId = sanitize_text_field($rawFields['payment_id']);
      }
    }

    $submission = $this->getSubmission();

    $transactionData = array_merge($this->getTransactionDefaults(), [
      'status'        => 'paid',
      'charge_id'     => $chargeId,
      'payment_total' => $totalPayable,
      'currency'      => $submission ? strtoupper($submission->currency) : 'USD',
    ]);

    $this->insertTransaction($transactionData);
    $this->changeSubmissionPaymentStatus('paid');
    $this->completePaymentSubmission();
  }
}
