<?php
/**
 * Functions for Tamarind Subscription Plan.
 *
 * @package tamarind_subscriptions
 */

namespace tamarind_subscriptions\subscription_plan;

use function tamarind_subscriptions\access\{is_role, get_type_access_roles};

defined( 'ABSPATH' ) || exit;

/**
 * Get the subscription plan ID associated with a user.
 *
 * @param int $user_id The user ID. Defaults to current user if 0.
 * @return int|null The subscription plan ID or null if not found.
 */
function get_user_plan_id( int $user_id = 0 ): int|null {
	if ( 0 === $user_id ) {
		$user_id = get_current_user_id();
	}

	$custom_subscription_plan = get_user_meta( $user_id, 'related_subscription_plan', true );
	if ( ! empty( $custom_subscription_plan ) ) {
		// get_user_meta returns an array when the field is multiple or a single value when not.
		$plan_id = is_array( $custom_subscription_plan ) ? absint( reset( $custom_subscription_plan ) ) : absint( $custom_subscription_plan );
		return $plan_id > 0 ? $plan_id : null;
	}

	$user_client_id = get_user_meta( $user_id, 'related_client', true );
	if ( empty( $user_client_id ) ) {
		return null;
	}

	return get_client_plan_id( absint( $user_client_id ) );
}

/**
 * Get the subscription plan ID associated with a client.
 *
 * @param int $client_id The client post ID.
 * @return int|null The subscription plan ID or null if not found.
 */
function get_client_plan_id( int $client_id = 0 ): int|null {
	if ( 0 === $client_id ) {
		return null;
	}
	return get_field( 'subscription_plan', $client_id ) ? absint( get_field( 'subscription_plan', $client_id ) ) : null;
}

/**
 * Get data for a subscription plan by its slug.
 *
 * @return array Associative array containing plan details or empty array if not found.
 */
function get_data_plan(): array {
	$user_plan_id = get_user_plan_id();
	if ( null === $user_plan_id ) {
		return array();
	}

	$args = array(
		'post_type'   => 'subscription-plan',
		'post_status' => 'publish',
		'p'           => $user_plan_id,
		'numberposts' => 1,
	);
	$plan_posts = get_posts( $args );

	$plan_id = $plan_posts[0]->ID ?? 0;
	if ( 0 === $plan_id ) {
		return array();
	}

	$plan_name     = get_field( 'plan_name', $plan_id ) ?? '';
	$plan_slug     = get_field( 'plan_slug', $plan_id ) ?? '';
	$plan_contents = get_field( 'plan_contents', $plan_id ) ?? '';

	$plan_geography        = get_field( 'plan_filters_geo_tax', $plan_id ) ?? array();
	$plan_geography_alerts = get_field( 'plan_filters_geo_alerts_tax', $plan_id ) ?? array();

	// If filters are not enabled, reset geography fields to empty arrays.
	if ( ! get_field( 'plan_filters', $plan_id ) ) {
		$plan_geography = array();
		$plan_geography_alerts = array();
	}

	$plan_clients = get_field( 'plan_clients', $plan_id ) ?? array();
	$plan_users   = get_field( 'plan_users', $plan_id ) ?? array();

	$plan_demo               = get_field( 'plan_demo', $plan_id ) ?? false;
	$plan_allow_download_pdf = get_field( 'plan_allow_pdf_downloads', $plan_id ) ?? false;

	$data_plan = array(
		'plan_id'                  => $plan_id,
		'plan_slug'                => $plan_slug,
		'plan_name'                => $plan_name,
		'plan_contents'            => $plan_contents,
		'plan_geo_tax'             => $plan_geography,
		'plan_geo_alerts_tax'      => $plan_geography_alerts,
		'plan_clients'             => $plan_clients,
		'plan_users'               => $plan_users,
		'plan_demo'                => $plan_demo,
		'plan_allow_pdf_downloads' => $plan_allow_download_pdf,
	);
	return $data_plan;
}

/**
 * Get all subscription plans.
 *
 * @return array List of subscription plans with their IDs, names, and slugs.
 */
function get_all_plans(): array {
	$args = array(
		'post_type'   => 'subscription-plan',
		'post_status' => 'publish',
		'numberposts' => -1,
	);
	$plan_posts = get_posts( $args );
	$plans      = array();

	foreach ( $plan_posts as $plan_post ) {
		$plan_id = $plan_post->ID;

		$plans[] = array(
			'plan_id'   => $plan_id,
			'plan_name' => get_field( 'plan_name', $plan_id ) ?? '',
			'plan_slug' => get_field( 'plan_slug', $plan_id ) ?? '',
		);
	}

	return $plans;
}

/**
 * Check if the current user's subscription plan includes alerts.
 *
 * @return bool True if the plan includes alerts, false otherwise.
 */
function is_alerts_plan(): bool {
	$administrative_roles = get_type_access_roles( 'roles_administrative_access' );
	if ( is_role( $administrative_roles ) ) {
		return true;
	}

	$data_plan = get_data_plan();
	if ( empty( $data_plan ) ) {
		return false;
	}

	$plan_contents = $data_plan['plan_contents'] ?? '';
	$id_alert = get_term_by( 'slug', 'alerts', 'content_types' )->term_id;
	if ( ! $id_alert ) {
		return false;
	}

	return in_array( $id_alert, $plan_contents, true );
}
