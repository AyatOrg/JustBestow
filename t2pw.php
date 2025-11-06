<?php

/**
 * Plugin Name: JustBestow Widget
 * Plugin URI:  https://ayatsolutions.com/justbestow
 * Description: Display JustBestow donation widget on your WordPress site. Adds a block and a simple shortcode.
 * Version:     1.0.0
 * Author:      Ayat Solutions 
 * Text Domain: justbestow-widget
 * Domain Path: /languages
 *
 */

defined('ABSPATH') || exit;

/* CONSTANTS */
define('T2PW_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('T2PW_PLUGIN_URL', plugin_dir_url(__FILE__));



require_once T2PW_PLUGIN_DIR . 'includes/class-t2pw-settings.php';



function t2pw_init()
{
  T2PWidgets_Settings::init();
  add_option(OPTION_NAME_STRIPE_ACCOUNT, '');
  add_option(OPTION_NAME_STRIPE_PUBLISHABLE_KEY, 'PUBLISHABLE_KEY');
  add_option(OPTION_NAME_FETCH_URL, 'https://api.justbestow.com/widget/form');
  add_option(OPTION_NAME_STRIPE_OAUTH_URL, 'https://api.justbestow.com/t2pw/connect');
}

add_action('plugins_loaded', 't2pw_init');


/* Blocks */
function t2pw_register_widget_block()
{


  wp_register_script(
    't2pw-widget-block',
    plugins_url('blocks/t2pw-widget/t2pw-widget.js', __FILE__),
    ['wp-blocks', 'wp-element', 'wp-i18n', 'wp-editor', 'wp-components', 'wp-server-side-render'],
    filemtime(plugin_dir_path(__FILE__) . 'blocks/t2pw-widget/t2pw-widget.js')
  );

  register_block_type('t2pw/widget-example', [
    'editor_script' => 't2pw-widget-block',
    'render_callback' => 't2pw_render_widget_block',
    'attributes' => [
      'title' => ['type' => 'string', 'default' => ''],
    ],
  ]);
}

add_action('init', 't2pw_register_widget_block');



/** We add the fetch url so we can access that in the frontend */
add_action('wp_head', function () {
  $mode = get_option(OPTION_NAME_MODE, 'test');
  $fetch_url = get_option('t2pw_fetch_url', 'https://api.justbestow.com/widget/form');
?>
  <script>
    var t2pw_settings = {
      fetchUrl: '<?php echo esc_js(esc_url_raw($fetch_url)); ?>',
      mode: '<?php echo esc_js($mode); ?>'
    };
  </script>
<?php
});
