<?php
/**
 * Search history database operations
 *
 * @package Tamarind_Search
 */

namespace tamarind_search\search_history;

defined( 'ABSPATH' ) || exit;

/**
 * Create search history table.
 */
function create_table() {
	global $wpdb;

	$table_name      = $wpdb->prefix . 'user_search_history';
	$charset_collate = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS $table_name (
		id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		user_id bigint(20) UNSIGNED NOT NULL,
		search_word varchar(255) NOT NULL,
		search_url text NULL,
		action varchar(20) NOT NULL,
		action_timestamp datetime NOT NULL,
		PRIMARY KEY (id),
		KEY user_id (user_id),
		KEY action (action),
		KEY action_timestamp (action_timestamp)
	) $charset_collate";

	
	$wpdb->query( $sql );
}

/**
 * Insert a search history log entry.
 *
 * @param int    $user_id     User ID.
 * @param string $search_word Search term.
 * @param string $search_url  Search URL.
 * @param string $action      Action type ('added' or 'removed').
 */
function insert_log( $user_id, $search_word, $search_url, $action ) {
	global $wpdb;

	$table_name = $wpdb->prefix . 'user_search_history';
	$timestamp  = current_time( 'mysql' );

	$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$table_name,
		array(
			'user_id'          => $user_id,
			'search_word'      => $search_word,
			'search_url'       => $search_url,
			'action'           => $action,
			'action_timestamp' => $timestamp,
		),
		array( '%d', '%s', '%s', '%s', '%s' )
	);
}

/**
 * Get search history for a user.
 *
 * @param int $user_id User ID.
 * @param int $limit   Number of records to retrieve.
 * @return array Search history logs.
 */
function get_user_history( $user_id, $limit = 100 ) {
	global $wpdb;

	$table_name = $wpdb->prefix . 'user_search_history';

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	return $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM $table_name WHERE user_id = %d ORDER BY action_timestamp DESC LIMIT %d",
			$user_id,
			$limit
		)
	);
}
