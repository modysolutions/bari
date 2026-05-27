<?php
/**
 * Plugin Name: Tamarind Events
 * Description: Plugin to manage Events.
 * Version:     1.1.0
 * Author:      Omitsis
 * Author URI:  https://www.omitsis.com
 * Text Domain: tm-events
 * Domain Path: /languages
 *
 * @package     Tamarind_Events
 */

namespace tamarind_events;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$version = '1.1.0';
if(file_exists(dirname(__FILE__) . '/dist/css/tamarind-events.min.css')) {
    $version = filemtime(dirname(__FILE__) . '/dist/css/tamarind-events.min.css');
}
define('TM_EVENTS_VERSION', $version);
const PLUGIN_PATH = __DIR__;

// Additional files.
require_once PLUGIN_PATH . '/includes/cpt.php';
require_once PLUGIN_PATH . '/includes/acf-options.php';
require_once PLUGIN_PATH . '/includes/template-queries.php';
require_once PLUGIN_PATH . '/includes/upcoming-events.php';
require_once PLUGIN_PATH . '/includes/events-by-month.php';

/**
 * Enqueue the plugin styles.
 */
function events_enqueue_styles() {
	$plugin_url = plugin_dir_url( __FILE__ );

	wp_enqueue_style( 'tm-events', $plugin_url . 'dist/css/tamarind-events.min.css', array(), TM_EVENTS_VERSION );
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\events_enqueue_styles' );
