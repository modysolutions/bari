<?php
/**
 * Plugin Name: Tamarind Search functionalities
 * Description: A plugin to manage Tamarind Search functionalities
 * Author: Omitsis
 * Author URI:      https://www.omitsis.com
 * Text Domain: tamarind-search
 * Domain Path: /languages
 * version: 1.1.0
 *
 * @package Tamarind_Search
 */

namespace tamarind_search;

defined( 'ABSPATH' ) || exit;

$version = '1.1.0';
if ( file_exists( dirname( __FILE__ ) . '/dist/css/tamarind-search.min.css' ) ) {
    $version = filemtime( dirname( __FILE__ ) . '/dist/css/tamarind-search.min.css' );
}
define( 'TM_SEARCH_VERSION', $version );
define( 'PLUGIN_URI', plugin_dir_url( __FILE__ ) );
const PLUGIN_PATH = __DIR__;

// Includes for Saved Searches.
require_once PLUGIN_PATH . '/includes/plugin-actions.php';
require_once PLUGIN_PATH . '/includes/permalinks.php';
require_once PLUGIN_PATH . '/includes/acf-options.php';
require_once PLUGIN_PATH . '/includes/saved-searches.php';
require_once PLUGIN_PATH . '/includes/general-settings.php';
require_once PLUGIN_PATH . '/includes/search-history-db.php';
require_once PLUGIN_PATH . '/includes/search-history-logger.php';
require_once PLUGIN_PATH . '/includes/indexer.php';
require_once PLUGIN_PATH . '/includes/searchform.php';

init_actions( __FILE__ );
add_action_route_search();

// Includes for Search Redirects.
require_once PLUGIN_PATH . '/includes/search-redirect.php';

/**
 * Enqueue plugin styles and scripts.
 *
 * @return void
 */
function wp_enqueue_scripts(): void {
    $file_path   = PLUGIN_PATH . '/dist/search.asset.php';
    $assets_file = null;
    if ( file_exists( $file_path ) ) {
        $assets_file = require_once $file_path;
    }

    if ( null === $assets_file ) {
        return;
    }

    wp_register_script(
        'tamarind-search',
        PLUGIN_URI . '/dist/search.js',
        $assets_file['dependencies'],
        $assets_file['version'],
        [ 'in_footer' => true ]
    );

    wp_register_style(
        'tamarind-search',
        PLUGIN_URI . '/dist/search.css',
        array(),
        $assets_file['version'],
    );
    wp_enqueue_script( 'tamarind-search' );
    wp_enqueue_style( 'tamarind-search' );
}

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\wp_enqueue_scripts' );

add_action( 'save_post_post', function ( $post_id ) {
    if ( wp_is_post_revision( $post_id ) ) {
        return;
    }
    $indexer = new \tamarind_search\Indexer();
    $indexer->sync_post( $post_id );
} );
