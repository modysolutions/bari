<?php
/**
 * Queries for the Templates modules.
 *
 * @package Tamarind_UserHome
 */

namespace tamarind_userhome;

defined( 'ABSPATH' ) || exit;

/**
 * Get the latest content query arguments.
 *
 * @param int $limit Number of posts to retrieve.
 * @return object \WP_Query The latest content query object.
 */
function get_query_latest_content( $limit ) {
	
	$args = array(
		'post_type'              => 'post',
		'posts_per_page'         => $limit,
		'orderby'                => 'date',
		'order'                  => 'DESC',
		'post_status'            => 'publish',
		'ignore_sticky_posts'    => true,
		'no_found_rows'          => true,
		'update_post_meta_cache' => false,
		'update_post_term_cache' => false,
	);

	$cache_args = array(
		'subscription_plan' => \tamarind_subscriptions\subscription_plan\get_user_plan_id(),
		'args' => $args,
	);
	$cache_key = 'tamarind_userhome_latest_content_' . md5( serialize( $cache_args ) );
	$cache_group = 'latest_content';
	$latest_content = wp_cache_get( $cache_key, $cache_group );
	if ( ! $latest_content ) {
		$latest_content = new \WP_Query( $args );
		wp_cache_set( $cache_key, $latest_content, $cache_group, 10 * MINUTE_IN_SECONDS );
	}
	return $latest_content;
}


/**
 * Get the company news query arguments.
 *
 * @param int $limit Number of posts to retrieve.
 * @return object \WP_Query The company news query object.
 */
function get_query_company_news( $limit ) {
	$args = array(
		'post_type'        => 'company-news',
		'post_status'      => 'publish',
		'posts_per_page'   => $limit,
		'suppress_filters' => true,   // Ignores filters from plugins like Groups.
	);
	$cache_args = array(
		'subscription_plan' => \tamarind_subscriptions\subscription_plan\get_user_plan_id(),
		'args' => $args,
	);
	$cache_key = 'company_news_' . md5( serialize( $cache_args ) );
	$cache_group = 'company_news';
	$company_news = wp_cache_get( $cache_key, $cache_group );
	if ( ! $company_news ) {
		$company_news = new \WP_Query( $args );
		wp_cache_set( $cache_key, $company_news, $cache_group, 10 * MINUTE_IN_SECONDS );
	}
	return $company_news;
}


/**
 * Retrieves recommended posts based on user favourites or latest content.
 *
 * - If user has favourites, gets related contents from those favourites
 * - If no favourites, gets related contents from latest content
 * - Always excludes both source posts AND latest content posts
 * - Filters results to only include posts from the last 12 months
 * - Limits the number of results to the specified limit
 * - Returns results in random order
 *
 * @param  int  $limit Maximum number of recommendations to return.
 * @return WP_Query Query object containing recommended posts.
 */
function get_query_recommendations( int $limit ) : \WP_Query {
	$user_id         = get_current_user_id();
	$source_post_ids = get_field( 'user_favourite_posts', 'user_' . $user_id );

	if ( empty( $source_post_ids ) ) {
		// If no favourites, get latest content IDs.
		$latest_content  = get_query_latest_content( $limit );
		$source_post_ids = wp_list_pluck( $latest_content->posts, 'ID' );
	}

	// Return an empty query if no source posts found.
	if ( empty( $source_post_ids ) ) {
		return cache_recommendations();
	}

	$related_posts_ids = array();

	// Collect all related posts from source posts.
	foreach ( $source_post_ids as $post_id ) {
		$related = get_field( 'related_contents', $post_id );

		if ( ! empty( $related ) ) {
			foreach ( $related as $post ) {
				$related_posts_ids[] = $post->ID;
			}
		}
	}

	// Remove duplicates and exclude source posts AND latest content.
	$related_posts_ids = array_unique( $related_posts_ids );
	$related_posts_ids = array_diff( $related_posts_ids, $source_post_ids );

	// Return empty query if no valid recommendations found.
	if ( empty( $related_posts_ids ) ) {
		return cache_recommendations();
	}

	// Calculate date range (last 12 months from now).
	$date_one_year_ago = gmdate( 'Y-m-d', strtotime( '-1 year' ) );

	// Return final query with random order and date filter.
	return cache_recommendations( array(
		'post_type'           => 'any',
		'post__in'            => $related_posts_ids,
		'orderby'             => 'rand',
		'posts_per_page'      => $limit,
		'ignore_sticky_posts' => 1,
		'date_query'          => array(
			array(
				'after'     => $date_one_year_ago,
				'inclusive' => true,
			),
		),
		'post_status'         => 'publish',
	));
}

function cache_recommendations( array $args = array() ) : \WP_Query {
	$cache_args = array(
		'subscription_plan' => \tamarind_subscriptions\subscription_plan\get_user_plan_id(),
		'args' => $args,
	);
	$cache_key = 'recommendations_' . md5( serialize( $cache_args ) );
	$cache_group = 'recommendations';
	$recommendations = wp_cache_get( $cache_key, $cache_group );
	if ( ! $recommendations ) {
		$recommendations = new \WP_Query( $args );
		wp_cache_set( $cache_key, $recommendations, $cache_group, 10 * MINUTE_IN_SECONDS );
	}
	return $recommendations;
}
