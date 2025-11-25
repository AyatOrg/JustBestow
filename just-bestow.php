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
define('JUST_BESTOW_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('JUST_BESTOW_PLUGIN_URL', plugin_dir_url(__FILE__));



require_once JUST_BESTOW_PLUGIN_DIR . 'includes/class-just-bestow-settings.php';

const JUST_BESTOW_OPTION_NAME_MODE = 'just_bestow_mode';
const JUST_BESTOW_OPTION_NAME_FETCH_URL = 'just_bestow_fetch_url';
const JUST_BESTOW_OPTION_NAME_CLIENT_TYPE = 'just_bestow_client_type';
const JUST_BESTOW_OPTION_NAME_CLIENT_NAME = 'just_bestow_client_name';
const JUST_BESTOW_OPTION_NAME_CLIENT_EMAIL = 'just_bestow_client_email';
const JUST_BESTOW_OPTION_NAME_STRIPE_ACCOUNT = 'just_bestow_stripe_account';
const JUST_BESTOW_OPTION_NAME_STRIPE_OAUTH_URL = 'just_bestow_stripe_oauth_url';
const JUST_BESTOW_OPTION_NAME_STRIPE_PUBLISHABLE_KEY = 'just_bestow_stripe_publishable_key';

function just_best_init()
{
  Just_Bestow_Settings::init();

  if (get_option(JUST_BESTOW_OPTION_NAME_MODE) === false) {
    update_option(JUST_BESTOW_OPTION_NAME_MODE, 'test');
  }
  if (get_option(JUST_BESTOW_OPTION_NAME_STRIPE_PUBLISHABLE_KEY) === false) {
    update_option(JUST_BESTOW_OPTION_NAME_STRIPE_PUBLISHABLE_KEY, 'PUBLISHABLE_KEY');
  }
  if (get_option(JUST_BESTOW_OPTION_NAME_FETCH_URL) === false) {
    update_option(JUST_BESTOW_OPTION_NAME_FETCH_URL, 'https://api.justbestow.com/widget/form');
  }
  if (get_option(JUST_BESTOW_OPTION_NAME_STRIPE_OAUTH_URL) === false) {
    update_option(JUST_BESTOW_OPTION_NAME_STRIPE_OAUTH_URL, 'https://api.justbestow.com/t2pw/connect');
  }
}


add_action('plugins_loaded', 'just_best_init');



register_activation_hook(__FILE__, 'just_bestow_activate_defaults');
function just_bestow_activate_defaults()
{
  add_option(JUST_BESTOW_OPTION_NAME_MODE, 'test');
  add_option(JUST_BESTOW_OPTION_NAME_STRIPE_ACCOUNT, '');
  add_option(JUST_BESTOW_OPTION_NAME_STRIPE_PUBLISHABLE_KEY, 'PUBLISHABLE_KEY');
  add_option(JUST_BESTOW_OPTION_NAME_FETCH_URL, 'https://api.justbestow.com/widget/form');
  add_option(JUST_BESTOW_OPTION_NAME_STRIPE_OAUTH_URL, 'https://api.justbestow.com/t2pw/connect');
}


/* register our block. */
function just_bestow_register_block()
{

  wp_register_script(
    'jb-block-editor-script',
    plugins_url('blocks/just-bestow/just-bestow.js', __FILE__),
    ['wp-blocks', 'wp-element', 'wp-editor', 'wp-i18n'],
    filemtime(plugin_dir_path(__FILE__) . 'blocks/just-bestow/just-bestow.js')
  );

  register_block_type('just-bestow/example-block', [
    'editor_script' => 'jb-block-editor-script',
    'render_callback' => 'just_bestow_render_block',
    'attributes' => [
      'title' => [
        'type'    => 'string',
        'default' => 'Just Bestow',
      ],
    ],
  ]);
}
add_action('init', 'just_bestow_register_block');


/** We add the fetch url so we can access that in the frontend */
add_action('wp_head', function () {
  $mode = get_option(JUST_BESTOW_OPTION_NAME_MODE, 'test');
  $fetch_url = get_option(JUST_BESTOW_OPTION_NAME_FETCH_URL, 'https://api.justbestow.com/widget/form');
?>
  <script>
    var t2pw_settings = {
      fetchUrl: '<?php echo esc_js(esc_url_raw($fetch_url)); ?>',
      mode: '<?php echo esc_js($mode); ?>'
    };
  </script>
<?php
});


