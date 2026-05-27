<?php
/**
 * Plugin Name: Tamarind Favourites
 * Description: Plugin to manage favorite content.
 * Version:     1.1.1
 * Author:      Omitsis
 * Author URI:  https://www.omitsis.com
 * Text Domain: tm-favourites
 * Domain Path: /languages
 *
 * @package     Tamarind_Favourites
 */

namespace tamarind_favourites;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$version = '1.1.1';
if(file_exists(dirname(__FILE__) . '/dist/css/tamarind-favourites.min.css')) {
    $version = filemtime(dirname(__FILE__) . '/dist/css/tamarind-favourites.min.css');
}
define('TM_FAVOURITES_VERSION', $version);
const PLUGIN_PATH = __DIR__;

/**
 * Enqueue plugin styles and scripts.
 *
 * @return void
 */
function tamarind_favourites_enqueue_scripts() {
	$plugin_url = plugin_dir_url( __FILE__ );

	wp_enqueue_style( 'tm-favourites', $plugin_url . 'dist/css/tamarind-favourites.min.css', array(),
        TM_FAVOURITES_VERSION );
	wp_enqueue_script( 'tm-favourites', $plugin_url . 'dist/js/tamarind-favourites.min.js', array(),
        TM_FAVOURITES_VERSION,
        true );

	// Get My Favourites url.
	$my_favourites_url = get_menu_link_by_key( 'my_favourites' );

	wp_localize_script(
		'tm-favourites',
		'tmFavourites',
		array(
			'ajax_url'          => admin_url( 'admin-ajax.php' ),
			'nonce'             => wp_create_nonce( 'tm_favourites_nonce' ),
			'my_favourites_url' => $my_favourites_url ? esc_url( $my_favourites_url ) : '#',
		)
	);
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\tamarind_favourites_enqueue_scripts', 20, 1 );


// Additional files.
require_once PLUGIN_PATH . '/includes/plugin-actions.php';
require_once PLUGIN_PATH . '/includes/permalinks.php';
require_once PLUGIN_PATH . '/includes/acf-options.php';
require_once PLUGIN_PATH . '/includes/historical-logging.php';
require_once PLUGIN_PATH . '/includes/icon-favourite.php';
require_once PLUGIN_PATH . '/includes/filter-favourites.php';

/**
 * Init the actions
 */
init_actions( __FILE__ );
add_action_route_favourites();
