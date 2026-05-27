<?php

namespace tamarind_reports;

defined( 'ABSPATH' ) || exit;

/**
 * Get user IDs for a specific client based on user meta.
 * This is the single source of truth for client filtering.
 * Results are cached for 1 hour to improve performance.
 * 
 * @param int $client_term_id The client term ID
 * @return array Array of user IDs, empty if no users found
 */
function get_client_user_ids( int $client_term_id ): array {
	if ( $client_term_id <= 0 ) {
		return array();
	}
	
	// Try to get from cache first
	$cache_key = 'tamarind_client_users_' . $client_term_id;
	$cached    = get_transient( $cache_key );
	if ( false !== $cached && is_array( $cached ) ) {
		return $cached;
	}
	
	$user_query = new \WP_User_Query( array(
		'fields'     => 'ID',
		'role'       => 'client',
		'meta_query' => array(
			array(
				'key'     => 'clientes',
				'value'   => $client_term_id,
				'compare' => '=',
			),
		),
	) );
	
	$user_ids = array_map( 'intval', (array) $user_query->get_results() );
	
	// Cache for 1 hour
	set_transient( $cache_key, $user_ids, HOUR_IN_SECONDS );
	
	return $user_ids;
}

/**
 * Build SQL WHERE clause for filtering by user IDs.
 * Uses wpdb->prepare for security.
 * 
 * @param array $user_ids Array of user IDs (already sanitized as integers)
 * @return string SQL WHERE clause fragment (empty string if no IDs)
 */
function build_user_ids_where_clause( array $user_ids, string $user_id_ref = 'user_id' ): string {
	if ( empty( $user_ids ) ) {
		return '';
	}
	
	global $wpdb;
	$placeholders = implode( ',', array_fill( 0, count( $user_ids ), '%d' ) );
	
	return $wpdb->prepare( " AND {$user_id_ref} IN ({$placeholders})", ...$user_ids );
}

/**
 * Get pageviews/downloads table names used by usage report queries.
 *
 * @return array
 */
function get_usage_activity_tables(): array {
	global $wpdb;

	return array(
		'dl_table' => $wpdb->prefix . 'subscriber_report_downloaded_docs',
		'pv_table' => $wpdb->prefix . 'subscriber_report_pageviews',
	);
}

/**
 * Build shared user filtering SQL fragments for usage report activity queries.
 *
 * @param int|null $client_term_id_filter
 * @param int|null $user_filter_id
 * @return array
 */
function get_user_ids_by_subscription_plan( int $subscription_plan_id ): array {
	if ( $subscription_plan_id <= 0 ) {
		return array();
	}

	$cache_key = 'tamarind_plan_users_' . $subscription_plan_id;
	$cached    = get_transient( $cache_key );
	if ( false !== $cached && is_array( $cached ) ) {
		return array_map( 'intval', $cached );
	}

	$custom_users_query = new \WP_User_Query( array(
		'role'       => 'client',
		'fields'     => 'ID',
		'meta_query' => array(
			array(
				'key'     => 'related_subscription_plan',
				'value'   => $subscription_plan_id,
				'compare' => '=',
			),
		),
	) );
	$custom_users      = array_map( 'intval', (array) $custom_users_query->get_results() );

	$clients_query = new \WP_Query( array(
		'post_type'      => 'client',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_query'     => array(
			array(
				'key'     => 'subscription_plan',
				'value'   => $subscription_plan_id,
				'compare' => '=',
			),
		),
	) );
	$client_ids    = array_map( 'intval', (array) $clients_query->posts );

	$client_users = array();
	if ( ! empty( $client_ids ) ) {
		$client_users_query = new \WP_User_Query( array(
			'role'       => 'client',
			'fields'     => 'ID',
			'meta_query' => array(
				array(
					'key'     => 'related_client',
					'value'   => $client_ids,
					'compare' => 'IN',
				),
			),
		) );
		$client_users      = array_map( 'intval', (array) $client_users_query->get_results() );
	}

	$legacy_client_term_ids = array();
	if ( ! empty( $client_ids ) ) {
		update_meta_cache( 'post', $client_ids );
		foreach ( $client_ids as $client_id ) {
			$old_term_id = (int) get_field( 'old_term_id', $client_id );
			if ( $old_term_id > 0 ) {
				$legacy_client_term_ids[] = $old_term_id;
			}
		}
		$legacy_client_term_ids = array_values( array_unique( $legacy_client_term_ids ) );
	}

	$legacy_client_users = array();
	if ( ! empty( $legacy_client_term_ids ) ) {
		$legacy_client_users_query = new \WP_User_Query( array(
			'role'       => 'client',
			'fields'     => 'ID',
			'meta_query' => array(
				array(
					'key'     => 'clientes',
					'value'   => $legacy_client_term_ids,
					'compare' => 'IN',
				),
			),
		) );
		$legacy_client_users      = array_map( 'intval', (array) $legacy_client_users_query->get_results() );
	}

	$user_ids = array_values( array_filter( array_unique( array_merge( $custom_users, $client_users, $legacy_client_users ) ) ) );
	set_transient( $cache_key, $user_ids, HOUR_IN_SECONDS );

	return $user_ids;
}

function build_detailed_post_tax_filters_where_clause( int $content_type_id = 0, int $subcontent_type_id = 0, string $outer_post_id_ref = 'post_id' ): string {
	global $wpdb;

	$where = '';
	if ( $content_type_id > 0 ) {
		$where .= $wpdb->prepare(
			" AND EXISTS (
				SELECT 1
				FROM {$wpdb->term_relationships} tr
				INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
				WHERE tr.object_id = {$outer_post_id_ref}
				  AND tt.taxonomy = 'content_types'
				  AND tt.term_id = %d
			)",
			$content_type_id
		);
	}

	if ( $subcontent_type_id > 0 ) {
		$where .= $wpdb->prepare(
			" AND EXISTS (
				SELECT 1
				FROM {$wpdb->term_relationships} tr
				INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
				WHERE tr.object_id = {$outer_post_id_ref}
				  AND tt.taxonomy = 'content_types'
				  AND tt.term_id = %d
			)",
			$subcontent_type_id
		);
	}

	return $where;
}

function build_detailed_favourites_where_clause( string $has_favourite = '', string $outer_user_id_ref = 'user_id', string $outer_post_id_ref = 'post_id' ): string {
	global $wpdb;

	if ( 'yes' !== $has_favourite && 'no' !== $has_favourite ) {
		return '';
	}

	if ( has_favourites_history_table() ) {
		$history_table = $wpdb->prefix . 'user_favourites_history';
		$latest_added_clause = "EXISTS (
			SELECT 1
			FROM {$history_table} fh
			WHERE fh.user_id = {$outer_user_id_ref}
			  AND fh.post_id = {$outer_post_id_ref}
			  AND fh.id = (
				SELECT MAX(fh2.id)
				FROM {$history_table} fh2
				WHERE fh2.user_id = {$outer_user_id_ref}
				  AND fh2.post_id = {$outer_post_id_ref}
			  )
			  AND fh.action_type = 'added'
		)";

		return 'yes' === $has_favourite ? " AND {$latest_added_clause}" : " AND NOT {$latest_added_clause}";
	}

	// Fallback when history table does not exist: derive state from current user favourites snapshot.
	$snapshot_clause = "EXISTS (
		SELECT 1
		FROM {$wpdb->usermeta} um
		WHERE um.user_id = {$outer_user_id_ref}
		  AND um.meta_key = 'user_favourite_posts'
			  AND (
				um.meta_value LIKE CONCAT('%:', CHAR(34), {$outer_post_id_ref}, CHAR(34), ';%')
				OR um.meta_value LIKE CONCAT('%', CHAR(34), {$outer_post_id_ref}, CHAR(34), '%')
				OR um.meta_value LIKE CONCAT('%i:', {$outer_post_id_ref}, ';%')
			  )
		)";

	return 'yes' === $has_favourite ? " AND {$snapshot_clause}" : " AND NOT {$snapshot_clause}";
}

function has_favourites_history_table(): bool {
	global $wpdb;

	static $has_table = null;
	if ( null !== $has_table ) {
		return $has_table;
	}

	$table_name = $wpdb->prefix . 'user_favourites_history';
	$exists     = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table_name ) );
	$has_table  = ( $exists === $table_name );

	return $has_table;
}

function get_usage_activity_user_filters( ?int $client_term_id_filter = null, ?int $user_filter_id = null, ?int $subscription_plan_id = null, string $user_id_ref = 'user_id' ): array {
	global $wpdb;

	$filtered_user_ids = null;

	if ( $client_term_id_filter ) {
		$client_user_ids = get_client_user_ids( $client_term_id_filter );
		if ( empty( $client_user_ids ) ) {
			return array(
				'has_results'    => false,
				'where_user_ids' => '',
				'where_user'     => '',
			);
		}
		$filtered_user_ids = $client_user_ids;
	}

	if ( $subscription_plan_id ) {
		$plan_user_ids = get_user_ids_by_subscription_plan( (int) $subscription_plan_id );
		if ( empty( $plan_user_ids ) ) {
			return array(
				'has_results'    => false,
				'where_user_ids' => '',
				'where_user'     => '',
			);
		}
		if ( is_array( $filtered_user_ids ) ) {
			$filtered_user_ids = array_values( array_intersect( $filtered_user_ids, $plan_user_ids ) );
		} else {
			$filtered_user_ids = $plan_user_ids;
		}
		if ( empty( $filtered_user_ids ) ) {
			return array(
				'has_results'    => false,
				'where_user_ids' => '',
				'where_user'     => '',
			);
		}
	}

	$where_user_ids = is_array( $filtered_user_ids ) ? build_user_ids_where_clause( $filtered_user_ids, $user_id_ref ) : '';
	$where_user = ( $user_filter_id ) ? $wpdb->prepare( " AND {$user_id_ref} = %d", $user_filter_id ) : '';

	return array(
		'has_results'    => true,
		'where_user_ids' => $where_user_ids,
		'where_user'     => $where_user,
	);
}

// @todo cache queries.
/**
 * Build classification sets for content_types (regulatory vs market).
 * @return array
 */
function content_types_sets(): array {
	// @todo use database to mapping
	$reg_slugs    = array(
		'alerts',
		'live-alerts-eu',
		'live-alerts-international',
		'live-alerts-us',
		'regulatory-alerts-podcasts',
		'policy-radar',
		'regulatory-reports',
		'country-regulatory-reports',
		'topic-regulatory-reports',
		'regulatory-briefing',
		'regulatory-trackers',
	);
	$reg_names    = array(
		'alerts',
		'live alerts eu',
		'live alerts international',
		'live alerts us',
		'news analysis',
		'regulatory alerts podcasts',
		'regulatory databases',
		'policy radar',
		'regulatory reports',
		'country regulatory reports',
		'policy radar analysis reports',
		'topic regulatory reports',
	);
	$market_slugs = array(
		'market-reports',
		'country-market-reports',
		'market-snapshots',
		'topic-market-reports',
		'pricing',
		'detailed-pricing-tracker',
		'brands-tracker',
		'product-tracker',
		'flavour-nicotine-tracker',
		'hardware-tracker',
		'nicotine-trackers',
	);
	$market_names = array(
		'market reports',
		'country market reports',
		'market snapshots',
		'topic market reports',
		'pricing',
		'pricing snapshots',
		'pricing tracker',
		'product feature trackers',
		'brand trackers',
		'disposable e-cigarette tracker',
		'market profile database',
		'select market reports',
		'flavour tracker',
		'hardware tracker',
	);

	return array(
		'reg_slugs'    => array_fill_keys( array_map( 'strtolower', $reg_slugs ), true ),
		'reg_names'    => array_fill_keys( array_map( 'strtolower', $reg_names ), true ),
		'market_slugs' => array_fill_keys( array_map( 'strtolower', $market_slugs ), true ),
		'market_names' => array_fill_keys( array_map( 'strtolower', $market_names ), true ),
	);
}

/**
 * Return interacted post-IDs (views âˆª downloads) and an IN list string.
 * @return array
 */
function interacted_posts( int $user_id, string $from, string $to ): array {
	global $wpdb;
	$pv_table = $wpdb->prefix . 'subscriber_report_pageviews';
	$dl_table = $wpdb->prefix . 'subscriber_report_downloaded_docs';
	$sql      = $wpdb->prepare(
		"(SELECT DISTINCT post_id FROM {$pv_table} WHERE user_id = %d AND post_id > 0 AND page_date BETWEEN %s AND %s)
         UNION
         (SELECT DISTINCT post_id FROM {$dl_table} WHERE user_id = %d AND post_id > 0 AND page_date BETWEEN %s AND %s)",
		$user_id, $from, $to, $user_id, $from, $to
	);
	$post_ids = (array) $wpdb->get_col( $sql );
	$in       = empty( $post_ids ) ? '' : implode( ', ', array_map( 'intval', $post_ids ) );

	return array( $post_ids, $in );
}

/**
 * Compute topics/geographies metrics for given posts.
 * @return array
 */
function topics_geos( string $post_ids_in ): array {
	global $wpdb;
	if ( $post_ids_in === '' ) {
		return array( 0, '', '', '' );
	}
	$rows   = (array) $wpdb->get_results(
		"SELECT tt.taxonomy AS taxonomy, t.name AS name, COUNT(*) AS c
         FROM {$wpdb->terms} t
         JOIN {$wpdb->term_taxonomy} tt ON tt.term_id = t.term_id AND tt.taxonomy IN ('topics','geography')
         JOIN {$wpdb->term_relationships} tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
         WHERE tr.object_id IN ( {$post_ids_in} )
         GROUP BY tt.taxonomy, t.term_id
         ORDER BY c DESC, t.name ASC"
	);
	$topics = array();
	$geos   = array();
	foreach ( $rows as $r ) {
		if ( $r->taxonomy === 'topics' ) {
			$topics[] = $r->name;
		} elseif ( $r->taxonomy === 'geography' ) {
			$geos[] = $r->name;
		}
	}
	$topics_distinct   = array_values( array_unique( $topics ) );
	$topics_count      = count( $topics_distinct );
	$top_topics_joined = implode( ', ', array_slice( $topics, 0, 5 ) );
	$geos_distinct     = array_values( array_unique( $geos ) );
	sort( $geos_distinct, SORT_NATURAL | SORT_FLAG_CASE );
	$geos_joined     = implode( ', ', $geos_distinct );
	$top_geos_joined = implode( ', ', array_slice( $geos, 0, 10 ) );

	return array( $topics_count, $top_topics_joined, $geos_joined, $top_geos_joined );
}

/**
 * Compute regulatory vs. market percentages based on content_types across posts.
 * @return array
 */
function regulatory_vs_market_percent( string $post_ids_in, array $sets ): array {
	global $wpdb;
	if ( $post_ids_in === '' ) {
		return array( 0, 0 );
	}
	$rows         = (array) $wpdb->get_results(
		"SELECT tr.object_id AS post_id, t.slug AS slug, LOWER(t.name) AS name
         FROM {$wpdb->term_relationships} tr
         JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id = tr.term_taxonomy_id AND tt.taxonomy = 'content_types'
         JOIN {$wpdb->terms} t ON t.term_id = tt.term_id
         WHERE tr.object_id IN ( {$post_ids_in} )"
	);
	$reg_posts    = array();
	$market_posts = array();
	foreach ( $rows as $r ) {
		$slug = $r->slug ? strtolower( $r->slug ) : '';
		$name = $r->name ? strtolower( $r->name ) : '';
		if ( isset( $sets['reg_slugs'][ $slug ] ) || isset( $sets['reg_names'][ $name ] ) ) {
			$reg_posts[ (int) $r->post_id ] = true;
		}
		if ( isset( $sets['market_slugs'][ $slug ] ) || isset( $sets['market_names'][ $name ] ) ) {
			$market_posts[ (int) $r->post_id ] = true;
		}
	}
	$reg    = count( $reg_posts );
	$market = count( $market_posts );
	$total  = $reg + $market;
	if ( $total === 0 ) {
		return array( 0, 0 );
	}
	$reg_pct = (int) round( ( $reg / $total ) * 100 );

	return array( $reg_pct, 100 - $reg_pct );
}

/**
 * Fetch detailed rows (downloads + pageviews) for the given filters.
 * Returns a merged, date-desc sorted list limited by $limit_rows.
 * Each row is an object with: user_id, client_id, page_date, post_id,
 * and optionally download_url, download_type for download rows.
 *
 * @deprecated Use detailed_rows_paginated() instead.
 *
 * @param string $from Date-time start (Y-m-d H:i:s)
 * @param string $to Date-time end   (Y-m-d H:i:s)
 * @param int|null $client_term_id_filter Optional client term ID filter
 * @param int|null $user_filter_id Optional user ID filter
 * @param int $limit_rows Max number of rows to return
 *
 * @return array<int, object>
 */
function detailed_rows( string $from, string $to, ?int $client_term_id_filter = null, ?int $user_filter_id = null, int $limit_rows = 250 ): array {
	global $wpdb;

	$dl_table = $wpdb->prefix . 'subscriber_report_downloaded_docs';
	$pv_table = $wpdb->prefix . 'subscriber_report_pageviews';

	$where_client = ( $client_term_id_filter ) ? $wpdb->prepare( ' AND client_id = %d', $client_term_id_filter ) : '';
	$where_user   = ( $user_filter_id ) ? $wpdb->prepare( ' AND user_id = %d', $user_filter_id ) : '';

	// Downloads
	$downloads = $wpdb->get_results( $wpdb->prepare(
		"SELECT user_id, client_id, page_date, post_id, download_url, download_type
         FROM {$dl_table}
         WHERE page_date BETWEEN %s AND %s {$where_client} {$where_user}
         ORDER BY page_date DESC
         LIMIT %d",
		$from, $to, $limit_rows
	) );

	// Pageviews
	$pageviews = $wpdb->get_results( $wpdb->prepare(
		"SELECT user_id, client_id, page_date, post_id
         FROM {$pv_table}
         WHERE page_date BETWEEN %s AND %s {$where_client} {$where_user}
         ORDER BY page_date DESC
         LIMIT %d",
		$from, $to, $limit_rows
	) );

	$merge = array_merge( $downloads ?: array(), $pageviews ?: array() );
	// Sort by date desc
	usort( $merge, function ( $a, $b ) {
		return strcmp( $b->page_date, $a->page_date );
	} );

	return array_slice( $merge, 0, max( 0, (int) $limit_rows ) );
}

/**
 * Get a total count of detailed rows (downloads + pageviews) for filters.
 */
function detailed_total( string $from, string $to, ?int $client_term_id_filter = null, ?int $user_filter_id = null, bool $include_empty = false, array $detail_filters = array() ): int {
	global $wpdb;
	$tables = get_usage_activity_tables();
	$dl_table = $tables['dl_table'];
	$pv_table = $tables['pv_table'];
	$subscription_plan_id = (int) ( $detail_filters['subscription_plan_id'] ?? 0 );
	$content_type_id      = (int) ( $detail_filters['content_type_id'] ?? 0 );
	$subcontent_type_id   = (int) ( $detail_filters['subcontent_type_id'] ?? 0 );
	$has_download         = (string) ( $detail_filters['has_download'] ?? '' );
	$has_favourite        = (string) ( $detail_filters['has_favourite'] ?? '' );
	$post_tax_where       = build_detailed_post_tax_filters_where_clause( $content_type_id, $subcontent_type_id, 'activity.post_id' );
	$favourites_where     = build_detailed_favourites_where_clause( $has_favourite, 'activity.user_id', 'activity.post_id' );

	$user_filters = get_usage_activity_user_filters( $client_term_id_filter, $user_filter_id, $subscription_plan_id, 'activity.user_id' );
	if ( ! $user_filters['has_results'] ) {
		return 0;
	}
	$where_user_ids = $user_filters['where_user_ids'];
	$where_user     = $user_filters['where_user'];

	$total_dl = 0;
	$total_pv = 0;
	if ( 'no' !== $has_download ) {
		$total_dl = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$dl_table} activity WHERE page_date BETWEEN %s AND %s AND post_id > 0 {$where_user_ids} {$where_user} {$post_tax_where} {$favourites_where}",
			$from, $to
		) );
	}
	if ( 'yes' !== $has_download ) {
		$total_pv = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$pv_table} activity WHERE page_date BETWEEN %s AND %s AND post_id > 0 {$where_user_ids} {$where_user} {$post_tax_where} {$favourites_where}",
			$from, $to
		) );
	}

	return $total_dl + $total_pv;
}

/**
 * Fetch a single page of detailed rows ordered by date desc using UNION ALL.
 * Returns [ 'rows' => array<object>, 'total' => int ].
 */
function detailed_rows_paginated( string $from, string $to, ?int $client_term_id_filter = null, ?int $user_filter_id = null, bool $include_empty = false, int $per_page = 100, int $page = 1, array $detail_filters = array() ): array {
	global $wpdb;
	$per_page = max( 1, (int) $per_page );
	$page     = max( 1, (int) $page );
	$offset   = ( $page - 1 ) * $per_page;

	$tables = get_usage_activity_tables();
	$dl_table = $tables['dl_table'];
	$pv_table = $tables['pv_table'];
	$subscription_plan_id = (int) ( $detail_filters['subscription_plan_id'] ?? 0 );
	$content_type_id      = (int) ( $detail_filters['content_type_id'] ?? 0 );
	$subcontent_type_id   = (int) ( $detail_filters['subcontent_type_id'] ?? 0 );
	$has_download         = (string) ( $detail_filters['has_download'] ?? '' );
	$has_favourite        = (string) ( $detail_filters['has_favourite'] ?? '' );
	$post_tax_where       = build_detailed_post_tax_filters_where_clause( $content_type_id, $subcontent_type_id, 'activity.post_id' );
	$favourites_where     = build_detailed_favourites_where_clause( $has_favourite, 'activity.user_id', 'activity.post_id' );

	$user_filters = get_usage_activity_user_filters( $client_term_id_filter, $user_filter_id, $subscription_plan_id, 'activity.user_id' );
	if ( ! $user_filters['has_results'] ) {
		return array( 'rows' => array(), 'total' => 0 );
	}
	$where_user_ids = $user_filters['where_user_ids'];
	$where_user     = $user_filters['where_user'];

	if ( 'yes' === $has_download ) {
		$sql = $wpdb->prepare(
			"SELECT user_id, client_id, page_date, post_id, download_url, download_type, 'download' AS event_type
	         FROM {$dl_table} activity
	         WHERE page_date BETWEEN %s AND %s AND post_id > 0 {$where_user_ids} {$where_user} {$post_tax_where} {$favourites_where}
	         ORDER BY page_date DESC
	         LIMIT %d OFFSET %d",
			$from, $to, $per_page, $offset
		);
	} elseif ( 'no' === $has_download ) {
		$sql = $wpdb->prepare(
			"SELECT user_id, client_id, page_date, post_id, NULL AS download_url, NULL AS download_type, 'page_view' AS event_type
	         FROM {$pv_table} activity
	         WHERE page_date BETWEEN %s AND %s AND post_id > 0 {$where_user_ids} {$where_user} {$post_tax_where} {$favourites_where}
	         ORDER BY page_date DESC
	         LIMIT %d OFFSET %d",
			$from, $to, $per_page, $offset
		);
	} else {
		$sql = $wpdb->prepare(
			"(SELECT user_id, client_id, page_date, post_id, download_url, download_type, 'download' AS event_type
	           FROM {$dl_table} activity
	           WHERE page_date BETWEEN %s AND %s AND post_id > 0 {$where_user_ids} {$where_user} {$post_tax_where} {$favourites_where})
	         UNION ALL
	         (SELECT user_id, client_id, page_date, post_id, NULL AS download_url, NULL AS download_type, 'page_view' AS event_type
	           FROM {$pv_table} activity
	           WHERE page_date BETWEEN %s AND %s AND post_id > 0 {$where_user_ids} {$where_user} {$post_tax_where} {$favourites_where})
	         ORDER BY page_date DESC
	         LIMIT %d OFFSET %d",
			$from, $to, $from, $to, $per_page, $offset
		);
	}

	$rows  = (array) $wpdb->get_results( $sql );
	$total = detailed_total( $from, $to, $client_term_id_filter, $user_filter_id, $include_empty, $detail_filters );

	return array( 'rows' => $rows, 'total' => $total );
}

/**
 * Build enriched detailed items ready for rendering, including
 * user email, company name, post-title, author, publication date,
 * content_types, subcontent_types, geographies and topics.
 *
 * @param string $from
 * @param string $to
 * @param int|null $client_term_id_filter
 * @param int|null $user_filter_id
 * @param int $limit_rows
 *
 * @return array
 */
/**
 * @deprecated Use detailed_data_paginated() instead.
 */
function detailed_data( string $from, string $to, ?int $client_term_id_filter = null, ?int $user_filter_id = null, int $limit_rows = 250 ): array {
	$rows = detailed_rows( $from, $to, $client_term_id_filter, $user_filter_id, $limit_rows );

	$details = array();
	foreach ( $rows as $row ) {
		$user  = get_user_by( 'id', (int) $row->user_id );
		$email = $user ? strtolower( (string) $user->user_email ) : '';

		$company = '';
		if ( ! empty( $row->client_id ) ) {
			$t = get_term( (int) $row->client_id, 'clientes' );
			if ( $t && ! is_wp_error( $t ) ) {
				$company = $t->name;
			}
		}

		$post_id          = (int) $row->post_id;
		$post_title       = $post_id ? get_the_title( $post_id ) : '';
		$author           = '';
		$pub_date         = '';
		$content_types    = array();
		$subcontent_types = array();
		$geos             = array();
		$topics           = array();

		if ( $post_id ) {
			$p = get_post( $post_id );
			if ( $p ) {
				$author_obj = get_user_by( 'id', (int) $p->post_author );
				$author     = $author_obj ? $author_obj->display_name : '';
				$pub_date   = $p->post_date;
			}
			$cts = get_the_terms( $post_id, 'content_types' );
			if ( $cts && ! is_wp_error( $cts ) ) {
				foreach ( $cts as $ct ) {
					if ( (int) $ct->parent === 0 ) {
						$content_types[] = $ct->name;
					} else {
						$subcontent_types[] = $ct->name;
					}
				}
			}
			$gts = get_the_terms( $post_id, 'geography' );
			if ( $gts && ! is_wp_error( $gts ) ) {
				foreach ( $gts as $gt ) {
					$geos[] = $gt->name;
				}
			}
			$tps = get_the_terms( $post_id, 'topics' );
			if ( $tps && ! is_wp_error( $tps ) ) {
				foreach ( $tps as $tp ) {
					$topics[] = $tp->name;
				}
			}
		}

		$details[] = array(
			'date'             => $row->page_date,
			'email'            => $email,
			'company'          => $company,
			'title'            => $post_title,
			'post_id'          => $post_id,
			'download_url'     => $row->download_url ?? '',
			'download_type'    => $row->download_type ?? '',
			'content_types'    => implode( ', ', array_unique( $content_types ) ),
			'subcontent_types' => implode( ', ', array_unique( $subcontent_types ) ),
			'geographies'      => implode( ', ', array_unique( $geos ) ),
			'topics'           => implode( ', ', array_unique( $topics ) ),
			'author'           => $author,
			'publication_date' => $pub_date,
		);
	}

	return $details;
}

function format_usage_report_last_login( int $user_id ): string {
	$last_login = get_user_meta( $user_id, 'wp-last-login', true );

	if ( is_numeric( $last_login ) ) {
		return date( 'Y-m-d H:i:s', (int) $last_login );
	}

	if ( is_string( $last_login ) ) {
		$last_login = trim( $last_login );
		if ( '' === $last_login ) {
			return '';
		}

		$parsed_timestamp = strtotime( $last_login );
		if ( false !== $parsed_timestamp ) {
			return date( 'Y-m-d H:i:s', $parsed_timestamp );
		}
	}

	return '';
}

/**
 * Paginated version returning [ $details, $total ].
 *
 * @param string $from Start date
 * @param string $to End date
 * @param int|string|null $client_term_id_filter Client term ID
 * @param int|string|null $user_filter_id User ID filter
 * @param bool $include_empty Whether to include users with no activity
 * @param int $per_page Items per page
 * @param int|string $page Page number
 * @return array
 */
function detailed_data_paginated( string $from, string $to, int|string|null $client_term_id_filter = null, int|string|null $user_filter_id = null, bool $include_empty = false, int $per_page = 100, int|string $page = 1, array $detail_filters = array() ): array {
	$page     = max( 1, (int) $page );
	$per_page = max( 1, (int) $per_page );
	$res      = detailed_rows_paginated( $from, $to, $client_term_id_filter, $user_filter_id, $include_empty, $per_page, $page, $detail_filters );

	// Prefetch: build a unique post_ids set
	$post_ids = array();
	foreach ( $res['rows'] as $r ) {
		$pid = isset( $r->post_id ) ? (int) $r->post_id : 0;
		if ( $pid > 0 ) {
			$post_ids[ $pid ] = true;
		}
	}
	$post_ids = array_keys( $post_ids );

	// Preload posts into a cache and build an author/date map
	$post_info = array();
	if ( ! empty( $post_ids ) ) {
		$posts = get_posts( array(
			'post__in'         => $post_ids,
			'posts_per_page'   => - 1,
			'orderby'          => 'post__in',
			'post_type'        => 'any',
			'suppress_filters' => true,
			'fields'           => 'all',
		) );
		foreach ( $posts as $p ) {
			$author_name = '';
			if ( isset( $p->post_author ) ) {
				$ao          = get_user_by( 'id', (int) $p->post_author );
				$author_name = $ao ? $ao->display_name : '';
			}
			$post_info[ (int) $p->ID ] = array(
				'author' => $author_name,
				'date'   => $p->post_date,
				'title'  => get_the_title( $p->ID ),
			);
		}
	}

	// Preload terms per taxonomy across all posts
	$post_terms = array(); // [post_id] => ['ct_parent'=>[], 'ct_child'=>[], 'geos'=>[], 'topics'=>[]]
	if ( ! empty( $post_ids ) ) {
		$cts_terms = wp_get_object_terms( $post_ids, 'content_types', array( 'fields' => 'all_with_object_id' ) );
		if ( ! is_wp_error( $cts_terms ) ) {
			foreach ( $cts_terms as $t ) {
				$pid = (int) $t->object_id;
				if ( ! isset( $post_terms[ $pid ] ) ) {
					$post_terms[ $pid ] = array(
						'ct_parent' => array(),
						'ct_child'  => array(),
						'geos'      => array(),
						'topics'    => array()
					);
				}
				if ( (int) $t->parent === 0 ) {
					$post_terms[ $pid ]['ct_parent'][] = $t->name;
				} else {
					$post_terms[ $pid ]['ct_child'][] = $t->name;
				}
			}
		}
		$g_terms = wp_get_object_terms( $post_ids, 'geography', array( 'fields' => 'all_with_object_id' ) );
		if ( ! is_wp_error( $g_terms ) ) {
			foreach ( $g_terms as $t ) {
				$pid = (int) $t->object_id;
				if ( ! isset( $post_terms[ $pid ] ) ) {
					$post_terms[ $pid ] = array(
						'ct_parent' => array(),
						'ct_child'  => array(),
						'geos'      => array(),
						'topics'    => array()
					);
				}
				$post_terms[ $pid ]['geos'][] = $t->name;
			}
		}
		$t_terms = wp_get_object_terms( $post_ids, 'topics', array( 'fields' => 'all_with_object_id' ) );
		if ( ! is_wp_error( $t_terms ) ) {
			foreach ( $t_terms as $t ) {
				$pid = (int) $t->object_id;
				if ( ! isset( $post_terms[ $pid ] ) ) {
					$post_terms[ $pid ] = array(
						'ct_parent' => array(),
						'ct_child'  => array(),
						'geos'      => array(),
						'topics'    => array()
					);
				}
				$post_terms[ $pid ]['topics'][] = $t->name;
			}
		}
	}

	// Caches for users/clients
	$user_email_cache  = array();
	$client_name_cache = array();
	$user_snapshot_cache   = array();
	$client_snapshot_cache = array();
	$plan_name_cache       = array();
	$favourite_actions_map = array();

	if ( has_favourites_history_table() && ! empty( $res['rows'] ) ) {
		global $wpdb;
		$history_table = $wpdb->prefix . 'user_favourites_history';
		$user_ids      = array();
		$post_ids_for_favourites = array();
		foreach ( $res['rows'] as $frow ) {
			$fuid = (int) ( $frow->user_id ?? 0 );
			$fpid = (int) ( $frow->post_id ?? 0 );
			if ( $fuid > 0 ) {
				$user_ids[ $fuid ] = $fuid;
			}
			if ( $fpid > 0 ) {
				$post_ids_for_favourites[ $fpid ] = $fpid;
			}
		}

		if ( ! empty( $user_ids ) && ! empty( $post_ids_for_favourites ) ) {
			$user_placeholders = implode( ',', array_fill( 0, count( $user_ids ), '%d' ) );
			$post_placeholders = implode( ',', array_fill( 0, count( $post_ids_for_favourites ), '%d' ) );
			$sql               = "SELECT user_id, post_id, action_type, id
				FROM {$history_table}
				WHERE user_id IN ({$user_placeholders})
				  AND post_id IN ({$post_placeholders})
				ORDER BY id DESC";
			$params            = array_merge( array_values( $user_ids ), array_values( $post_ids_for_favourites ) );
			$history_rows      = (array) $wpdb->get_results( $wpdb->prepare( $sql, ...$params ) );

			foreach ( $history_rows as $hrow ) {
				$key = (int) $hrow->user_id . ':' . (int) $hrow->post_id;
				if ( isset( $favourite_actions_map[ $key ] ) ) {
					continue;
				}
				$favourite_actions_map[ $key ] = (string) $hrow->action_type;
			}
		}
	}

	$details = array();
	foreach ( $res['rows'] as $row ) {
		$uid = (int) $row->user_id;
		if ( ! isset( $user_email_cache[ $uid ] ) ) {
			$uo                       = get_user_by( 'id', $uid );
			$user_email_cache[ $uid ] = $uo ? strtolower( (string) $uo->user_email ) : '';
		}
		$email = $user_email_cache[ $uid ];

		$company = '';
		$cid     = (int) ( $row->client_id ?? 0 );
		if ( $cid ) {
			if ( ! isset( $client_name_cache[ $cid ] ) ) {
				$client_name_cache[ $cid ] = \tamarind_reports\get_client_name_from_reference( $cid, 'N/A' );
			}
			$company = $client_name_cache[ $cid ];
		}

		if ( ! isset( $user_snapshot_cache[ $uid ] ) ) {
			$user_meta_key     = 'user_' . $uid;
			$user_status       = get_field( 'is_user_active', $user_meta_key ) ? 'Active' : 'Inactive';
			$related_client_id = (int) get_field( 'related_client', $user_meta_key );
			$user_favourite_posts = get_user_meta( $uid, 'user_favourite_posts', true );
			if ( ! is_array( $user_favourite_posts ) ) {
				$user_favourite_posts = array();
			}
			$user_favourite_posts = array_fill_keys( array_map( 'intval', $user_favourite_posts ), true );

			$subscription_plan_name = 'N/A';
			if ( function_exists( '\tamarind_subscriptions\subscription_plan\get_user_plan_id' ) ) {
				$plan_id = \tamarind_subscriptions\subscription_plan\get_user_plan_id( $uid );
				if ( $plan_id ) {
					if ( ! isset( $plan_name_cache[ $plan_id ] ) ) {
						$plan_name = (string) get_field( 'plan_name', $plan_id );
						if ( '' === $plan_name ) {
							$plan_name = (string) get_the_title( $plan_id );
						}
						$plan_name_cache[ $plan_id ] = '' !== $plan_name ? $plan_name : 'N/A';
					}
					$subscription_plan_name = $plan_name_cache[ $plan_id ];
				}
			}

			$user_snapshot_cache[ $uid ] = array(
				'status'            => $user_status,
				'related_client_id' => $related_client_id,
				'subscription_plan' => $subscription_plan_name,
				'favourite_posts'   => $user_favourite_posts,
			);
		}
		$user_snapshot = $user_snapshot_cache[ $uid ];

		$resolved_client_id = (int) ( $user_snapshot['related_client_id'] ?? 0 );
		if ( ! $resolved_client_id && 'client' === get_post_type( $cid ) ) {
			$resolved_client_id = $cid;
		}

		$client_status        = 'N/A';
		$client_users_count   = 0;
		$client_creation_date = '';
		if ( $resolved_client_id > 0 ) {
			if ( ! isset( $client_snapshot_cache[ $resolved_client_id ] ) ) {
				$is_client_active = get_field( 'is_client_active', $resolved_client_id ) ? 'Active' : 'Inactive';
				$client_post      = get_post( $resolved_client_id );
				$users_query      = new \WP_User_Query( array(
					'role'       => 'client',
					'fields'     => 'ID',
					'number'     => 1,
					'count_total' => true,
					'meta_query' => array(
						array(
							'key'     => 'related_client',
							'value'   => $resolved_client_id,
							'compare' => '=',
						),
					),
				) );

				$client_snapshot_cache[ $resolved_client_id ] = array(
					'status'        => $is_client_active,
					'users_count'   => (int) $users_query->get_total(),
					'creation_date' => $client_post ? (string) $client_post->post_date : '',
				);
			}
			$client_snapshot      = $client_snapshot_cache[ $resolved_client_id ];
			$client_status        = (string) $client_snapshot['status'];
			$client_users_count   = (int) $client_snapshot['users_count'];
			$client_creation_date = (string) $client_snapshot['creation_date'];
		}

		$post_id = (int) $row->post_id;
		$pi      = $post_id && isset( $post_info[ $post_id ] ) ? $post_info[ $post_id ] : array(
			'author' => '',
			'date'   => '',
			'title'  => ''
		);
		$pt      = $post_id && isset( $post_terms[ $post_id ] ) ? $post_terms[ $post_id ] : array(
			'ct_parent' => array(),
			'ct_child'  => array(),
			'geos'      => array(),
			'topics'    => array()
		);

		$details[] = array(
			'date'               => $row->page_date,
			'email'              => $email,
			'user_status'        => $user_snapshot['status'],
			'company'            => $company ?: 'N/A',
			'client_status'      => $client_status,
			'subscription_plan'  => $user_snapshot['subscription_plan'],
			'client_users_count' => $client_users_count,
			'title'              => $pi['title'],
			'post_id'            => $post_id,
			'download_url'       => $row->download_url ?? '',
			'download_type'      => $row->download_type ?? '',
			'event_type'         => (string) ( $row->event_type ?? '' ),
			'favourites'         => ( isset( $favourite_actions_map[ $uid . ':' . $post_id ] ) ?
				( 'added' === $favourite_actions_map[ $uid . ':' . $post_id ] ? 'bookmarked' : 'unbookmarked' ) :
				( isset( $user_snapshot['favourite_posts'][ $post_id ] ) ? 'bookmarked' : 'unbookmarked' ) ),
			'content_types'      => implode( ', ', array_values( array_unique( $pt['ct_parent'] ) ) ),
			'subcontent_types' => implode( ', ', array_values( array_unique( $pt['ct_child'] ) ) ),
			'geographies'        => implode( ', ', array_values( array_unique( $pt['geos'] ) ) ),
			'topics'             => implode( ', ', array_values( array_unique( $pt['topics'] ) ) ),
			'author'             => $pi['author'],
			'publication_date'   => $pi['date'],
			'last_login'         => format_usage_report_last_login( $uid ),
			'client_creation_date' => $client_creation_date,
		);
	}

	return array( $details, (int) $res['total'], (int) max( 1, (int) ceil( ( $res['total'] ?: 0 ) / $per_page ) ) );
}

/**
 * Paginated user IDs for the All Users view.
 * If $include_empty is false, paginates only users with activity in range.
 * If true, paginates all client users (optionally filtered by client term meta).
 * Returns [ ids => int[], total => int ].
 */
function user_ids_paginated( string $from, string $to, ?int $client_term_id_filter, bool $include_empty, int $per_page, ?int $page ): array {
	global $wpdb;
	$per_page = max( 1, (int) $per_page );
	$page     = max( 1, (int) $page );
	$offset   = ( $page - 1 ) * $per_page;

	if ( $include_empty ) {
		// Use WP_User_Query for all client users (with optional client meta filter)
		$args = array(
			'number'  => $per_page,
			'offset'  => $offset,
			'fields'  => 'ID',
			'role'    => 'client',
			'orderby' => 'ID',
			'order'   => 'ASC',
		);
		if ( $client_term_id_filter ) {
			$args['meta_query'] = array(
				array(
					'key'     => 'clientes',
					'value'   => (int) $client_term_id_filter,
					'compare' => '=',
				),
			);
		}
		$q     = new \WP_User_Query( $args );
		$ids   = array_map( 'intval', (array) $q->get_results() );
		$total = (int) $q->get_total();

		return array( 'ids' => $ids, 'total' => $total );
	}

	// Only users with activity (pageviews or downloads) in the date range
	$tables   = get_usage_activity_tables();
	$dl_table = $tables['dl_table'];
	$pv_table = $tables['pv_table'];

	$user_filters = get_usage_activity_user_filters( $client_term_id_filter, null );
	if ( ! $user_filters['has_results'] ) {
		return array( 'ids' => array(), 'total' => 0 );
	}
	$where_user_ids = $user_filters['where_user_ids'];

	// Total distinct users with activity
	$sql_total = $wpdb->prepare(
		"SELECT COUNT(*) FROM (
            SELECT DISTINCT user_id FROM {$dl_table} WHERE user_id > 0 AND page_date BETWEEN %s AND %s {$where_user_ids}
            UNION
            SELECT DISTINCT user_id FROM {$pv_table} WHERE user_id > 0 AND page_date BETWEEN %s AND %s {$where_user_ids}
        ) AS u",
		$from, $to, $from, $to
	);
	$total     = (int) $wpdb->get_var( $sql_total );

	// Page of user IDs ordered by last activity desc
	$sql_ids = $wpdb->prepare(
		"SELECT user_id
         FROM (
            SELECT user_id, MAX(page_date) AS last_date
            FROM (
                SELECT user_id, page_date FROM {$dl_table} WHERE user_id > 0 AND page_date BETWEEN %s AND %s {$where_user_ids}
                UNION ALL
                SELECT user_id, page_date FROM {$pv_table} WHERE user_id > 0 AND page_date BETWEEN %s AND %s {$where_user_ids}
            ) t
            GROUP BY user_id
         ) x
         ORDER BY last_date DESC
         LIMIT %d OFFSET %d",
		$from, $to, $from, $to, $per_page, $offset
	);
	$ids     = array_map( 'intval', (array) $wpdb->get_col( $sql_ids ) );

	return array( 'ids' => $ids, 'total' => $total );
}

function user_rows( array $users, array $ct_sets, array $pv_counts, array $dl_counts, array $user_posts_map, bool $include_empty = false ): array {
	$rows = array();
	foreach ( $users as $u ) {
		$user_id = (int) $u->ID ?? 0;
		if ( ! $user_id ) {
			continue;
		}

		$user_email = strtolower( (string) ( $u->user_email ?? '' ) );
		// Client term name for user.
		$cid         = get_field( 'related_client', "user_{$user_id}" );
		$client_name = \tamarind_reports\get_client_name_from_reference( $cid, 'N/A' );

		$page_views      = (int) ( $pv_counts[ $user_id ] ?? 0 );
		$downloads_count = (int) ( $dl_counts[ $user_id ] ?? 0 );

		if ( ! $include_empty && 0 === $page_views && 0 === $downloads_count ) {
			continue;
		}

		// Distinct posts interacted (from a precomputed map)
		$post_ids    = array_keys( $user_posts_map[ $user_id ] ?? array() );
		$post_ids_in = empty( $post_ids ) ? '' : implode( ', ', array_map( 'intval', $post_ids ) );

		list( $topics_count, $top_topics_joined, $geographies_joined, $top_geographies_joined ) = \tamarind_reports\topics_geos( $post_ids_in );

		// Regulatory vs. Market percentages
		list( $regulatory_percent, $market_percent ) = \tamarind_reports\regulatory_vs_market_percent( $post_ids_in, $ct_sets );

		$rows[] = array(
			'client'             => $client_name,
			'email'              => $user_email,
			'page_views'         => $page_views,
			'downloads_count'    => $downloads_count,
			'topics_count'       => $topics_count,
			'top_topics'         => $top_topics_joined,
			'geos'               => $geographies_joined,
			'top_geos'           => $top_geographies_joined,
			'regulatory_percent' => $regulatory_percent,
			'market_percent'     => $market_percent,
		);
	}

	return $rows;
}
