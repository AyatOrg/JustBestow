<?php

class Just_Bestow_Settings
{
  public static function init()
  {
    add_action('admin_menu', [__CLASS__, 'add_menu_page']);
    add_action('admin_init', [__CLASS__, 'register_settings']);
    add_action('admin_enqueue_scripts', [__CLASS__, 'admin_enqueue_scripts']);
  }

  public static function add_menu_page()
  {
    add_menu_page(
      'Just Bestow',
      'Just Bestow',
      'manage_options',
      'just-bestow-settings',
      [__CLASS__, 'render_settings_page'],
      'dashicons-heart',
      80
    );
  }

  public static function register_settings()
  {
    register_setting('just_bestow_settings_group', esc_attr(JUST_BESTOW_OPTION_NAME_MODE), ['sanitize_callback' => 'sanitize_text_field',]);
    register_setting('just_bestow_settings_group', esc_attr(JUST_BESTOW_OPTION_NAME_FETCH_URL), ['sanitize_callback' => 'sanitize_text_field',]);
    register_setting('just_bestow_settings_group', esc_attr(JUST_BESTOW_OPTION_NAME_CLIENT_NAME), ['sanitize_callback' => 'sanitize_text_field',]);
    register_setting('just_bestow_settings_group', esc_attr(JUST_BESTOW_OPTION_NAME_CLIENT_EMAIL), ['sanitize_callback' => 'sanitize_text_field',]);
    register_setting('just_bestow_settings_group', esc_attr(JUST_BESTOW_OPTION_NAME_STRIPE_ACCOUNT), ['sanitize_callback' => 'sanitize_text_field',]);
    register_setting('just_bestow_settings_group', esc_attr(JUST_BESTOW_OPTION_NAME_STRIPE_OAUTH_URL), ['sanitize_callback' => 'sanitize_text_field',]);
    register_setting('just_bestow_settings_group', esc_attr(JUST_BESTOW_OPTION_NAME_STRIPE_PUBLISHABLE_KEY), ['sanitize_callback' => 'sanitize_text_field',]);


    add_settings_field(
      'just_bestow_mode',
      'Mode',
      [__CLASS__, 'render_mode_field'],
      'just-bestow-settings',
      'just_bestow_main_section'
    );

    add_settings_field(
      'stripe_account',
      'Stripe Account ID',
      [__CLASS__, 'render_stripe_account_field'],
      'just-bestow-settings',
      'just_bestow_main_section'
    );

    add_settings_field(
      'stripe_publishable_key',
      'Stripe Publishable Key',
      [__CLASS__, 'render_stripe_publishable_key_field'],
      'just-bestow-settings',
      'just_bestow_main_section'
    );


    add_settings_section(
      'just_bestow_main_section',
      'Main Settings',
      null,
      'just-bestow-settings'
    );

    add_settings_field(
      'fetch_url',
      'Fetch URL',
      [__CLASS__, 'render_fetch_url_field'],
      'just-bestow-settings',
      'just_bestow_main_section'
    );

    add_settings_field(
      'stripe_oauth_url',
      'Stripe OAuth URL',
      [__CLASS__, 'render_stripe_oauth_url_field'],
      'just-bestow-settings',
      'just_bestow_main_section'
    );

    add_settings_field(
      'client_email',
      'Client Email',
      function () {
        $value = esc_attr(get_option(JUST_BESTOW_OPTION_NAME_CLIENT_EMAIL, ''));
        echo '<input type="text" value="' . esc_attr($value) . '" class="regular-text" readonly />';
      },
      'just-bestow-settings',
      'just_bestow_main_section'
    );

    add_settings_field(
      'client_name',
      'Client Name',
      function () {
        $value = esc_attr(get_option(JUST_BESTOW_OPTION_NAME_CLIENT_NAME, ''));
        echo '<input type="text" value="' . esc_attr($value) . '" class="regular-text" readonly />';
      },
      'just-bestow-settings',
      'just_bestow_main_section'
    );
  }


  public static function admin_enqueue_scripts($hook_suffix)
  {
    if ($hook_suffix === 'toplevel_page_just-bestow-settings') {
      wp_enqueue_style(
        'just-bestow-admin-style',
        plugin_dir_url(__FILE__) . 'css/just-bestow-admin.css',
        [],
        '1.0.0'
      );

      wp_enqueue_script(
        'just-bestow-admin-script',
        plugin_dir_url(__FILE__) . 'js/just-bestow-admin.js',
        ['jquery'],
        '1.0.0',
        true
      );

      /* The inline script is used to construct the stripe oauth url. */
      wp_register_script(
        'just-bestow-inline-script',
        plugin_dir_url(__FILE__) . 'js/just-bestow-settings.js',
        ['jquery'],
        '1.0.0',
        true
      );

      $stripe_oauth_url = trim(get_option(JUST_BESTOW_OPTION_NAME_STRIPE_OAUTH_URL, ''));
      $mode = get_option(JUST_BESTOW_OPTION_NAME_MODE);
      $clean_stripe_url = ltrim($stripe_oauth_url, '/');

      wp_localize_script(
        'just-bestow-inline-script',
        'JB_STRIPE_VARS',
        [
          'stripe_url' => $clean_stripe_url,
          'mode'       => $mode,
          'nonce'      => wp_create_nonce('jb_stripe_oauth_redirect'),
        ]
      );

      wp_enqueue_script('just-bestow-inline-script');
    }
  }


  public static function render_mode_field()
  {
    $value = esc_attr(get_option(JUST_BESTOW_OPTION_NAME_MODE, 'test'));
?>
    <select name="<?php echo esc_attr(JUST_BESTOW_OPTION_NAME_MODE); ?>">
      <option value="test" <?php esc_attr(selected($value, 'test')); ?>>Test</option>
      <option value="live" <?php esc_attr(selected($value, 'live')); ?>>Live</option>
    </select>
  <?php
  }

  public static function render_fetch_url_field()
  {
    $value = esc_attr(get_option(JUST_BESTOW_OPTION_NAME_FETCH_URL, ''));
    echo '<input type="text" name="' . esc_attr(JUST_BESTOW_OPTION_NAME_FETCH_URL) . '" value="' . esc_attr($value) . '" class="regular-text" />';
  }

  public static function render_stripe_oauth_url_field()
  {
    $value = esc_attr(get_option(JUST_BESTOW_OPTION_NAME_STRIPE_OAUTH_URL, '', "https://api.justbestow.com/t2pw/connect"));
    echo '<input type="text" name="' . esc_attr(JUST_BESTOW_OPTION_NAME_STRIPE_OAUTH_URL) . '" value="' . esc_attr($value) . '" class="regular-text" />';
  }

  public static function render_stripe_account_field()
  {
    $value = esc_attr(get_option(JUST_BESTOW_OPTION_NAME_STRIPE_ACCOUNT, ''));
    echo '<input type="text" name="' . esc_attr(JUST_BESTOW_OPTION_NAME_STRIPE_ACCOUNT) . '" value="' . esc_attr($value) . '" class="regular-text" readonly/>';
  }

  public static function render_stripe_publishable_key_field()
  {
    $value = esc_attr(get_option(JUST_BESTOW_OPTION_NAME_STRIPE_PUBLISHABLE_KEY, ''));
    echo '<input type="text" name="' . esc_attr(JUST_BESTOW_OPTION_NAME_STRIPE_PUBLISHABLE_KEY) . '" value="' . esc_attr($value) . '" class="regular-text" readonly/>';
  }


  public static function render_settings_page()
  {
    if (
      isset($_GET['_wpnonce']) &&
      wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'jb_stripe_oauth_redirect') &&
      current_user_can('manage_options') &&
      isset($_GET['_stripe_account_id'])
    ) {
      update_option(
        JUST_BESTOW_OPTION_NAME_STRIPE_ACCOUNT,
        sanitize_text_field(wp_unslash($_GET['_stripe_account_id']))
      );

      if (isset($_GET['_client_email'])) {
        update_option(
          JUST_BESTOW_OPTION_NAME_CLIENT_EMAIL,
          sanitize_email(wp_unslash($_GET['_client_email']))
        );
      }

      if (isset($_GET['_client_name'])) {
        update_option(
          JUST_BESTOW_OPTION_NAME_CLIENT_NAME,
          sanitize_text_field(wp_unslash($_GET['_client_name']))
        );
      }

      if (isset($_GET['_client_type'])) {
        update_option(
          JUST_BESTOW_OPTION_NAME_CLIENT_TYPE,
          sanitize_text_field(wp_unslash($_GET['_client_type']))
        );
      }

      update_option('just_bestow_show_success_notice', true);
    }


    $stripe_oauth_url = trim(get_option(JUST_BESTOW_OPTION_NAME_STRIPE_OAUTH_URL, ''));
    $mode = get_option(JUST_BESTOW_OPTION_NAME_MODE);
    $is_enabled = ! empty($stripe_oauth_url);

    $clean_stripe_url = ltrim($stripe_oauth_url, '/');

    if (
      isset($_SERVER['REQUEST_METHOD'], $_POST['_wpnonce']) &&
      'POST' === $_SERVER['REQUEST_METHOD'] &&
      isset($_POST['disconnect_stripe']) &&
      wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'disconnect_stripe_account')
    ) {
      delete_option(JUST_BESTOW_OPTION_NAME_STRIPE_ACCOUNT);
      delete_option(JUST_BESTOW_OPTION_NAME_CLIENT_TYPE);
      echo '<div class="notice notice-success is-dismissible"><p>Stripe account disconnected successfully.</p></div>';
    }

    if (
      isset($_SERVER['REQUEST_METHOD'], $_POST['_wpnonce'], $_POST['mode']) &&
      'POST' === $_SERVER['REQUEST_METHOD'] &&
      wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'change_mode')
    ) {
      update_option(JUST_BESTOW_OPTION_NAME_MODE, sanitize_text_field(wp_unslash($_POST['mode'])));
      $mode = get_option(JUST_BESTOW_OPTION_NAME_MODE);
    }


  ?>
    <div class="wrap hidden">
      <h1>Just Bestow Settings</h1>
      <form method="post" action="options.php">
        <input type="hidden" id="current-mode" value="<?php echo esc_attr(get_option(JUST_BESTOW_OPTION_NAME_MODE, 'test')); ?>" />

        <?php
        settings_fields('just_bestow_settings_group');
        do_settings_sections('just-bestow-settings');
        submit_button();
        ?>
      </form>
      <p>
        <button
          id="connect-stripe-btn"
          class="button button-primary"
          <?php echo $is_enabled ? '' : 'disabled'; ?>
          data-stripe-url="<?php echo esc_attr($clean_stripe_url); ?>">Connect with Stripe</button>
      </p>
    </div>
    <section>
      <section>
        <div class="container">
          <section class="header-section">
            <h1>Just Bestow Settings</h1>

            <?php
            $stripe_account = get_option(JUST_BESTOW_OPTION_NAME_STRIPE_ACCOUNT, '');
            $mode = get_option(JUST_BESTOW_OPTION_NAME_MODE);
            $stripe_connected = !empty($stripe_account);
            if (get_option('just_bestow_show_success_notice')) {
            ?>
              <div class="add-account-section connection-feedback" style="margin-bottom: 3px;">
                <p>
                  <strong>Your Stripe account has been successfully connected!</strong><br>
                  Please check your email for further instructions on adding campaigns.
                  If you haven’t received an email, please contact
                  <a href="mailto:info@ayatsolutions.com">info@ayatsolutions.com</a>
                  or visit our <a href="https://ayatsolutions.com/contact-us" target="_blank">contact page</a>.
                </p>
              </div>
            <?php } ?>
            <div class="add-account-section">
              <div class="stripe-logo">
                <span id="account-status-text">
                  <?php echo $stripe_connected ? 'STRIPE ACCOUNT CONNECTED' : 'ADD STRIPE ACCOUNT'; ?>
                </span>
              </div>
              <div class="forms-wrapper">

                <?php if ($stripe_connected): ?>
                  <form method="post">
                    <?php wp_nonce_field('disconnect_stripe_account'); ?>
                    <input type="hidden" name="disconnect_stripe" value="1">
                    <button type="submit" class="disconnect-main-btn" id="disconnect-btn">
                      Disconnect Stripe
                    </button>
                  </form>
                <?php else: ?>
                  <button class="connect-btn" id="connect-nonprofit-btn">
                    Connect as Non-Profit
                  </button>

                  <button class="connect-btn" id="connect-profit-btn" style="background:#4CAF50;">
                    Connect as Business
                  </button>
                <?php endif; ?>

                <form method="post" class="hidden">
                  <?php wp_nonce_field('change_mode'); ?>

                  <?php if ($mode == "live"): ?>

                    <input type="hidden" name="mode" value="test">
                    <button type="submit" class="test-mode-btn">
                      Start test mode
                    </button>
                  <?php else: ?>
                    <input type="hidden" name="mode" value="live">
                    <button type="submit" class="live-mode-btn">
                      Start live mode
                    </button>
                  <?php endif; ?>
                </form>
              </div>


            </div>


          </section>
          <section class="connected-accounts" id="accounts-section">
            <h2 class="section-title">Connected Accounts</h2>
            <?php if (!$stripe_connected): ?>
              <div class="blur-overlay">
                <div class="blur-message">
                  <h3>No Stripe Account Connected</h3>
                  <p>Connect your Stripe account to view and manage your payment settings.</p>
                </div>
              </div>
            <?php endif; ?>
            <div class="account-card <?php echo !$stripe_connected ? 'blurred-content' : ''; ?>" id="account-content">
              <div class="account-header">
                <span class="account-name">
                  <?php echo esc_html(get_option(JUST_BESTOW_OPTION_NAME_CLIENT_NAME, 'Anonymouse')); ?>
                </span>
                <span class="checkmark"></span>
              </div>

              <div class="account-details">
                <div class="detail-row">
                  <span class="detail-label">Account email:</span>
                  <span class="detail-value">
                    <?php echo esc_html(get_option(JUST_BESTOW_OPTION_NAME_CLIENT_EMAIL, 'not set')); ?>
                  </span>
                </div>

                <div class="detail-row">
                  <span class="detail-label">Account ID:</span>
                  <span class="detail-value"><?php echo esc_html($stripe_account); ?></span>
                </div>

                <div class="detail-row">
                  <span class="detail-label">Organization Type:</span>
                  <span class="detail-value" style="text-transform: uppercase;">
                    <?php echo esc_html(get_option(JUST_BESTOW_OPTION_NAME_CLIENT_TYPE, 'not set')); ?>
                  </span>
                </div>


                <div class="detail-row">
                  <span class="detail-label">Connection Method:</span>
                  <span class="detail-value">Stripe Connect</span>
                </div>

                <div class="detail-row hidden">
                  <span class="detail-label">
                    Statement Descriptor: <span class="info-icon"></span>
                  </span>
                  <div class="statement-row">
                    <span class="detail-value">NAIFCENTER DONATION</span>
                  </div>
                </div>
              </div>
            </div>
          </section>
        </div>
      </section>
    </section>
<?php
  }
}
