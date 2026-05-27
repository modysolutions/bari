<?php
/**
 * Used to load the plugin's textdomain for internationalization.
 *
 * @package Omitsis_Data_Tamarind_Api
 */

 namespace omitsis\i18n;

defined( 'ABSPATH' ) || exit;

/**
 * Load plugin textdomain.
 */
function load_textdomain() {
	load_plugin_textdomain( 'omitsis-data-tamarind-api', false, basename( __DIR__ ) . '/i18n/languages/' );
}

/**
 * Init i18n.
 */
function init_i18n() {
	add_action( 'plugins_loaded', __NAMESPACE__ . '\load_textdomain' );
}
