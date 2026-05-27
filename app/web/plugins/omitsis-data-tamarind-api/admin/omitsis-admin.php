<?php

/**
 * Handle common Functions for admin
 *
 * @package Omitsis_Data_Tamarind_Api
 */

namespace omitsis\admin;

defined( 'ABSPATH' ) || exit;

const PLUGIN_NAME = \omitsis_data_api\PLUGIN_NAME;

require_once __DIR__ . '/omitsis-import.php';
use data\import as import;

require_once __DIR__ . '/omitsis-settings.php';
use omitsis\admin_settings as settings;

require_once \omitsis_data_api\PLUGIN_PATH . '/includes/omitsis-api-demo.php';
use omitsis\rest_api_demo as rest_api_demo;

require_once __DIR__ . '/omitsis-data-view.php';
use omitsis\data_view as data_view;

/**
 * The function `omitsis_data_api_imports` imports the `omitsis_data_api_imports` function from the
 * `import` namespace.
 */
function omitsis_data_api_imports () {
	import\omitsis_data_api_imports();
}

/**
 * The function `omitsis_data_api_endpoints` initializes the REST API demo for the Omitsis data.
 */
function omitsis_data_api_endpoints () {
	rest_api_demo\init_api_demo();
}

/**
 * The function omitsis_data_api_settings() is called to access the settings for the Omitsis Data API.
 */
function omitsis_data_api_settings () {
	settings\omitsis_data_api_settings();
}

function omitsis_data_view () {
	data_view\omitsis_data_api_data_views();
}

/**
 * The function "set_menu_settings" adds menu pages and sub-menu pages for a Data API in a WordPress plugin.
 */
function set_menu_settings () {
	add_menu_page(
		__( 'Import Data for API', 'tamarind' ),
		__( 'Tamarind API', 'tamarind' ),
		'edit_posts',
		'data-tamarind-api-import',
		__NAMESPACE__ . '\omitsis_data_api_imports',
		'dashicons-rest-api',
		85,
	);

	add_submenu_page(
		'data-tamarind-api-import',
		__( 'Data / EndPoints' ),
		__( 'Demo Endpoint' ),
		'edit_posts',
		'data-tamarind-api-endpoints',
		__NAMESPACE__ . '\omitsis_data_api_endpoints'
	);

	add_submenu_page(
		'data-tamarind-api-import',
		__( 'View data' ),
		__( 'View data ddbb' ),
		'edit_posts',
		'data-tamarind-api-view-data',
		__NAMESPACE__ . '\omitsis_data_view'
	);

	add_submenu_page(
		'data-tamarind-api-import',
		__( 'Data API / Settings' ),
		__( 'Settings' ),
		'edit_posts',
		'data-tamarind-api-settings',
		__NAMESPACE__ . '\omitsis_data_api_settings'
	);
}

/**
 * The function `omitsis_data_api_admin_enqueue_scripts` is used to enqueue a CSS file for the admin area of a WordPress plugin.
 */
function omitsis_data_api_admin_enqueue_scripts () {
	wp_enqueue_style( 'omitsis-data-api-admin-style', '/wp-content/plugins/' . \omitsis_data_api\PLUGIN_NAME . '/assets/css/dist/omitsis-data-tamarind-api-admin.css', array(), \omitsis_data_api\VERSION, 'all' );
}

/**
 * The function "init_admin" sets up the admin menu and initializes the settings update for a plugin named "omitsis_data_api".
 */
function init_admin () {
	add_action( 'admin_menu', __NAMESPACE__ . '\set_menu_settings', 9);
	add_action( 'admin_init', 'omitsis\admin_settings\omitsis_options_update' );
	add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\omitsis_data_api_admin_enqueue_scripts' );
}

