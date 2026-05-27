<?php
/**
 * Access functions for the Tamarind Subscriptions plugin.
 *
 * @package tamarind_subscriptions
 */

namespace tamarind_subscriptions\access;

defined( 'ABSPATH' ) || exit;

use function tamarind_base\taxonomies\{get_all_content_types};
use function tamarind_subscriptions\client\{is_client_plan_expired};
use function tamarind_subscriptions\users\{get_date_expiration_plan};

/**
 * Import function get_data_plan from tamarind_subscriptions\subscription_plan
 */
use function tamarind_subscriptions\subscription_plan\{get_data_plan};

/**
 * Get the post ID, defaulting to the current post if none is provided.
 *
 * @param int $post_id The post ID (default is 0, which means current post).
 * @return int The post ID.
 */
function get_post_id( int $post_id = 0 ): int {
	return ( ( 0 === $post_id ) ? get_the_ID() : $post_id );
}

/**
 * Check if the current user has any of the specified roles.
 *
 * @param array $roles Array of role slugs to check against.
 * @return bool True if the user has any of the specified roles, false otherwise.
 */
function is_role( array $roles ): bool {
	if ( is_user_logged_in() ) {
		$user = wp_get_current_user();
		if ( array_intersect( $roles, $user->roles ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Check if the current post is marked as open content.
 *
 * @param int $post_id The post ID (default is current post).
 * @return bool True if the post is open content, false otherwise.
 */
function is_post_open_content( int $post_id = 0 ): bool {
	$post_id = get_post_id( $post_id );
	return get_field( 'open', $post_id ) ?? false;
}

/**
 * Check if the current user is allowed access based on specific user or group settings.
 *
 * @param int $post_id The post ID (default is current post).
 * @return bool True if the user is allowed access, false otherwise.
 */
function is_user_open_content( int $post_id = 0 ): bool {
	if ( get_field( 'allow_to_specific_user_group', $post_id ) ) {
		$allowed_groups = get_field( 'user_groups', $post_id );
		$data_plan          = get_data_plan();
		$current_user_group = $data_plan['plan_contents'];

		if ( in_array( $current_user_group, $allowed_groups ) ) { // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			return true;
		}
	}
	if ( get_field( 'allow_to_specific_user', $post_id ) ) {
		$allowed_users = get_field( 'users', $post_id );
		$current_user_id = get_current_user_id();

		if ( ! is_array( $allowed_users ) ) {
			return false;
		}

		return in_array( $current_user_id, $allowed_users, true ); // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
	}
	return false;
}

/**
 * Extract term IDs from an array of WP_Term objects.
 *
 * @param array $terms Array of WP_Term objects.
 * @return array Array of term IDs.
 */
function get_term_ids( array $terms ): array {
	$term_ids = array();
	foreach ( $terms as $term ) {
		$term_ids[] = $term->term_id;
	}
	return $term_ids;
}

/**
 * Get the content type terms associated with the current post.
 *
 * @param int    $post_id   The post ID (not used, kept for compatibility).
 * @param string $type_return The format to return the terms ('WP_Term_Object' or 'term_id').
 * @return array|false Array of WP_Term objects or term IDs, or false if no terms found.
 */
function get_post_terms_content_type( int $post_id = 0, string $type_return = 'WP_Term_Object' ): array|false {
	$post_id  = get_post_id( $post_id );
	$taxonomy = 'content_types';
	$terms    = get_the_terms( $post_id, $taxonomy );

	if ( ! $terms || is_wp_error( $terms ) ) {
		return false;
	}

	if ( 'term_id' === $type_return ) {
		return get_term_ids( $terms );
	}
	return $terms;
}

/**
 * Get the geography terms associated with the current post.
 *
 * @param int    $post_id   The post ID (not used, kept for compatibility).
 * @param string $type_return The format to return the terms ('WP_Term_Object' or 'term_id').
 * @return array|false Array of WP_Term objects or term IDs, or false if no terms found.
 */
function get_post_terms_geography( int $post_id = 0, string $type_return = 'WP_Term_Object' ): array|false {
	$post_id  = get_post_id( $post_id );
	$taxonomy = 'geography';
	$terms    = get_the_terms( $post_id, $taxonomy );

	if ( ! $terms || is_wp_error( $terms ) ) {
		return false;
	}

	if ( 'term_id' === $type_return ) {
		return get_term_ids( $terms );
	}
	return $terms;
}

/**
 * Check if the current post has the 'alerts' content type.
 *
 * @param int $post_id The post ID (default is current post).
 * @return bool True if the post has the 'alerts' content type, false otherwise.
 */
function is_post_alert( int $post_id = 0 ): bool {
	$post_id            = get_post_id( $post_id );
	$terms_content_type = get_post_terms_content_type( $post_id, 'WP_Term_Object' );
	if ( ! $terms_content_type ) {
		return false;
	}

	foreach ( $terms_content_type as $term ) {
		if ( 'alerts' === $term->slug ) {
			return true;
		}
	}
	return false;
}

/**
 * Check if all content types of the current post are within the specified types.
 *
 * @param int   $post_id The post ID (default is current post).
 * @param array $content_types_to_compare Array of content type term IDs to compare against.
 * @return bool True if all content types of the post are within the specified types, false otherwise.
 */
function is_all_post_content_type_into( int $post_id = 0, array $content_types_to_compare = array() ): bool {
	$post_id            = get_post_id( $post_id );
	$terms_content_type = get_post_terms_content_type( $post_id, 'term_id' );

	if ( empty( $content_types_to_compare ) || ! $terms_content_type ) {
		return false;
	}
	return empty( array_diff( $terms_content_type, $content_types_to_compare ) );
}

/**
 * Check if the current post's content type is marked as open content in the options.
 *
 * @param int $post_id The post ID (default is current post).
 * @return bool True if the content type is open content, false otherwise.
 */
function is_content_type_open_content( int $post_id = 0 ): bool {
	$post_id        = get_post_id( $post_id );
	$open_terms_ids = get_field( 'open_all_content_type', 'options' );
	if ( empty( $open_terms_ids ) ) {
		return false;
	}
	return is_all_post_content_type_into( $post_id, $open_terms_ids );
}

/**
 * Check if access is still valid for the current user.
 *
 * @param int $client_id The client post ID used as fallback.
 * @return bool True if access is still valid, false otherwise.
 */
function is_on_date( int $client_id ): bool {
	// If the plan as not expired, allow access.
	if ( ! is_client_plan_expired( $client_id ) ) {
		return true;
	}

	$expiration_date = get_date_expiration_plan();
	if ( empty( $expiration_date ) ) {
		return false;
	}

	return ( gmdate( 'Y-m-d' ) <= $expiration_date );
}

/**
 * Check if the current user's subscription plan allows access to the current post's content type and geography.
 *
 * @param int $post_id The post ID (default is current post).
 * @return bool True if the subscription plan allows access, false otherwise.
 */
function is_subscription_plan_open_content( int $post_id = 0 ): bool {
	$post_id            = get_post_id( $post_id );
	$data_plan          = get_data_plan();
	$plan_content_types = $data_plan['plan_contents'];

	$client_id = absint( get_field( 'related_client', 'user_' . get_current_user_id() ) );
	if ( 0 === $client_id ) {
		$client_id = absint( $data_plan['plan_clients'][0] ?? 0 );
	}
	if ( ! is_on_date( $client_id ) ) {
		return false;
	}

	if ( is_all_post_content_type_into( $post_id, $plan_content_types ) && is_access_geography( $post_id, $data_plan ) ) {
		return true;
	}
	return false;
}

/**
 * Check if the current post's geography terms are within the allowed geography of the subscription plan.
 *
 * @param int   $post_id The post ID (default is current post).
 * @param array $data_plan The subscription plan data.
 * @return bool True if the post's geography is allowed, false otherwise.
 */
function is_access_geography( int $post_id = 0, array $data_plan = array() ): bool {
	$post_id   = get_post_id( $post_id );
	$is_alert  = is_post_alert( $post_id );

	$plan_access_geography = $data_plan['plan_geo_tax'];
	if ( $is_alert ) {
		$plan_access_geography = $data_plan['plan_geo_alerts_tax'];
	}

	// If no geography restrictions, allow access.
	if ( empty( $plan_access_geography ) ) {
		return true;
	}

	$terms_geography = get_post_terms_geography( $post_id, 'term_id' );
	if ( ! $terms_geography ) {
		return false;
	}

	if ( ! empty( array_intersect( $plan_access_geography, $terms_geography ) ) ) {
		return true;
	}

	return false;
}

/**
 * Get the roles associated with a specific ACF field.
 *
 * @param string $acf_field The ACF field name.
 * @return array Array of role slugs.
 */
function get_type_access_roles( string $acf_field ): array {
	$roles = get_field( $acf_field, 'option' );
	if ( ! is_array( $roles ) ) {
		$roles = array();
	}
	return $roles;
}

/**
 * Determine if the current user can read the specified post.
 *
 * @param int $post_id The post ID (default is current post).
 * @return bool True if the user can read the post, false otherwise.
 */
function current_user_can_read_post( int $post_id = 0 ): bool { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	$post_id              = get_post_id( $post_id );
	$administrative_roles = get_type_access_roles( 'roles_administrative_access' );
	$client_roles         = get_type_access_roles( 'roles_client_access' );

	if ( is_role( $administrative_roles ) ) {
		return true;
	}

	if ( is_content_type_open_content( $post_id ) ) {
		return true;
	}

	if ( is_post_open_content( $post_id ) ) {
		return true;
	}

	if ( is_user_open_content( $post_id ) ) {
		return true;
	}

	if ( is_role( $client_roles ) ) {
		if ( is_subscription_plan_open_content( $post_id ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Checks if the current user can read a specific content type.
 *
 * @param string $content_type_needle The content type to check for access.
 * @return bool True if the user has access, false otherwise.
 */
function current_user_can_read_content_type( string $content_type_needle ): bool {
	$data_plan         = get_data_plan();
	$terms_content     = $data_plan['plan_contents'] ?? array();
	$all_content_types = get_all_content_types();

	$content_type_filtered    = array_values( array_filter( $all_content_types, fn( $term ) => $content_type_needle === $term->slug ) );
	$content_type_filtered_id = array_map( fn( $term ) => $term->term_id, $content_type_filtered );

	if ( in_array( $content_type_filtered_id[0], $terms_content, true ) ) {
		return true;
	}

	return false;
}
