=== Just Bestow ===
Contributors: ayatsolutions
Tags: donations, stripe
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Short Description: Displays a donation form on your site via the block editor.

== Description ==
Just Bestow is a lightweight and efficient donation plugin designed to help organizations accept donations quickly and securely. It integrates seamlessly with Stripe using a simple onboarding process, allowing you to connect your Stripe account in minutes.

This plugin provides a clean, minimal setup experience inside WordPress while keeping advanced management features in the external Just Bestow Dashboard. After connecting your account, you can manage campaigns, view reports, and customize donation experiences directly from your Just Bestow Dashboard — not inside WordPress.

You can easily display your donation form anywhere on your site using the WordPress Block Editor. Simply edit any page or post, open the block selector, and search for the “Just Bestow” block. Add the block to your content, and the donation widget will automatically appear on your site using your connected Stripe account.

== External Services ==
This plugin relies on two external services to function properly:

### Just Bestow (API + Dashboard)
The plugin connects to the Just Bestow API to manage campaigns, store configuration data, and initiate donation transactions.  
It sends data such as campaign details, configuration settings, and donation-related information only when these features are used.  
Advanced campaign management, reporting, and customization are handled through the external Just Bestow Dashboard.

- Just Bestow Terms of Service: https://ayatsolutions.com/justbestow/justbestow-terms  
- Just Bestow Privacy Policy: https://ayatsolutions.com/justbestow/justbestow-privacy  

### Stripe (Payment Processing via OAuth)
To process donations securely, the plugin uses Stripe.  
Administrators are redirected to Stripe’s OAuth flow to connect their Stripe account to Just Bestow.  
All donor payment details are handled directly by Stripe — this plugin does not store or process any sensitive payment information.

- Stripe Terms of Service: https://stripe.com/legal  
- Stripe Privacy Policy: https://stripe.com/privacy  

== Installation ==

1. Upload the `justbestow` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Visit Just Bestow settings to configure your Stripe account.
4. Add the Just Bestow Donation Form to your posts using the Block Editor.

== Changelog ==
= 1.0.0 =
* Initial release
