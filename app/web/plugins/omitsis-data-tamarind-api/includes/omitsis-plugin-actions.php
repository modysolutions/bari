<?php

/**
 * Handle common plugin actions
 *
 * @package Omitsis_Data_Tamarind_Api
 */

namespace omitsis\plugin_actions;

defined( 'ABSPATH' ) || exit;

/**
 * The function checks if a specific database table exists in the WordPress database.
 *
 * @return a boolean value. If the table exists in the database, it will return true. Otherwise, it
 * will return false.
 */
function is_created_db() {
	global $wpdb;
	$prefix_plugin = 'omitsis_data_api_';
	$prefix        = $wpdb->prefix . $prefix_plugin;

	$table_name = $prefix . 'country';
	$sql = "SHOW TABLES LIKE '" . $table_name . "'";
	$result = $wpdb->get_results( $sql );
	if ( 0 < count( $result ) ) {
		return true;
	} else {
		return false;
	}
}

/**
  * Database is created on plugin activation
  */
function create_db() {

	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$prefix_plugin   = 'omitsis_data_api_';
	$prefix          = $wpdb->prefix . $prefix_plugin;
	$charset_collate = $wpdb->get_charset_collate();

	$table_name  = $prefix . 'country';
	$sql_country = 'CREATE TABLE ' . $table_name . ' (
		id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		name varchar(255) NOT NULL,
		code bigint(20) NULL,
		code_char varchar(5) NULL,
		currency bigint(20) NULL,
		region_id bigint(20) NULL,
		PRIMARY KEY (id)) ' . $charset_collate;

	$table_name = $prefix . 'region';
	$sql_region = 'CREATE TABLE ' . $table_name . ' (
		id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		name varchar(255) NOT NULL,
		note longtext NULL,
		PRIMARY KEY (id)) ' . $charset_collate;

	$table_name     = $prefix . 'meta_value';
	$sql_meta_value = 'CREATE TABLE ' . $table_name . ' (
		id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		country_id bigint(20) NOT NULL,
		year int NOT NULL,
		slug_feature_name varchar(255) NULL,
		value longtext NULL,
		PRIMARY KEY (id),
		KEY country_id (country_id),
		KEY year (year)) ' . $charset_collate;

	$table_name   = $prefix . 'features';
	$sql_features = 'CREATE TABLE ' . $table_name . ' (
		id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		slug_feature_name varchar(255) NOT NULL,
		feature_name longtext NULL,
		feature_order int NULL,
		feature_type varchar(255) NULL,
		PRIMARY KEY (id),
		KEY feature_type (feature_type)) ' . $charset_collate;

	$sql = array(
		$sql_country,
		$sql_region,
		$sql_meta_value,
		$sql_features,
	);

	foreach ( $sql as $query ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$query,
		);
	}
}

/**
  * Database is deleted on plugin deactivation
  */
function delete_db() {

	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$prefix_plugin = 'omitsis_data_api_';
	$prefix        = $wpdb->prefix . $prefix_plugin;

	$drop_sentences = array_map(
		function( $table_name ) use ( $prefix ) {
			return sprintf( 'DROP TABLE IF EXISTS %1$s%2$s', $prefix, $table_name );
		},
		array(
			'country',
			'region',
			'meta_value',
			'features',
		)
	);

	foreach ( $drop_sentences as $query ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$query,
		);
	}

}

/**
 * The code that runs during plugin activation.
 */
function activate_omitsis_data_tamarind_api() {
	/**
	 * Checking if a specific database table exists in the WordPress database.
	 * If the table exists, the function will return and no further action will be taken.
	 */
	if ( is_created_db() ) {
		return;
	}

	create_db();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_omitsis_data_tamarind_api() {
	$omitsis_data_api_settings_delete_data = get_option( 'omitsis_data_api_settings_delete_data' );
	if ( $omitsis_data_api_settings_delete_data ) {
		delete_db();
	}
}

/**
 * The code that runs during plugin uninstall.
 */
function uninstall_omitsis_data_tamarind_api() {
	$omitsis_data_api_settings_delete_data = get_option( 'omitsis_data_api_settings_delete_data' );
	if ( $omitsis_data_api_settings_delete_data ) {
		delete_db();
	}
}

/**
 * The function "init_actions" registers activation, deactivation, and uninstall hooks for the
 * "omitsis_data_tamarind_api" plugin.
 *
 * @param file The `` parameter is the path to the main plugin file. It is used to register
 * activation, deactivation, and uninstall hooks for the plugin.
 */
function init_actions( $file ) {
	register_activation_hook( $file, __NAMESPACE__ . '\activate_omitsis_data_tamarind_api');
	register_deactivation_hook( $file, __NAMESPACE__ . '\deactivate_omitsis_data_tamarind_api');
	register_uninstall_hook( $file, __NAMESPACE__ . '\uninstall_omitsis_data_tamarind_api');
}
