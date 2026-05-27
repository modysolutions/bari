<?php
/**
 * Plugin name: Tamarind Subscriptions
 * Plugin URI: https://tamarindintelligence.com
 * Description: Manage subscriptions for Tamarind Intelligence Websites
 * Author: Tamarind Intelligence
 * Author URI: https://tamarindintelligence.com
 * Text Domain: tamarind-subscriptions
 * Domain Path: /languages
 * Version: 1.0.0
 *
 * @package tamarind_subscriptions
 */

namespace tamarind_subscriptions;

defined( 'ABSPATH' ) || exit;

$version = '1.0.0';
if(file_exists(dirname(__FILE__) . '/dist/css/tamarind-subscriptions.min.css')) {
    $version = filemtime(dirname(__FILE__) . '/dist/css/tamarind-subscriptions.min.css');
}
define('VERSION', $version);
const TMS_PLUGIN_NAME = 'tamarind-subscriptions';

define( 'TMS_PLUGIN', __FILE__ );
define( 'TMS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'TMS_PLUGIN_DIR_URL', plugin_dir_url( __FILE__ ) );

// TODO: Add into modules.
require_once TMS_PLUGIN_DIR . 'includes/acf-options.php';

/* Load modules */
\tamarind_base\load_modules( TMS_PLUGIN_DIR );

/**
 * Enqueue the plugin styles.
 */
function subscriptions_enqueue_styles() {
	$plugin_url = plugin_dir_url( __FILE__ );

	wp_enqueue_style( 'tm-subscriptions', $plugin_url . 'dist/css/tamarind-subscriptions.min.css', array(), VERSION );
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\subscriptions_enqueue_styles' );
