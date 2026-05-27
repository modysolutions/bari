<?php
/**
 * Expiration Banner countdown.
 *
 * @package tamarind_subscriptions
 */

namespace tamarind_subscriptions\notifications;

defined( 'ABSPATH' ) || exit;


/**
 * Get expiration banner days setting for the current user
 *
 * Hierarchy: user-level > client-level > global-level
 * Returns 0 if no value is found
 *
 * @param int $user_id The user ID. If 0, uses the current user.
 *
 * @return int
 */
function get_expiration_banner_days( int $user_id = 0 ) : int {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( ! $user_id ) {
		return 0;
	}

	// First, check user-level setting.
	$user_days = get_field( 'expiration_banner_days_user', 'user_' . $user_id );

	if ( ! empty( $user_days ) && is_numeric( $user_days ) && $user_days > 0 ) {
		return (int) $user_days;
	}

	// Second, check client-level setting.
	$client_id = get_field( 'related_client', 'user_' . $user_id );

	if ( ! empty( $client_id ) ) {
		$client_days = get_field( 'expiration_banner_days_client', $client_id );

		if ( ! empty( $client_days ) && is_numeric( $client_days ) && $client_days > 0 ) {
			return (int) $client_days;
		}
	}

	// Third, check global-level setting.
	$global_days = get_field( 'expiration_banner_days_global', 'option' );

	if ( ! empty( $global_days ) && is_numeric( $global_days ) && $global_days > 0 ) {
		return (int) $global_days;
	}

	// Default: return 0 if no valid value found.
	return 0;
}

/**
 * Check if expiration banner is active for the current user
 *
 * Hierarchy: user-level > client-level > global-level
 * Empty values (new fields) are treated as true
 *
 * @param int $user_id The user ID. If 0, uses the current user.
 * @return bool
 */
function is_expiration_banner_active( int $user_id = 0 ) : bool {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( ! $user_id ) {
		return false;
	}

	// Check user-level setting.
	$user_banner_active = get_field( 'expiration_banner_user_active', 'user_' . $user_id );

	if ( false === $user_banner_active ) {
		return false;
	}

	// Check client-level setting.
	$client_id = get_field( 'related_client', 'user_' . $user_id );

	if ( $client_id ) {
		$client_banner_active = get_field( 'expiration_banner_client_active', $client_id );

		if ( false === $client_banner_active ) {
			return false;
		}
	}

	// Check global-level setting.
	$global_banner_active = get_field( 'expiration_banner_global_active', 'option' );
	if ( false === $global_banner_active ) {
		return false;
	}

	return true;
}

/**
 * Show Expiration Banner
 */
function show_expiration_banner() {
	$user_id = get_current_user_id();

	if ( ! $user_id ) {
		return;
	}

	// Check if expiration banner is active.
	$expiration_banner_active = is_expiration_banner_active( $user_id );
	if ( false === $expiration_banner_active ) {
		return;
	}

	// Get expiration days for show banner countdown.
	$expiration_banner_days = get_expiration_banner_days( $user_id );
	if ( 0 >= $expiration_banner_days ) {
		return;
	}

	// Get expiration date.
	$date_expiration = \tamarind_subscriptions\users\get_date_expiration_plan( $user_id );
	if ( empty( $date_expiration ) ) {
		// if no expiration date is set, we consider the plan not expired.
		return;
	}

	$current_date = gmdate( 'Y-m-d' );

	// Calculate banner start date.
	$banner_start_date = gmdate( 'Y-m-d', strtotime( $date_expiration . ' -' . $expiration_banner_days . ' days' ) );

	// Calculate days remaining.
	$days_remaining = round( ( strtotime( $date_expiration ) - strtotime( $current_date ) ) / ( 60 * 60 * 24 ) );

	// Prepare data for template.
	$banner_data = array(
		'days_remaining' => $days_remaining,
	);

	// Check if current date is on or after the banner start date.
	if ( $current_date >= $banner_start_date ) {
		load_template( TMS_PLUGIN_DIR . 'template-parts/expiration-banner.php', false, $banner_data );
	}
}
