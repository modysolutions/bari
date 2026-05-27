<?php
/**
 * Plugin Name: Tamarind - Base
 * Description: A plugin to manage Tamarind Base websites
 * Author:      Omitsis
 * Author URI:  https://www.omitsis.com
 * Text Domain: tamarind-base
 * Domain Path: /languages
 * version:     1.4.0
 *
 * @package Tamarind_Base
 */

namespace tamarind_base;

/**
 * Version number of the plugin to manage Tamarind Base websites
 */
$version = '1.4.0';
if(file_exists(dirname(__FILE__) . '/dist/css/tamarind-base.min.css')) {
	$version = filemtime(dirname(__FILE__) . '/dist/css/tamarind-base.min.css');
}
define('TM_BASE_VERSION', $version);
define('TM_LANGUAGE_DOMAIN', 'tamarind');
define('TM_PLUGIN_DIR_PATH', trailingslashit(__DIR__));
define('TM_PLUGIN_DIR_URL', trailingslashit(plugin_dir_url(__FILE__)));

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\tamarind_base_enqueue_scripts' );
add_action( 'admin_init', __NAMESPACE__ . '\tamarind_base_admin_init' );

/**
 * Enqueue the styles and scripts
 *
 * @return void
 */
function tamarind_base_enqueue_scripts(): void {
	$plugin_url = plugin_dir_url( __FILE__ );
	wp_enqueue_style( 'tm-base', $plugin_url . 'dist/css/tamarind-base.min.css', array(), TM_BASE_VERSION );

	// TODO: in tamarind-base.min is all the included code of the webcomponents, analyze if it should be like this or not.
	wp_enqueue_script( 'tm-base', $plugin_url . 'dist/js/tamarind-base.min.js', array(), TM_BASE_VERSION, true );

	wp_enqueue_style( 'tm-swiper', $plugin_url . 'dist/css/swiper-bundle.min.css', array(), TM_BASE_VERSION );
	wp_enqueue_script( 'tm-swiper', $plugin_url . 'dist/js/swiper-bundle.min.js', array(), TM_BASE_VERSION, false );
}

function tamarind_base_admin_init() : void {
	remove_action('admin_notices', 'update_nag', 3);
	remove_action('admin_notices', 'maintenance_nag', 10);
}

/**
 * Load all PHP files in a specified folder.
 *
 * @param string $folder_path The path to the folder containing PHP files.
 * @return void
 */
function load_folder_files( string $folder_path ) : void {
	if ( ! is_dir( $folder_path ) ) {
		return;
	}

	foreach ( glob( "{$folder_path}/*.php" ) as $file ) {
		require_once $file;
	}
}

/**
 * Load modules from the includes/modules directory.
 *
 * This function scans the modules directory and includes each module's functions.php file
 * and ACF fields if they exist.
 *
 * @param string $path The path to the plugin directory. Default is TM_PLUGIN_DIR_PATH.
 *
 * @return void
 */
function load_modules( string $path = TM_PLUGIN_DIR_PATH ) : void {
	$base_path = $path . '/includes/modules';
	foreach ( glob( "{$base_path}/*", GLOB_ONLYDIR ) as $module_path ) {
		$functions_file = "{$module_path}/functions.php";
		if ( file_exists( $functions_file ) ) {
			$cpt_dir = "{$module_path}/cpt";
			if ( is_dir( $cpt_dir ) ) {
				load_folder_files( $cpt_dir );
			}
			$acf_dir = "{$module_path}/acf";
			if ( is_dir( $acf_dir ) ) {
				load_folder_files( $acf_dir );
			}
			require_once $functions_file;
		}
	}
}

/* Load ACF options, fields and loader */
require_once TM_PLUGIN_DIR_PATH . '/includes/acf-options.php';

/* Load Additional files */
require_once TM_PLUGIN_DIR_PATH . '/includes/get-svg-icon.php';
require_once TM_PLUGIN_DIR_PATH . '/includes/template-parts.php';

/* Load Components */
require_once TM_PLUGIN_DIR_PATH . '/src/lib/tm-dropdown/tm-dropdown.php';
require_once TM_PLUGIN_DIR_PATH . '/src/lib/tm-accordion/tm-accordion.php';
require_once TM_PLUGIN_DIR_PATH . '/src/lib/tm-tabs/tm-tabs.php';

/* Load modules */
load_modules();
