<?php
/**
 * Plugin Name: Tamarind Mapsvg
 * Description: A plugin to manage Tamarind Mapsvg custom JS
 * Author:      Tamarind Intelligence
 * Author URI:  https://tamarindintelligence.com
 * Text Domain: tamarind-mapsvg
 * Domain Path: /languages
 * Version:     1.2.1
 *
 * @package tamarind_templates
 */

namespace tamarind_mapsvg;

defined( 'ABSPATH' ) || die;

/**
 * Version number of the omitsis base plugin
 */
$version = '1.2.1';
if(file_exists(dirname(__FILE__) . '/dist/css/map-svg-v8.js')) {
    $version = filemtime(dirname(__FILE__) . '/dist/css/map-svg-v8.js');
}
define('TM_MAPSVG_VERSION', $version);

define( 'TAMARIND_MAPSVG_URL', plugin_dir_url( __FILE__ ) );
define( 'TAMARIND_MAPSVG_PATH', plugin_dir_path( __FILE__ ) );

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\tamarind_mapsvg_enqueue_scripts' );

/**
 * Enqueue the scripts
 *
 * @return void
 */
function tamarind_mapsvg_enqueue_scripts() {
	if (
		// for single posts that are map or regulatory tracker -- wich is the SAME.
		( is_single() && has_term( 'map', 'content_types' ) ) ||
		( is_single() && has_term( 'regulatory-trackers', 'content_types' ) ) ||
		( is_single() && has_term( 'regulatory-tracker', 'content_types' ) )
	) {
		$handle = 'map-svg-new';

		wp_register_script(
			$handle,
			TAMARIND_MAPSVG_URL . '/js/map-svg-v8.js',
			array(),
			TM_MAPSVG_VERSION,
			true
		);
		wp_enqueue_script( 'map-svg-new' );
	}
}
