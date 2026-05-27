<?php
/**
 * Historical logging for favourites actions.
 *
 * @package Tamarind_Favourites
 */

namespace tamarind_favourites;

defined( 'ABSPATH' ) || exit;

/**
 * Create the favourites history table on plugin activation.
 */
function create_favourites_history_table() {
	global $wpdb;

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$table_name      = $wpdb->prefix . 'user_favourites_history';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = 'CREATE TABLE ' . $table_name . ' (
		id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		user_id bigint(20) UNSIGNED NOT NULL,
		post_id bigint(20) UNSIGNED NOT NULL,
		action_type varchar(20) NOT NULL,
		post_type varchar(20) NOT NULL,
		created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		KEY user_id (user_id),
		KEY post_id (post_id),
		KEY action_type (action_type),
		KEY created_at (created_at),
		KEY user_post (user_id, post_id)) ' . $charset_collate;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.NotPrepared
	$wpdb->query( $sql );
}

/**
 * Log a favourite action to the history table.
 *
 * @param int    $user_id     The user ID.
 * @param int    $post_id     The post ID.
 * @param string $action_type The action type ('added' or 'removed').
 * @param string $post_type   The post type.
 */
function log_favourite_action( $user_id, $post_id, $action_type, $post_type ) {
	global $wpdb;

	$table_name = $wpdb->prefix . 'user_favourites_history';

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->insert(
		$table_name,
		array(
			'user_id'     => $user_id,
			'post_id'     => $post_id,
			'action_type' => $action_type,
			'post_type'   => $post_type,
			'created_at'  => current_time( 'mysql' ),
		),
		array( '%d', '%d', '%s', '%s', '%s' )
	);
}

/**
 * Hook into ACF field update to log favourite changes.
 *
 * @param mixed $value    The new value.
 * @param int   $post_id  The post ID (in this case, 'user_X').
 * @param array $field    The field array.
 * @param mixed $original The original value before update.
 * @return mixed The unmodified value.
 */
function log_acf_favourites_update( $value, $post_id, $field, $original ) {
	if ( ! is_string( $post_id ) || strpos( $post_id, 'user_' ) !== 0 ) {
		return $value;
	}

	$user_id = (int) str_replace( 'user_', '', $post_id );

	if ( ! $user_id ) {
		return $value;
	}

	$original_posts = is_array( $original ) ? $original : array();
	$new_posts      = is_array( $value ) ? $value : array();

	$added_posts   = array_diff( $new_posts, $original_posts );
	$removed_posts = array_diff( $original_posts, $new_posts );

	foreach ( $added_posts as $post_id_to_log ) {
		$post_type = get_post_type( $post_id_to_log );
		if ( $post_type ) {
			log_favourite_action( $user_id, $post_id_to_log, 'added', $post_type );
		}
	}

	foreach ( $removed_posts as $post_id_to_log ) {
		$post_type = get_post_type( $post_id_to_log );
		if ( $post_type ) {
			log_favourite_action( $user_id, $post_id_to_log, 'removed', $post_type );
		}
	}

	return $value;
}
add_filter( 'acf/update_value/key=field_user_favourite_posts', __NAMESPACE__ . '\log_acf_favourites_update', 10, 4 );
