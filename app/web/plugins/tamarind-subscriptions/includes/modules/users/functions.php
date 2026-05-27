<?php
/**
 * Handles user-related functionalities for the Tamarind Subscriptions plugin.
 *
 * @package tamarind_subscriptions
 */

namespace tamarind_subscriptions\users;

defined( 'ABSPATH' ) || exit;

/**
 * Check if the user date expiration plan has expired.
 *
 * @param int $user_id The user ID. If 0, uses the current user.
 * @return bool True if the date has expired, false otherwise.
 */
function is_user_plan_expired( int $user_id = 0 ): bool {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( 0 === $user_id ) {
		return true;
	}

	$expiration_date = get_field( 'user_expirate_date', 'user_' . $user_id );
	if ( ! empty( $expiration_date ) ) {
		$current_date = gmdate( 'Y-m-d' );
		return ( $current_date > $expiration_date );
	}
	return true;
}

/**
 * Get the expiration date of the user's subscription plan.
 *
 * @param int $user_id The user ID. If 0, uses the current user.
 * @return string|null The expiration date in 'Y-m-d' format, or null if not set.
 */
function get_date_expiration_plan( int $user_id = 0 ): ?string {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( 0 === $user_id ) {
		return null;
	}

	// Check user-level.
	$expiration_date = get_field( 'user_expirate_date', 'user_' . $user_id );
	if ( ! empty( $expiration_date ) ) {
		return $expiration_date;
	} else {
		// Check client-level.
		$client_id = get_field( 'related_client', 'user_' . $user_id );
		if ( $client_id ) {
			$client_expiration_date = get_field( 'expiration_date_plan', $client_id );
			if ( $client_expiration_date ) {
				return $client_expiration_date;
			}
		}
	}
	return null;
}


/**
 * Check whether a user is marked as Chinese, falling back to their related client.
 *
 * @param int $user_id The user ID. If 0, uses the current user.
 * @return bool True if the user (or their related client) is marked as Chinese, false otherwise.
 */
function is_user_chinese( int $user_id = 0 ): bool {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( 0 === $user_id ) {
		return false;
	}

	// Check client-level.
	$client_id = get_field( 'related_client', 'user_' . $user_id );
	if ( $client_id ) {
		$is_chinese = get_field( 'is_client_chinese', $client_id );
		if ( $is_chinese ) {
			return $is_chinese;
		}
	}

	return false;
}
