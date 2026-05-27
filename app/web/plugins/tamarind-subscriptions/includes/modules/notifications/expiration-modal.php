<?php
/**
 * Expiration Modal alert.
 *
 * @package tamarind_subscriptions
 */

namespace tamarind_subscriptions\notifications;

defined( 'ABSPATH' ) || exit;


/**
 * Show Expiration Modal in footer if conditions are met
 *
 * @return void
 */
function show_expiration_modal() {
	$user_id = get_current_user_id();

	if ( ! $user_id ) {
		return;
	}

	// Check if expiration modal is active.
	$expiration_modal_active = is_expiration_modal_active( $user_id );
	if ( false === $expiration_modal_active ) {
		return;
	}

	// Get expiration days for show Modal.
	$expiration_banner_days = get_expiration_banner_days( $user_id );
	if ( 0 >= $expiration_banner_days ) {
		return;
	}

	// Get expiration date.
	$date_expiration = \tamarind_subscriptions\users\get_date_expiration_plan( $user_id );
	if ( empty( $date_expiration ) ) {
		return;
	}

	$current_date = gmdate( 'Y-m-d' );

	// Calculate the date when the banner should start showing.
	$banner_start_date = gmdate( 'Y-m-d', strtotime( $date_expiration . ' -' . $expiration_banner_days . ' days' ) );

	// Show modal if current date is on or after the banner start date.
	if ( $current_date >= $banner_start_date ) {
		load_template( TMS_PLUGIN_DIR . 'template-parts/expiration-modal.php' );
		load_expiration_modal_scripts();
	}
}
add_action( 'wp_footer', __NAMESPACE__ . '\show_expiration_modal' );


/**
 * Enqueue and localize scripts for the expiration modal.
 *
 * Retrieves the current user ID, enqueues the modal script and localizes
 * the AJAX URL, a nonce scoped to the current user and the user ID.
 *
 * @return void
 */
function load_expiration_modal_scripts() {
	$plugin_url = plugin_dir_url( dirname( __FILE__, 3 ) );
	$user_id    = get_current_user_id();

	wp_enqueue_script(
		'expiration-modal-handler',
		$plugin_url . 'dist/js/expiration-modal.min.js',
		array( 'tm-base' ),
		'1.0.0',
		true
	);

	wp_localize_script(
		'expiration-modal-handler',
		'expirationModalData',
		array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'disable_expiration_modal_' . $user_id ),
			'userId'  => $user_id,
		)
	);
}


/**
 * Check if expiration modal is active for the current user
 *
 * Hierarchy: client-level > global-level
 * Empty values (new fields) are treated as true
 *
 * @param int $user_id The user ID. If 0, uses the current user.
 * @return bool
 */
function is_expiration_modal_active( int $user_id = 0 ) : bool {
	if ( ! $user_id ) {
		$user_id = get_current_user_id();
	}

	if ( ! $user_id ) {
		return false;
	}

	// Check user-level setting.
	$user_modal_active = get_field( 'expiration_modal_user_active', 'user_' . $user_id );

	if ( false === $user_modal_active ) {
		return false;
	}

	// Check client-level setting.
	$client_id = get_field( 'related_client', 'user_' . $user_id );

	if ( $client_id ) {
		$client_modal_active = get_field( 'expiration_modal_client_active', $client_id );

		if ( false === $client_modal_active ) {
			return false;
		}
	}

	// Check global-level setting.
	$global_modal_active = get_field( 'expiration_modal_global_active', 'option' );
	if ( false === $global_modal_active ) {
		return false;
	}

	return true;
}


/**
 * Handle AJAX request to disable expiration modal for user
 */
function handle_disable_expiration_modal() {
	$user_id = isset( $_POST['user_id'] ) ? intval( $_POST['user_id'] ) : 0;
	$nonce   = isset( $_POST['nonce'] ) ? sanitize_text_field( $_POST['nonce'] ) : '';

	if ( ! wp_verify_nonce( $nonce, 'disable_expiration_modal_' . $user_id ) ) {
		wp_send_json_error( 'Security verification failed' );
	}

	if ( get_current_user_id() !== $user_id ) {
		wp_send_json_error( 'You can only disable your own modal' );
	}

	$updated = update_field( 'expiration_modal_user_active', '0', 'user_' . $user_id );

	if ( false !== $updated ) {
		wp_send_json_success( 'Modal disabled successfully' );
	} else {
		wp_send_json_error( 'Failed to disable modal' );
	}
}
add_action( 'wp_ajax_disable_expiration_modal', __NAMESPACE__ . '\handle_disable_expiration_modal' );
add_action( 'wp_ajax_nopriv_disable_expiration_modal', __NAMESPACE__ . '\handle_disable_expiration_modal' );


/**
 * Reset expiration modal when client expiration date is changed - ONLY ON RENEWAL
 *
 * @param int $client_id The client ID.
 * @return void
 */
function on_client_expiration_date_change( $client_id ) {
	// Only process if it's a numeric client ID (not user).
	if ( ! is_numeric( $client_id ) || $client_id == 0 ) {
		return;
	}

	// Get the new expiration date from POST data.
	$new_expiration_date = null;
	if ( isset( $_POST['acf'] ) && is_array( $_POST['acf'] ) ) {
		foreach ( $_POST['acf'] as $key => $value ) {
			$field = get_field_object( $key );
			if ( 'expiration_date_plan' === $field && $field['name'] ) {
				$new_expiration_date = $value;
				break;
			}
		}
	}

	if ( $new_expiration_date ) {
		// Get OLD expiration date from transient (captured before save).
		$old_expiration_date = get_transient( 'old_client_expiration_' . $client_id );

		// Only reset modals if this is a RENEWAL.
		if ( is_renewal( $old_expiration_date, $new_expiration_date ) ) {
			// Reset modals for all users of this client who don't have personal expiration dates.
			$users = get_users(
				array(
					'meta_key'   => 'related_client',
					'meta_value' => $client_id,
					'fields'     => 'ID',
				)
			);

			foreach ( $users as $user_id ) {
				$user_expiration_date = get_field( 'user_expirate_date', 'user_' . $user_id );
				if ( empty( $user_expiration_date ) ) {
					reset_expiration_modal_for_user( $user_id );
				}
			}
		}

		// Clean up transient.
		delete_transient( 'old_client_expiration_' . $client_id );
	}
}
add_action( 'acf/save_post', __NAMESPACE__ . '\on_client_expiration_date_change', 99 );


/**
 * Reset expiration modal when user expiration date is changed - ONLY ON RENEWAL
 *
 * @param int $user_id The user ID.
 * @return void
 */
function on_user_expiration_date_change( $user_id ) {
	// Only process user saves.
	if ( is_string( $user_id ) && strpos( $user_id, 'user_' ) === 0 ) {
		$user_id = absint( str_replace( 'user_', '', $user_id ) );
	}

	if ( ! $user_id ) {
		return;
	}

	// Get the new expiration date from POST data.
	$new_expiration_date = null;
	if ( isset( $_POST['acf'] ) && is_array( $_POST['acf'] ) ) {
		foreach ( $_POST['acf'] as $key => $value ) {
			$field = get_field_object( $key );
			if ( 'user_expirate_date' === $field && $field['name'] ) {
				$new_expiration_date = $value;
				break;
			}
		}
	}

	if ( $new_expiration_date ) {
		// Get OLD expiration date from transient (captured before save).
		$old_expiration_date = get_transient( 'old_user_expiration_' . $user_id );

		// Only reset modal if this is a RENEWAL.
		if ( is_renewal( $old_expiration_date, $new_expiration_date ) ) {
			reset_expiration_modal_for_user( $user_id );
		}

		// Clean up transient.
		delete_transient( 'old_user_expiration_' . $user_id );
	}
}
add_action( 'acf/save_post', __NAMESPACE__ . '\on_user_expiration_date_change', 99 );


/**
 * Store old client expiration date before save
 *
 * @param int $client_id The client ID.
 * @return void
 */
function store_old_client_expiration_date( $client_id ) {
	if ( ! is_numeric( $client_id ) || $client_id == 0 ) {
		return;
	}

	// Only process client post type.
	$post_type = get_post_type( $client_id );
	if ( 'client' !== $post_type ) {
		return;
	}

	$old_expiration_date = get_post_meta( $client_id, 'expiration_date_plan', true );
	set_transient( 'old_client_expiration_' . $client_id, $old_expiration_date, 60 );
}
add_action( 'acf/save_post', __NAMESPACE__ . '\store_old_client_expiration_date', 5 );


/**
 * Store old user expiration date before save
 *
 * @param int $user_id The user ID.
 * @return void
 */
function store_old_user_expiration_date( $user_id ) {
	// Only process user saves.
	if ( is_string( $user_id ) && strpos( $user_id, 'user_' ) === 0 ) {
		$user_id = absint( str_replace( 'user_', '', $user_id ) );
	}

	if ( ! $user_id ) {
		return;
	}

	$old_expiration_date = get_user_meta( $user_id, 'user_expirate_date', true );
	set_transient( 'old_user_expiration_' . $user_id, $old_expiration_date, 60 );
}
add_action( 'acf/save_post', __NAMESPACE__ . '\store_old_user_expiration_date', 5 );


/**
 * Check if the date change represents a renewal (extension to future)
 *
 * @param string $old_date Old expiration date (Y-m-d).
 * @param string $new_date New expiration date (Y-m-d).
 * @return bool
 */
function is_renewal( $old_date, $new_date ): bool {
	// If no old date exists, consider it a new subscription (renewal).
	if ( empty( $old_date ) ) {
		return true;
	}

	// If dates are the same, it's not a renewal.
	if ( $old_date === $new_date ) {
		return false;
	}

	// Convert to timestamps for comparison.
	$old_timestamp = strtotime( $old_date );
	$new_timestamp = strtotime( $new_date );

	// It's a renewal if the new date is in the future compared to the old date.
	return $new_timestamp > $old_timestamp;
}

/**
 * Reset expiration modal for a user (set to active)
 *
 * @param int $user_id The user ID.
 * @return bool
 */
function reset_expiration_modal_for_user( int $user_id ): bool {
	if ( ! $user_id ) {
		return false;
	}

	$current_value = get_field( 'expiration_modal_user_active', 'user_' . $user_id );

	// Only update if it's not already active (0, false, empty, etc.).
	if ( ! $current_value || '0' === $current_value || '' === $current_value ) {
		return update_field( 'expiration_modal_user_active', '1', 'user_' . $user_id );
	} else {
		return true; // Consider it success since it's already active.
	}
}
