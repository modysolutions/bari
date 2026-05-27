<?php
/**
 * Plugin Name:     Tamarind User Home
 * Plugin URI:      https://www.omitsis.com
 * Description:     User Home Settings for Tamarind.
 * Author:          Omitsis
 * Author URI:      https://www.omitsis.com
 * Text Domain:     tamarind-user-home
 * Domain Path:     /languages
 * Version:         1.1.0
 *
 * @package         Tamarind_UserArea
 */

namespace tamarind_userhome;

$version = '1.1.0';
if(file_exists(dirname(__FILE__) . '/dist/css/tamarind-user-home.min.css')) {
    $version = filemtime(dirname(__FILE__) . '/dist/css/tamarind-user-home.min.css');
}
define('TM_UH_VERSION', $version);
defined( 'ABSPATH' ) || exit;

/**
 * Path to the plugin
 */
const PLUGIN_PATH = __DIR__;

// Includes.
require_once PLUGIN_PATH . '/includes/acf-options.php';
require_once PLUGIN_PATH . '/includes/display-user-home.php';
require_once PLUGIN_PATH . '/includes/template-queries.php';

/**
 * Enqueue the styles and scripts
 */
function tamarind_userhome_enqueue_scripts() {
	$plugin_url = plugin_dir_url( __FILE__ );

	wp_enqueue_style( 'tm-user-home', $plugin_url . 'dist/css/tamarind-user-home.min.css', array(), TM_UH_VERSION );
	wp_enqueue_script( 'tm-user-home', $plugin_url . 'dist/js/tamarind-user-home.min.js', array(), TM_UH_VERSION,
        true );
}

/**
 * Detect if the current page is from the Home Page
 */
add_action(
	'wp',
	function () {
		if ( is_front_page() ) {
			add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\tamarind_userhome_enqueue_scripts' );
		}
	}
);
