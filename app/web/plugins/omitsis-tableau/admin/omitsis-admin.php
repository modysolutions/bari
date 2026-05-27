<?php

/**
 * Handle common Functions for admin
 *
 * @package Omitsis_Data_Tamarind_Api
 */

namespace omitsis\tableau_admin;

defined( 'ABSPATH' ) || exit;

const PLUGIN_NAME = \omitsis_tableau\PLUGIN_NAME;
const PLUGIN_PATH = \omitsis_tableau\PLUGIN_PATH;

require_once __DIR__ . '/omitsis-settings.php';
use omitsis\tableau_admin_settings as settings;

require_once PLUGIN_PATH . '/includes/omitsis-api-demo.php';
use omitsis\tableau_api_demo as tableau_api_demo;

function omitsis_tableau_ini () {
	echo '<h1>Tableau</h1>';
}

/**
 * The function omitsis_data_api_settings() is called to access the settings for the Omitsis Data API.
 */
function omitsis_tableau_settings () {
	settings\omitsis_tableau_settings();
}

/**
 * The function `omitsis_data_api_endpoints` initializes the REST API demo for the Omitsis data.
 */
function omitsis_tableau_api () {
	tableau_api_demo\init_tableau_api();
}

/**
 * The function "set_menu_settings" adds menu pages and sub-menu pages for a Data API in a WordPress plugin.
 */
function set_menu_settings () {
	add_menu_page(
		__( 'Tableau', 'tamarind' ),
		__( 'Tableau', 'tamarind' ),
		'edit_posts',
		'tableau-admin',
		__NAMESPACE__ . '\omitsis_tableau_api',
		// __NAMESPACE__ . '\omitsis_tableau_ini',
		'dashicons-rest-api',
		85,
	);

	// add_submenu_page(
	// 	'tableau-admin',
	// 	__( 'Tableau / Settings' ),
	// 	__( 'Settings' ),
	// 	'edit_posts',
	// 	'omitsis-tableau-settings',
	// 	__NAMESPACE__ . '\omitsis_tableau_settings'
	// );

	// add_submenu_page(
	// 	'tableau-admin',
	// 	__( 'Data Tableau' ),
	// 	__( 'Demo' ),
	// 	'edit_posts',
	// 	'omitsis-tableau-api',
	// 	__NAMESPACE__ . '\omitsis_tableau_api'
	// );
}

/**
 * The function `omitsis_data_api_admin_enqueue_scripts` is used to enqueue a CSS file for the admin area of a WordPress plugin.
 */
function omitsis_enqueue_scripts () {
	wp_enqueue_style( 'omitsis-tableau-admin-style', '/wp-content/plugins/' . \omitsis_tableau\PLUGIN_NAME . '/assets/css/dist/omitsis-tableau-admin.css', array(), \omitsis_tableau\VERSION, 'all' );
}

/**
 * The function "init_admin" sets up the admin menu and initializes the settings update for a plugin named "omitsis_data_api".
 */
function init_admin () {
	add_action( 'admin_menu', __NAMESPACE__ . '\set_menu_settings', 9);
	add_action( 'admin_init', 'omitsis\tableau_admin_settings\omitsis_options_update' );
	add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\omitsis_enqueue_scripts' );
}

