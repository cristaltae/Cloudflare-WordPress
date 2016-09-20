<?php
/*
Plugin Name: CloudFlare
Plugin URI: http://www.cloudflare.com/wiki/CloudFlareWordPressPlugin
Description: CloudFlare integrates your blog with the CloudFlare platform.
Version: 3.0.2
Author: John Wineman, Furkan Yilmaz, Junade Ali (CloudFlare Team)
License: BSD-3-Clause
*/

require_once 'vendor/autoload.php';

if (!defined('ABSPATH')) { // Exit if accessed directly
    exit;
}


$cloudflareConfig = new CF\Integration\DefaultConfig(file_get_contents('config.js', true));
$cloudflareLogger = new CF\Integration\DefaultLogger($cloudflareConfig->getValue('debug'));
$cloudflareDataStore = new CF\WordPress\DataStore($cloudflareLogger);
$cloudflareWordpressAPI = new CF\WordPress\WordPressAPI($cloudflareDataStore);
$cloudflareWordpressIntegration = new CF\Integration\DefaultIntegration($cloudflareConfig, $cloudflareWordpressAPI, $cloudflareDataStore, $cloudflareLogger);

$cloudflareHooks = new \CF\WordPress\Hooks($cloudflareWordpressIntegration);

// Load Init Script
add_action('init', array($cloudflareHooks, 'init'), 1);

//Register proxy AJAX endpoint
add_action('wp_ajax_cloudflare_proxy', array($cloudflareHooks, 'initProxy'));

//Add CloudFlare Plugin homepage to admin settings menu
add_action('admin_menu', array($cloudflareHooks, 'cloudflareConfigPage'));

add_action('admin_init', array($cloudflareHooks, 'cloudflareAdminInit'));

//Add CloudFlare Plugin homepage to admin settings menu
add_action('plugin_action_links_cloudflare/cloudflare.php', array($cloudflareHooks, 'pluginActionLinks'));

// Load Activation Script
register_activation_hook(__FILE__, array($cloudflareHooks, 'checkVersionCompatibility'));

// Load Deactivation Script
register_deactivation_hook(__FILE__, array($cloudflareHooks, 'deactivate'));

// Load Uninstall Script
register_uninstall_hook(__FILE__, array('\CF\Hooks\Uninstall', 'init'));

// Load AutomaticCache
add_action('init', array('\CF\Hooks\AutomaticCache', 'init'));

// Enable HTTP2 Server Push
// Removed until the problems are indentified and fixed
// add_action('init', array('\CF\Hooks\HTTP2ServerPush', 'init'));
