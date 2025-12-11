<?php

/**
 * Plugin Name: Just Bestow 
 * Plugin URI:  https://ayatsolutions.com/justbestow
 * Description: Display JustBestow donation form on your WordPress site. Adds a block and a simple shortcode.
 * Version:     1.0.0
 * Author:      Ayat Solutions
 * Author URI:  https://ayatsolutions.com
 * Text Domain: justbestow
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
    update_option(JUSTBESTOW_OPTION_NAME_FETCH_URL, 'https://api.justbestow.com/widget/form');
  }
  if (get_option(JUSTBESTOW_OPTION_NAME_STRIPE_OAUTH_URL) === false) {
    update_option(JUSTBESTOW_OPTION_NAME_STRIPE_OAUTH_URL, 'https://api.justbestow.com/t2pw/connect');
  }
}


add_action('plugins_loaded', 'justbestow_init');



register_activation_hook(__FILE__, 'justbestow_activate_defaults');
function justbestow_activate_defaults()
{
  add_option(JUSTBESTOW_OPTION_NAME_MODE, 'test');
  add_option(JUSTBESTOW_OPTION_NAME_STRIPE_ACCOUNT, '');
  add_option(JUSTBESTOW_OPTION_NAME_STRIPE_PUBLISHABLE_KEY, 'PUBLISHABLE_KEY');
  add_option(JUSTBESTOW_OPTION_NAME_FETCH_URL, 'https://api.justbestow.com/widget/form');
  add_option(JUSTBESTOW_OPTION_NAME_STRIPE_OAUTH_URL, 'https://api.justbestow.com/t2pw/connect');
}


/* register our block. */
function justbestow_register_block()
{

  wp_register_script(
    'justbestow-block-editor-script',
    plugins_url('blocks/justbestow/justbestow.js', __FILE__),
    ['wp-blocks', 'wp-element', 'wp-editor', 'wp-i18n'],
    filemtime(plugin_dir_path(__FILE__) . 'blocks/justbestow/justbestow.js')
  );

  register_block_type('justbestow/example-block', [
    'editor_script' => 'justbestow-block-editor-script',
    'render_callback' => 'justbestow_render_block',
    'attributes' => [
      'title' => [
        'type'    => 'string',
        'default' => 'Just Bestow',
      ],
    ],
  ]);
}
add_action('init', 'justbestow_register_block');


/** We add the fetch url so we can access that in the frontend */
add_action('wp_head', function () {
  $mode = get_option(JUSTBESTOW_OPTION_NAME_MODE, 'test');
  $fetch_url = get_option(JUSTBESTOW_OPTION_NAME_FETCH_URL, 'https://api.justbestow.com/widget/form');
?>
  <script>
    var t2pw_settings = {
      fetchUrl: '<?php echo esc_js(esc_url_raw($fetch_url)); ?>',
      mode: '<?php echo esc_js($mode); ?>'
    };
  </script>
<?php
});
