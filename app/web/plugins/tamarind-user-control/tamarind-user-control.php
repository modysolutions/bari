<?php
/**
 * Tamarind User Control
 *
 * @package           TamarindUserControl
 * @author            Omitsis
 * @copyright         2023 Omitsis
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Tamarind User Control
 * Description:
 * Version:           1.0.0
 * Requires at least: 6.0.3
 * Requires PHP:      7.4
 * Author:            Omitsis
 * Author URI:
 * Text Domain:       tamarind-user-control
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

namespace tamarind\user_control;

defined( 'ABSPATH' ) || exit;

// Deletes the user when not allowed.
add_action( 'user_register', __NAMESPACE__ . '\\maybe_delete_user', 30, 1);

if ( class_exists( 'GFAPI' ) ) {
	add_action( 'gform_pre_submission', __NAMESPACE__ . '\check_register_key_before_submission', 20, 1 );
}

/**
 * Check if the user is allowed to register before submission.
 * 
 * @param array $form Form.
 * @return void
 */
function check_register_key_before_submission( $form ) {
	if ( ( \tamarind_forms\display_form\get_type_form($form) == 'register-user' ) && ! is_allowed_user() ) {
		$submit_key = get_submit_key();
		error_log( '[Gravity Forms] Register blocked by invalid key. IP: ' . $_SERVER['REMOTE_ADDR'] . ' - SUBMIT KEY: ' . $submit_key );
		wp_die(
			__( 'Register not allowed. Your IP has been logged.', 'tamarind-user-control' ),
			__( 'Register blocked', 'tamarind-user-control' ),
			array( 'response' => 403 )
		);
	} 
	
	return;
}

/**
 * Deletes user if not allowed to register.
 *
 * @param int $user_id
 */
function maybe_delete_user( $user_id ) {
	if ( ! is_allowed_user() ) {
		$email = get_email( $user_id );
		$username = get_username( $user_id );
		require_once( ABSPATH . 'wp-admin/includes/user.php' );
		\wp_delete_user( $user_id );
		log_user_deletion( $email, $username, $user_id );
	} 
}

/**
 * Get the submit key from the form. False if not found.
 *
 * @return string
 */
function get_submit_key() {
	$register_key = '';
	if ( function_exists( 'get_field' ) ) {
		$register_key = get_field( 'register_key', 'option' );
	}

	return array_values(
		array_filter(
			$_POST, 
			function( $value ) use ( $register_key ) {
				// Avoid TypeError when non-string POST values are present.
				return is_string( $value ) && str_contains( $value, '?key=' . $register_key );
			}
		)
	)[0] ?? false;
}

/**
 * Get the email of a user.
 *
 * @param int $user_id
 * @return string
 */
function get_email ( $user_id ) {
	$user = get_user_by( 'id', $user_id );
	return $user->user_email;
}

/**
 * Get the username of a user.
 *
 * @param int $user_id
 * @return string
 */
function get_username ( $user_id ) {
	$user = get_user_by( 'id', $user_id );
	return $user->user_login;
}

/**
 * Checks if the user is allowed to register.
 *
 * @param int $user_id
 */
function is_allowed_user() {

	if ( is_user_logged_in() && current_user_can( 'create_users' ) ) {
		return true;
	}	

	// skip if submit_key is not informed.
	$submit_key = get_submit_key();
	if ( ! $submit_key ) {
		return false;
	}

	$site_domain = parse_url( get_site_url(), PHP_URL_HOST );
	$form_domain = parse_url( $submit_key, PHP_URL_HOST );
	if ( $site_domain !== $form_domain ) {
		return false;
	}
	return true;
}

/**
 * Logs user deletion to wp-content/tamarind-user-control/ISOdate.log
 */
function log_user_deletion( $email, $username, $user_id ) {
	$dir = WP_CONTENT_DIR . '/tamarind-user-control/';
	wp_mkdir_p( $dir );
	$filename = date( 'Y-m-d' ) . '.log';
	$full_path = $dir . $filename;
	$log = sprintf(
		'%1$s - Deleted user with mail %2$s (username: %3$s id: %5$s) and submit_key %4$s' . "\n",
		date( 'Y-m-d H:i:s' ),
		$email,
		$username,
		get_submit_key(),
		$user_id
	);

	$site_domain = parse_url( get_site_url(), PHP_URL_HOST );
	$form_domain = parse_url( get_submit_key(), PHP_URL_HOST );
	if ( $site_domain !== $form_domain ) {
		$log .= 'Domain mismatch: ' . $site_domain . ' / ' . $form_domain . "\n";
	}

	$stack = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 10 );
	$stack_lines = array_map( function( $trace ) {
		return isset( $trace['file'] ) ? "{$trace['file']}:{$trace['line']}:{$trace['function']}" : '';
	}, $stack );

	$log .= 'Stack trace:' . "\n";
	foreach ( $stack_lines as $line ) {
		$log .= $line . "\n";
	}
	$log .= "\n";
	$log .= '------------------------' . "\n";
	$log .= "\n";
	file_put_contents( $full_path, $log, FILE_APPEND );
}
