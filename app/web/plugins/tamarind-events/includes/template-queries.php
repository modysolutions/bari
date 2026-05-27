<?php
/**
 * Template Queries for Tamarind Events Plugin.
 *
 * @package Tamarind_Events
 */

namespace tamarind_events;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Retrieves the first month and year with events starting from today.
 *
 * @return array An associative array containing 'month' and 'year'.
 */
function get_first_month_with_events() {
	$args = array(
		'post_type'      => 'events',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'meta_key'       => 'event_date_start',
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		'meta_query'     => array(
			array(
				'key'     => 'event_date_start',
				'value'   => gmdate( 'Y-m-d' ), // Today's date.
				'compare' => '>=',
				'type'    => 'DATE',
			),
		),
	);

	$cache_args = array(
		'subscription_plan' => \tamarind_subscriptions\subscription_plan\get_user_plan_id(),
		'args' => $args,
	);
	$cache_key  = 'first_month_with_events_' . md5( serialize( $cache_args ) );
	$cache_group = 'events';
	$query = wp_cache_get( $cache_key, $cache_group );
	if ( ! $query ) {
		$query = new \WP_Query( $args );
		wp_cache_set( $cache_key, $query, $cache_group, 10 * MINUTE_IN_SECONDS );
	}

	if ( $query->have_posts() ) {
		$query->the_post();
		$event_date = get_field( 'event_date_start', get_the_ID() );
		$date       = \DateTime::createFromFormat( 'Y-m-d', $event_date );

		wp_reset_postdata();

		return array(
			'month' => $date->format( 'm' ),
			'year'  => $date->format( 'Y' ),
		);
	}

	return array(
		'month' => gmdate( 'm' ),
		'year'  => gmdate( 'Y' ),
	);
}

/**
 * Retrieves events within a specified date range.
 *
 * @param string $first_day The start date of the range in 'Y-m-d' format.
 * @param string $last_day  The end date of the range in 'Y-m-d' format.
 * @param int    $limit     The maximum number of events to retrieve. Default is -1 (no limit).
 * @return \WP_Query The query object containing the events.
 */
function get_events_by_range_query( $first_day, $last_day, $limit = -1 ) {
	$args = array(
		'post_type'      => 'events',
		'posts_per_page' => $limit,
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'meta_key'       => 'event_date_start',
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		'meta_query'     => array(
			array(
				'key'     => 'event_date_start',
				'value'   => array( $first_day, $last_day ),
				'compare' => 'BETWEEN',
				'type'    => 'DATE',
			),
		),
	);

	$cache_args = array(
		'subscription_plan' => \tamarind_subscriptions\subscription_plan\get_user_plan_id(),
		'args' => $args,
	);
	$cache_key = 'events_by_range_' . md5( serialize( $cache_args ) );
	$cache_group = 'events';
	$events_query = wp_cache_get( $cache_key, $cache_group );
	if( ! $events_query ) {
		$events_query = new \WP_Query( $args );
		wp_cache_set( $cache_key, $events_query, $cache_group, 10 * MINUTE_IN_SECONDS );
	}

	return $events_query;
}


/**
 * Get available months with events using WP_Query
 *
 * @return array Array of months with events
 */
function get_available_months_with_events_query() {
	$args = array(
		'post_type'      => 'events',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'meta_key'       => 'event_date_start',
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		'meta_query'     => array(
			array(
				'key'     => 'event_date_start',
				'value'   => gmdate( 'Y-m-d' ),
				'compare' => '>=',
				'type'    => 'DATE',
			),
		),
	);

	$cache_args = array(
		'subscription_plan' => \tamarind_subscriptions\subscription_plan\get_user_plan_id(),
		'args' => $args,
	);
	$cache_key = 'available_months_with_events_' . md5( serialize( $cache_args ) );
	$cache_group = 'events';
	$query = wp_cache_get( $cache_key, $cache_group );
	if ( ! $query ) {
		$query = new \WP_Query( $args );
		wp_cache_set( $cache_key, $query, $cache_group, 10 * MINUTE_IN_SECONDS );
	}

	$months = array();

	if ( $query->have_posts() ) {
		$unique_months = array();

		while ( $query->have_posts() ) {
			$query->the_post();
			$event_date = get_field( 'event_date_start', get_the_ID() );
			$date       = \DateTime::createFromFormat( 'Y-m-d', $event_date );

			if ( false === $date ) {
				continue;
			}

			$month_year = $date->format( 'Y-m' );
			$year       = $date->format( 'Y' );
			$month      = $date->format( 'm' );

			if ( ! isset( $unique_months[ $month_year ] ) ) {
				$unique_months[ $month_year ] = array(
					'month'      => $month,
					'year'       => $year,
					'month_year' => $month_year,
					'display'    => $date->format( 'F Y' ),
				);
			}
		}

		// Sort the months chronologically.
		usort(
			$unique_months,
			function ( $a, $b ) {
				return strcmp( $a['month_year'], $b['month_year'] );
			}
		);

		$months = array_values( $unique_months );
	}

	wp_reset_postdata();
	return $months;
}

/**
 * Retrieves a query for upcoming events.
 *
 * Fetches events that have not yet ended, ordered by their start date.
 *
 * @return \WP_Query Query object containing upcoming events.
 */
function get_upcoming_events_query() {

	$today = gmdate( 'Ymd' );

	$args = array(
		'post_type'      => 'events',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'meta_key'       => 'event_date_start',
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		'meta_query'     => array(
			array(
				'key'     => 'event_date_end',
				'value'   => $today,
				'compare' => '>=',
				'type'    => 'DATE',
			),
		),
	);

	$cache_args = array(
		'subscription_plan' => \tamarind_subscriptions\subscription_plan\get_user_plan_id(),
		'args' => $args,
	);
	$cache_key = 'upcoming_events_' . md5( serialize( $cache_args ) );
	$cache_group = 'events';
	$query = wp_cache_get( $cache_key, $cache_group );
	if ( ! $query ) {
		$query = new \WP_Query( $args );
		wp_cache_set( $cache_key, $query, $cache_group, 10 * MINUTE_IN_SECONDS );
	}

	return $query;
}
