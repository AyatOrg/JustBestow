<?php

/**
 * Uninstall script for Just Bestow plugin
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
  exit;
}

delete_option('just_bestow_mode');
delete_option('just_bestow_fetch_url');
delete_option('just_bestow_client_type');
delete_option('just_bestow_client_name');
delete_option('just_bestow_client_email');
delete_option('just_bestow_stripe_account');
delete_option('just_bestow_stripe_oauth_url');
delete_option('just_bestow_stripe_publishable_key');
