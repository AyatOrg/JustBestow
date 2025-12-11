<?php

/**
 * Uninstall script for Just Bestow plugin
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
  exit;
}

delete_option('justbestow_mode');
delete_option('justbestow_fetch_url');
delete_option('justbestow_client_type');
delete_option('justbestow_client_name');
delete_option('justbestow_client_email');
delete_option('justbestow_stripe_account');
delete_option('justbestow_stripe_oauth_url');
delete_option('justbestow_stripe_publishable_key');
