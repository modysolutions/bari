<?php
/**
 * Search history logging logic
 *
 * @package Tamarind_Search
 */

namespace tamarind_search;

defined( 'ABSPATH' ) || exit;

/**
 * Log changes when user saved searches are updated.
 *
 * @param array  $value   New field value.
 * @param string $post_id Post ID (format: 'user_123').
 * @param array  $field   ACF field config.
 * @return array Unmodified value.
 */
function log_search_changes( $value, $post_id, $field ) {
	if ( strpos( $post_id, 'user_' ) !== 0 ) {
		return $value;
	}

	$user_id = (int) str_replace( 'user_', '', $post_id );
	if ( ! $user_id ) {
		return $value;
	}

	$old_value = get_field( 'saved_searches', $post_id );

	$old_searches = normalize_searches( $old_value );
	$new_searches = normalize_searches( $value );

	$changes = detect_changes( $old_searches, $new_searches );

	save_changes_to_history( $user_id, $changes );

	return $value;
}
add_filter( 'acf/update_value/key=field_saved_searches', __NAMESPACE__ . '\log_search_changes', 10, 3 );

/**
 * Normalize search data to comparable format.
 *
 * @param array $searches Raw ACF repeater data.
 * @return array Normalized searches indexed by search_word.
 */
function normalize_searches( $searches ) {
	$normalized = array();

	foreach ( (array) $searches as $search ) {
		if ( ! empty( $search['search_word'] ) ) {
			$normalized[ $search['search_word'] ] = array(
				'search_word' => $search['search_word'],
				'search_url'  => $search['search_url'] ?? '',
			);
		}
	}

	return $normalized;
}

/**
 * Detect added and removed searches.
 *
 * @param array $old_searches Old normalized searches.
 * @param array $new_searches New normalized searches.
 * @return array Changes with 'added', 'removed', and search data.
 */
function detect_changes( $old_searches, $new_searches ) {
	$old_words = array_keys( $old_searches );
	$new_words = array_keys( $new_searches );

	return array(
		'added'        => array_diff( $new_words, $old_words ),
		'removed'      => array_diff( $old_words, $new_words ),
		'old_searches' => $old_searches,
		'new_searches' => $new_searches,
	);
}

/**
 * Save detected changes to database.
 *
 * @param int   $user_id User ID.
 * @param array $changes Changes array with 'added' and 'removed'.
 */
function save_changes_to_history( $user_id, $changes ) {
	foreach ( $changes['added'] as $search_word ) {
		$search_url = $changes['new_searches'][ $search_word ]['search_url'] ?? '';
		search_history\insert_log( $user_id, $search_word, $search_url, 'added' );
	}

	foreach ( $changes['removed'] as $search_word ) {
		$search_url = $changes['old_searches'][ $search_word ]['search_url'] ?? '';
		search_history\insert_log( $user_id, $search_word, $search_url, 'removed' );
	}
}
