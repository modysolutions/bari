<?php
/**
 * Plugin Name: Tamarind Notifications
 * Description: Plugin to manage Notifications for users.
 * Version:     1.1.0
 * Author:      Omitsis
 * Author URI:  https://www.omitsis.com
 * Text Domain: tm-notifications
 * Domain Path: /languages
 *
 * @package     Tamarind_Notifications
 */

namespace tamarind_notifications;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$version = '1.1.0';
if(file_exists(dirname(__FILE__) . '/dist/css/tamarind-notifications.min.css')) {
    $version = filemtime(dirname(__FILE__) . '/dist/css/tamarind-notifications.min.css');
}
define('TM_NOTIF_VERSION', $version);
const PLUGIN_PATH = __DIR__;

// Additional files.
require_once PLUGIN_PATH . '/includes/cpt.php';
require_once PLUGIN_PATH . '/includes/acf-options.php';
require_once PLUGIN_PATH . '/includes/functions.php';

/**
 * Enqueue the plugin styles.
 */
function notifications_enqueue_styles() {
	$plugin_url = plugin_dir_url( __FILE__ );

	wp_enqueue_style( 'tm-notifications', $plugin_url . 'dist/css/tamarind-notifications.min.css', array(),
        TM_NOTIF_VERSION );

	wp_register_style(
		'tm-notifications-css',
		$plugin_url . 'dist/css/tamarind-notifications.min.css',
		array(),
		TM_NOTIF_VERSION
	);
	wp_enqueue_style( 'tm-notifications-css' );

	wp_register_script(
		'tm-notifications-js',
		$plugin_url . 'dist/js/tamarind-notifications.min.js',
		array(),
		TM_NOTIF_VERSION,
		true
	);

	wp_localize_script(
		'tm-notifications-js',
		'tamarindNotifications',
		array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'tamarind_notifications_nonce' ),
		)
	);

	wp_enqueue_script( 'tm-notifications-js' );
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\notifications_enqueue_styles' );
