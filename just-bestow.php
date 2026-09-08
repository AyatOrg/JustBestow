<?php

/**
 * Plugin Name: Just Bestow
 * Plugin URI:  https://ayatsolutions.com/justbestow
 * Description: Display JustBestow donation form on your WordPress site. Adds a block and a simple shortcode.
 * Version:     1.0.0
 * Author:      Ayat Solutions
 * Author URI:  https://ayatsolutions.com
 * Text Domain: just-bestow
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined('ABSPATH') || exit;

/* CONSTANTS */
define('JUSTBESTOW_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('JUSTBESTOW_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once JUSTBESTOW_PLUGIN_DIR . 'includes/class-justbestow-settings.php';

const JUSTBESTOW_OPTION_NAME_MODE = 'justbestow_mode';
const JUSTBESTOW_OPTION_NAME_FETCH_URL = 'justbestow_fetch_url';
const JUSTBESTOW_OPTION_NAME_CLIENT_TYPE = 'justbestow_client_type';
const JUSTBESTOW_OPTION_NAME_CLIENT_NAME = 'justbestow_client_name';
const JUSTBESTOW_OPTION_NAME_CLIENT_EMAIL = 'justbestow_client_email';
const JUSTBESTOW_OPTION_NAME_STRIPE_ACCOUNT = 'justbestow_stripe_account';
const JUSTBESTOW_OPTION_NAME_STRIPE_OAUTH_URL = 'justbestow_stripe_oauth_url';
const JUSTBESTOW_OPTION_NAME_STRIPE_PUBLISHABLE_KEY = 'justbestow_stripe_publishable_key';

/**
 * Init plugin defaults
 */
function justbestow_init()
{
  Justbestow_Settings::init();

  if (get_option(JUSTBESTOW_OPTION_NAME_MODE) === false) {
    update_option(JUSTBESTOW_OPTION_NAME_MODE, 'test');
  }

  if (get_option(JUSTBESTOW_OPTION_NAME_STRIPE_PUBLISHABLE_KEY) === false) {
    update_option(JUSTBESTOW_OPTION_NAME_STRIPE_PUBLISHABLE_KEY, 'PUBLISHABLE_KEY');
  }

  if (get_option(JUSTBESTOW_OPTION_NAME_FETCH_URL) === false) {
    update_option(
      JUSTBESTOW_OPTION_NAME_FETCH_URL,
      'https://api.justbestow.com/widget/form'
    );
  }

  if (get_option(JUSTBESTOW_OPTION_NAME_STRIPE_OAUTH_URL) === false) {
    update_option(
      JUSTBESTOW_OPTION_NAME_STRIPE_OAUTH_URL,
      'https://api.justbestow.com/t2pw/connect'
    );
  }
}
add_action('plugins_loaded', 'justbestow_init');

/**
 * Activation defaults
 */
function justbestow_activate_defaults()
{
  add_option(JUSTBESTOW_OPTION_NAME_MODE, 'test');
  add_option(JUSTBESTOW_OPTION_NAME_STRIPE_ACCOUNT, '');
  add_option(JUSTBESTOW_OPTION_NAME_STRIPE_PUBLISHABLE_KEY, 'PUBLISHABLE_KEY');
  add_option(
    JUSTBESTOW_OPTION_NAME_FETCH_URL,
    'https://api.justbestow.com/widget/scripts'
  );
  add_option(
    JUSTBESTOW_OPTION_NAME_STRIPE_OAUTH_URL,
    'https://api.justbestow.com/t2pw/connect'
  );
}
register_activation_hook(__FILE__, 'justbestow_activate_defaults');

/**
 * Register Gutenberg block
 */
function justbestow_register_block()
{

  wp_register_script(
    'justbestow-block-editor-script',
    plugins_url('blocks/justbestow/justbestow.js', __FILE__),
    ['wp-blocks', 'wp-element', 'wp-editor'],
    filemtime(
      plugin_dir_path(__FILE__) . 'blocks/justbestow/justbestow.js'
    )
  );

  register_block_type('justbestow/example-block', [
    'editor_script'   => 'justbestow-block-editor-script',
    'render_callback' => 'justbestow_render_block',
  ]);
}
add_action('init', 'justbestow_register_block');


/**
 * Build the widget container + loader script markup.
 * Shared by the Gutenberg block, the Elementor widget, and the FluentForm field.
 */
function justbestow_get_widget_markup($container_id = 'tap2pay-widget', $campaign_id = '', $extra_query_args = [])
{

  $fetch_url = get_option(
    JUSTBESTOW_OPTION_NAME_FETCH_URL,
    'https://api.justbestow.com/widget/scripts'
  );

  if (empty($fetch_url)) {
    return '';
  }

  if (!empty($campaign_id)) {
    $fetch_url = add_query_arg('campaignId', rawurlencode($campaign_id), $fetch_url);
  }

  if (!empty($extra_query_args)) {
    $fetch_url = add_query_arg(array_map('rawurlencode', $extra_query_args), $fetch_url);
  }

  ob_start();
?>
  <div id="<?php echo esc_attr($container_id); ?>"></div>
  <script crossorigin="anonymous" src="<?php echo esc_url($fetch_url); ?>" async></script>
<?php
  return ob_get_clean();
}

/**
 * Render block output (frontend)
 */
function justbestow_render_block()
{
  return justbestow_get_widget_markup();
}



/* add the Elementor widget if Elementor is active */
function justbestow_register_elementor_widget()
{
  if (!did_action('elementor/loaded')) {
    /* in-case no Elementor */
    return;
  }

  require_once JUSTBESTOW_PLUGIN_DIR . 'includes/class-justbestow-elementor-widget.php';

  \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Justbestow_Elementor_Widget());
}

add_action('elementor/widgets/register', 'justbestow_register_elementor_widget');

/**
 * Register "Just Bestow" as a FluentForm payment method, if FluentForm Pro's
 * Payments module is active. The widget itself renders inline under the
 * "Payment Method" field once Just Bestow is the (only) enabled method -
 * see fluentform/payment_method_contents_justbestow in
 * class-justbestow-fluentform-payment-method.php - the same mechanism
 * Stripe/Square use, so no separate field needs to be added to the form.
 */
function justbestow_register_fluentform_payment_method()
{
  if (!class_exists('\FluentFormPro\Payments\PaymentMethods\BaseProcessor')) {
    /* FluentForm Pro's Payments module is not active. */
    return;
  }

  require_once JUSTBESTOW_PLUGIN_DIR . 'includes/class-justbestow-fluentform-payment-method.php';

  (new Justbestow_FluentForm_PaymentMethod())->init();
}
add_action('plugins_loaded', 'justbestow_register_fluentform_payment_method', 20);

/**
 * Enqueue the FluentForm glue script/style on the frontend.
 * Both are cheap no-ops on pages without the Just Bestow payment method.
 */
function justbestow_enqueue_fluentform_assets()
{
  if (!class_exists('\FluentFormPro\Payments\PaymentMethods\BaseProcessor')) {
    return;
  }

  wp_enqueue_style(
    'justbestow-fluentform',
    JUSTBESTOW_PLUGIN_URL . 'includes/css/justbestow-fluentform.css',
    [],
    filemtime(JUSTBESTOW_PLUGIN_DIR . 'includes/css/justbestow-fluentform.css')
  );

  wp_enqueue_script(
    'justbestow-fluentform',
    JUSTBESTOW_PLUGIN_URL . 'includes/js/justbestow-fluentform.js',
    ['jquery'],
    filemtime(JUSTBESTOW_PLUGIN_DIR . 'includes/js/justbestow-fluentform.js'),
    true
  );
}
add_action('wp_enqueue_scripts', 'justbestow_enqueue_fluentform_assets');
