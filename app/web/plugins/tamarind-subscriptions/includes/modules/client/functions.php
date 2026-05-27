<?php
/**
 * Functions for Tamarind Clients.
 *
 * @package tamarind_subscriptions
 */

namespace tamarind_subscriptions\client;

defined( 'ABSPATH' ) || exit;

/**
 * Check if the subscription plan associated with a client has expired.
 *
 * @param int $client_id The client post ID.
 * @return bool True if the plan has expired, false otherwise.
 */
function is_client_plan_expired( int $client_id = 0 ): bool {
	if ( 0 === $client_id ) {
		return true;
	}
	$expiration_date = get_field( 'expiration_date_plan', $client_id );
	if ( empty( $expiration_date ) ) {
		// if no expiration date is set, we consider the plan not expired.
		return false;
	}
	$current_date = gmdate( 'Y-m-d' );
	return ( $current_date > $expiration_date );
}
