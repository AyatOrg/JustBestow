<?php

const OPTION_NAME_MODE = 't2pw_mode';
const OPTION_NAME_FETCH_URL = 't2pw_fetch_url';
const OPTION_NAME_CLIENT_TYPE = 't2pw_client_type';
const OPTION_NAME_CLIENT_NAME = 't2pw_client_name';
const OPTION_NAME_CLIENT_EMAIL = 't2pw_client_email';
const OPTION_NAME_STRIPE_ACCOUNT = 't2pw_stripe_account';
const OPTION_NAME_STRIPE_OAUTH_URL = 't2pw_stripe_oauth_url';
const OPTION_NAME_STRIPE_PUBLISHABLE_KEY = 't2pw_stripe_publishable_key';
class T2PWidgets_Settings
{



  public static function init()
  {
    add_action('admin_menu', [__CLASS__, 'add_menu_page']);
    add_action('admin_init', [__CLASS__, 'register_settings']);
  }

  public static function add_menu_page()
  {
    add_menu_page(
      'Just Bestow',
      'Just Bestow',
      'manage_options',
      't2pw-settings',
      [__CLASS__, 'render_settings_page'],
      'dashicons-heart',
      80
    );
  }

  public static function register_settings()
  {
    register_setting('t2pw_settings_group', OPTION_NAME_MODE);
    register_setting('t2pw_settings_group', OPTION_NAME_FETCH_URL);
    register_setting('t2pw_settings_group', OPTION_NAME_CLIENT_NAME);
    register_setting('t2pw_settings_group', OPTION_NAME_CLIENT_EMAIL);
    register_setting('t2pw_settings_group', OPTION_NAME_STRIPE_ACCOUNT);
    register_setting('t2pw_settings_group', OPTION_NAME_STRIPE_OAUTH_URL);
    register_setting('t2pw_settings_group', OPTION_NAME_STRIPE_PUBLISHABLE_KEY);


    add_settings_field(
      't2pw_mode',
      'Mode',
      [__CLASS__, 'render_mode_field'],
      't2pw-settings',
      't2pw_main_section'
    );

    add_settings_field(
      'stripe_account',
      'Stripe Account ID',
      [__CLASS__, 'render_stripe_account_field'],
      't2pw-settings',
      't2pw_main_section'
    );

    add_settings_field(
      'stripe_publishable_key',
      'Stripe Publishable Key',
      [__CLASS__, 'render_stripe_publishable_key_field'],
      't2pw-settings',
      't2pw_main_section'
    );


    add_settings_section(
      't2pw_main_section',
      'Main Settings',
      null,
      't2pw-settings'
    );

    add_settings_field(
      'fetch_url',
      'Fetch URL',
      [__CLASS__, 'render_fetch_url_field'],
      't2pw-settings',
      't2pw_main_section'
    );

    add_settings_field(
      'stripe_oauth_url',
      'Stripe OAuth URL',
      [__CLASS__, 'render_stripe_oauth_url_field'],
      't2pw-settings',
      't2pw_main_section'
    );

    add_settings_field(
      'client_email',
      'Client Email',
      function () {
        $value = esc_attr(get_option(OPTION_NAME_CLIENT_EMAIL, ''));
        echo '<input type="text" value="' . $value . '" class="regular-text" readonly />';
      },
      't2pw-settings',
      't2pw_main_section'
    );

    add_settings_field(
      'client_name',
      'Client Name',
      function () {
        $value = esc_attr(get_option(OPTION_NAME_CLIENT_NAME, ''));
        echo '<input type="text" value="' . $value . '" class="regular-text" readonly />';
      },
      't2pw-settings',
      't2pw_main_section'
    );
  }

  public static function render_mode_field()
  {
    $value = esc_attr(get_option(OPTION_NAME_MODE, 'test'));
?>
    <select name="<?php echo OPTION_NAME_MODE; ?>">
      <option value="test" <?php selected($value, 'test'); ?>>Test</option>
      <option value="live" <?php selected($value, 'live'); ?>>Live</option>
    </select>
    <?php
  }

  public static function render_fetch_url_field()
  {
    $value = esc_attr(get_option(OPTION_NAME_FETCH_URL, ''));
    echo '<input type="text" name="' . OPTION_NAME_FETCH_URL . '" value="' . $value . '" class="regular-text" />';
  }

  public static function render_stripe_oauth_url_field()
  {
    $value = esc_attr(get_option(OPTION_NAME_STRIPE_OAUTH_URL, '', "https://api.justbestow.com/t2pw/connect"));
    echo '<input type="text" name="' . OPTION_NAME_STRIPE_OAUTH_URL . '" value="' . $value . '" class="regular-text" />';
  }

  public static function render_stripe_account_field()
  {
    $value = esc_attr(get_option(OPTION_NAME_STRIPE_ACCOUNT, ''));
    echo '<input type="text" name="' . OPTION_NAME_STRIPE_ACCOUNT . '" value="' . $value . '" class="regular-text" readonly/>';
  }

  public static function render_stripe_publishable_key_field()
  {
    $value = esc_attr(get_option(OPTION_NAME_STRIPE_PUBLISHABLE_KEY, ''));
    echo '<input type="text" name="' . OPTION_NAME_STRIPE_PUBLISHABLE_KEY . '" value="' . $value . '" class="regular-text" readonly/>';
  }


  public static function render_settings_page()
  {
    if (isset($_GET['_stripe_account_id'])) {
      $incoming_account_id = sanitize_text_field($_GET['_stripe_account_id']);
      update_option(OPTION_NAME_STRIPE_ACCOUNT, $incoming_account_id);
      /* one time message */
      update_option('t2pw_show_success_notice', true);
    }

    if (isset($_GET['_client_email'])) {
      $incoming_email = sanitize_email($_GET['_client_email']);
      update_option(OPTION_NAME_CLIENT_EMAIL, $incoming_email);
    }

    if (isset($_GET['_client_name'])) {
      $incoming_name = sanitize_text_field($_GET['_client_name']);
      update_option(OPTION_NAME_CLIENT_NAME, $incoming_name);
    }

    if (isset($_GET['_client_type'])) {
      $incoming_type = sanitize_text_field($_GET['_client_type']);
      update_option(OPTION_NAME_CLIENT_TYPE, $incoming_type);
    }



    $stripe_oauth_url = trim(get_option(OPTION_NAME_STRIPE_OAUTH_URL, ''));
    $mode = get_option(OPTION_NAME_MODE);
    $is_enabled = !empty($stripe_oauth_url);

    $clean_stripe_url = ltrim($stripe_oauth_url, '/');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['disconnect_stripe']) && wp_verify_nonce($_POST['_wpnonce'], 'disconnect_stripe_account')) {
      delete_option(OPTION_NAME_STRIPE_ACCOUNT);
      delete_option(OPTION_NAME_CLIENT_TYPE);
      echo '<div class="notice notice-success is-dismissible"><p>Stripe account disconnected successfully.</p></div>';
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mode']) && wp_verify_nonce($_POST['_wpnonce'], 'change_mode')) {
      update_option(OPTION_NAME_MODE, $_POST['mode']);
      $mode = get_option(OPTION_NAME_MODE);
    }

    if (get_option('t2pw_show_success_notice')) {
    ?>
      <div class="notice notice-success is-dismissible">
        <p>
          <strong>Your Stripe account has been successfully connected!</strong><br>
          Please check your email for further instructions on adding campaigns.
          If you haven’t received an email, please contact
          <a href="mailto:info@ayatsolutions.com">info@ayatsolutions.com</a>
          or visit our <a href="https://ayatsolutions.com/contact-us" target="_blank">contact page</a>.
        </p>
      </div>
    <?php
      /* remove the flag so message is hidden*/
      delete_option('t2pw_show_success_notice');
    }


    ?>
    <script>
      (function() {
        const url = new URL(window.location.href);
        ['_stripe_account_id', '_client_email', '_client_name'].forEach(param => {
          if (url.searchParams.has(param)) {
            url.searchParams.delete(param);
          }
        });
        window.history.replaceState({}, document.title, url.pathname + url.search);
      })();
    </script>
    <div class="wrap hidden">
      <h1>T2P Widgets Settings</h1>

      <form method="post" action="options.php">
        <input type="hidden" id="current-mode" value="<?php echo esc_attr(get_option(OPTION_NAME_MODE, 'test')); ?>" />

        <?php
        settings_fields('t2pw_settings_group');
        do_settings_sections('t2pw-settings');
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


      <style>
        .container * {
          box-sizing: border-box;
          margin: 0;
          padding: 0;
        }



        .container {
          width: 100%;
          margin: 0 auto;

        }

        .container>* {
          margin: 10px;
        }

        .header-section {
          background: white;
          padding: 20px;

          border-radius: 8px;
          margin-top: 10px;
          margin-bottom: 10px;
          box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .header-section h1 {
          font-size: 24px;
          font-weight: 600;
          margin-bottom: 20px;
          color: #1a1a1a;
        }

        .add-account-section {
          display: flex;
          align-items: center;
          justify-content: space-between;
          padding: 10px 20px;
          background: #f8f9fa;
          border-radius: 6px;
          border: 1px solid #e9ecef;
        }

        .stripe-logo {
          display: flex;
          align-items: center;
          gap: 12px;
          color: #6772e5;
          font-weight: 500;
        }

        .stripe-logo::before {
          content: "S";
          width: 32px;
          height: 32px;
          background: #6772e5;
          color: white;
          border-radius: 4px;
          display: flex;
          align-items: center;
          justify-content: center;
          font-weight: bold;
        }

        .connect-btn {
          background: #6772e5;
          color: white;
          border: none;
          padding: 8px 16px;
          border-radius: 4px;
          font-size: 14px;
          cursor: pointer;
          display: flex;
          align-items: center;
          gap: 6px;
        }

        .connect-btn::before {
          content: "S";
          font-weight: bold;
        }

        .connect-btn:hover {
          background: #5a67d8;
        }

        /* Disconnect button styles */
        .disconnect-main-btn {
          background: #dc3545;
          color: white;
          border: none;
          padding: 8px 16px;
          border-radius: 4px;
          font-size: 14px;
          cursor: pointer;
          display: flex;
          align-items: center;
          gap: 6px;
        }

        .disconnect-main-btn::before {
          content: "⚠";
          font-weight: bold;
        }

        .disconnect-main-btn:hover {
          background: #c82333;
        }

        .connected-accounts {
          background: white;
          border-radius: 8px;
          box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
          overflow: hidden;
          position: relative;
        }

        /* Blur effect when not connected */
        .blurred-content {
          filter: blur(3px);
          pointer-events: none;
          opacity: 0.6;
        }

        .blur-overlay {
          position: absolute;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          background: rgba(255, 255, 255, 0.8);
          display: flex;
          align-items: center;
          justify-content: center;
          z-index: 10;
        }

        .blur-message {
          background: white;
          padding: 20px;
          border-radius: 8px;
          box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
          text-align: center;
          max-width: 300px;
        }

        .blur-message h3 {
          margin-bottom: 10px;
          color: #333;
        }

        .blur-message p {
          color: #666;
          font-size: 14px;
        }

        .section-title {
          padding: 20px;
          font-size: 18px;
          font-weight: 600;
          border-bottom: 1px solid #e9ecef;
        }

        .account-card {
          padding: 20px;
          border: 2px solid #28a745;
          border-radius: 6px;
          margin: 20px;
          background: #f8fff9;
          position: relative;
        }

        .account-header {
          display: flex;
          align-items: center;
          gap: 8px;
          margin-bottom: 16px;
        }

        .account-name {
          font-weight: 600;
          font-size: 16px;
        }

        .checkmark {
          width: 20px;
          height: 20px;
          background: #28a745;
          border-radius: 50%;
          display: flex;
          align-items: center;
          justify-content: center;
          color: white;
          font-size: 12px;
        }

        .checkmark::after {
          content: "✓";
        }

        .account-details {
          display: grid;
          gap: 12px;
          margin-bottom: 16px;
        }

        .detail-row {
          display: flex;
          flex-direction: column;
          gap: 4px;
        }

        .detail-label {
          font-size: 12px;
          color: #666;
          font-weight: 500;
        }

        .detail-value {
          font-size: 14px;
          color: #333;
        }

        .statement-row {
          display: flex;
          align-items: center;
          gap: 8px;
        }

        .edit-btn {
          background: none;
          border: none;
          color: #6772e5;
          font-size: 12px;
          cursor: pointer;
          text-decoration: underline;
        }

        .status-row {
          display: flex;
          align-items: center;
          gap: 12px;
          margin-bottom: 16px;
        }

        .status-connected {
          background: #28a745;
          color: white;
          padding: 4px 8px;
          border-radius: 4px;
          font-size: 12px;
          font-weight: 600;
        }

        .disconnect-btn {
          background: none;
          border: none;
          color: #666;
          font-size: 12px;
          cursor: pointer;
          text-decoration: underline;
        }

        .default-account {
          display: flex;
          align-items: center;
          gap: 8px;
          font-size: 14px;
        }

        .checkbox {
          width: 16px;
          height: 16px;
          border: 2px solid #ddd;
          border-radius: 3px;
          background: white;
        }

        .info-icon {
          width: 16px;
          height: 16px;
          background: #ccc;
          border-radius: 50%;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          font-size: 10px;
          color: white;
          margin-left: 4px;
        }

        .info-icon::after {
          content: "?";
        }

        /* Hidden class */
        .hidden {
          display: none;
        }
      </style>

      <section>
        <div class="container">
          <section class="header-section">
            <h1>Just Bestow Settings</h1>

            <?php
            $stripe_account = get_option(OPTION_NAME_STRIPE_ACCOUNT, '');
            $mode = get_option(OPTION_NAME_MODE);
            $stripe_connected = !empty($stripe_account);
            ?>

            <div class="add-account-section">
              <div class="stripe-logo">
                <span id="account-status-text">
                  <?php echo $stripe_connected ? 'STRIPE ACCOUNT CONNECTED' : 'ADD STRIPE ACCOUNT'; ?>
                </span>
              </div>
              <div class="forms-wrapper">
                <style>
                  .forms-wrapper {
                    display: flex;
                    gap: 10px;
                  }

                  .test-mode-btn,
                  .live-mode-btn {
                    color: white;
                    border: none;
                    padding: 8px 16px;
                    border-radius: 4px;
                    font-size: 14px;
                    cursor: pointer;
                    display: flex;
                    align-items: center;
                    gap: 6px;
                    font-weight: 500;
                    transition: background-color 0.2s ease;
                  }

                  .test-mode-btn {
                    background: #17a2b8;
                    /* Info blue */
                  }

                  .test-mode-btn:hover {
                    background: #138496;
                  }

                  .live-mode-btn {
                    background: #ffc107;
                    /* Warning yellow */
                    color: #212529;
                  }

                  .live-mode-btn:hover {
                    background: #e0a800;
                  }
                </style>
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
                  <?php echo esc_html(get_option(OPTION_NAME_CLIENT_NAME, 'Anonymouse')); ?>
                </span>
                <span class="checkmark"></span>
              </div>

              <div class="account-details">
                <div class="detail-row">
                  <span class="detail-label">Account email:</span>
                  <span class="detail-value">
                    <?php echo esc_html(get_option(OPTION_NAME_CLIENT_EMAIL, 'not set')); ?>
                  </span>
                </div>

                <div class="detail-row">
                  <span class="detail-label">Account ID:</span>
                  <span class="detail-value"><?php echo esc_html($stripe_account); ?></span>
                </div>

                <div class="detail-row">
                  <span class="detail-label">Organization Type:</span>
                  <span class="detail-value" style="text-transform: uppercase;">
                    <?php echo esc_html(get_option(OPTION_NAME_CLIENT_TYPE, 'not set')); ?>
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
      <script>
        (function() {
          function base64Encode(str) {
            return btoa(unescape(encodeURIComponent(str)))
              .replace(/\+/g, '-')
              .replace(/\//g, '_')
              .replace(/=+$/, '');
          }

          function redirectToStripe(type) {
            const baseUrl = "<?php echo esc_js($clean_stripe_url); ?>";
            if (!baseUrl) return alert("Stripe OAuth URL missing");

            const ru = base64Encode(window.location.href);
            const m = base64Encode("<?php echo esc_js($mode); ?>");
            const t = base64Encode(type);

            const sep = baseUrl.includes("?") ? "&" : "?";
            const finalUrl = `${baseUrl}${sep}_ru=${ru}&_m=${m}&_t=${t}`;

            window.location.href = finalUrl;
          }

          const nonProfitBtn = document.getElementById("connect-nonprofit-btn");
          const profitBtn = document.getElementById("connect-profit-btn");

          if (nonProfitBtn) nonProfitBtn.addEventListener("click", () => redirectToStripe("non-profit"));
          if (profitBtn) profitBtn.addEventListener("click", () => redirectToStripe("profit"));
        })();
      </script>
    </section>
<?php
  }
}
