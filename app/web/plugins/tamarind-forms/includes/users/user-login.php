<?php
/**
 * User Login.
 *
 * @package Tamarind_Forms
 *
 * phpcs:disable WordPress.Files.FileName
 */

namespace tamarind_forms\users\login;

defined( 'ABSPATH' ) || exit;


add_filter( 'login_redirect', __NAMESPACE__ . '\custom_login_redirect_by_role', 10, 3 );
add_filter( 'wp_2fa_post_login_user_redirect', __NAMESPACE__ . '\custom_2fa_redirect', 10, 2 );
add_filter( 'authenticate', __NAMESPACE__ . '\capture_redirect_to_early', 1, 3 );


/**
 * Redirect users after standard login based on their role.
 *
 * @param string   $redirect_to The URL to redirect to.
 * @param string   $request     The original request URL.
 * @param \WP_User $user        The user object.
 *
 * @return string The resolved redirect URL.
 */
function custom_login_redirect_by_role( $redirect_to, $request, $user ) {
	if ( ! $user instanceof \WP_User ) {
		return $redirect_to;
	}

	error_log( 'login redirect_to: ' . $redirect_to );
	error_log( 'login request: ' . $request );

	$redirect_to = resolve_redirect_by_role( $redirect_to, $user );

	error_log( 'login final redirect_to: ' . $redirect_to );

	return $redirect_to;
}


/**
 * Redirect users after 2FA login based on their role.
 *
 * @param string   $redirect_to The URL to redirect to.
 * @param \WP_User $user        The user object.
 *
 * @return string The resolved redirect URL.
 */
function custom_2fa_redirect( $redirect_to, $user ) {
	if ( ! $user instanceof \WP_User ) {
		return $redirect_to;
	}

	if ( ! session_id() && ! headers_sent() ) {
		session_start();
	}

	if ( isset( $_SESSION['custom_redirect_to'] ) ) {
		$custom_redirect = esc_url_raw( $_SESSION['custom_redirect_to'] );
		if ( ! empty( $custom_redirect ) ) {
			$redirect_to = $custom_redirect;
		}
		unset( $_SESSION['custom_redirect_to'] );
	}

	$redirect_to = resolve_redirect_by_role( $redirect_to, $user );

	error_log( '2fa final redirect_to: ' . $redirect_to );

	return $redirect_to;
}


/**
 * Common logic for redirection based on user role.
 *
 * @param string   $redirect_to The URL to redirect to.
 * @param \WP_User $user        The user object.
 *
 * @return string The resolved redirect URL.
 */
function resolve_redirect_by_role( $redirect_to, \WP_User $user ) {
	$roles = (array) $user->roles;

	// Client.
	if ( in_array( 'client', $roles, true ) ) {
		$post_id = url_to_postid( $redirect_to );

		if ( $post_id && get_post_type( $post_id ) === 'post' ) {
			return get_permalink( $post_id );
		}

		return home_url();
	}

	// Customer.
	if ( in_array( 'customer', $roles, true ) ) {
		return get_permalink( wc_get_page_id( 'shop' ) );
	}

	// Subscriber.
	if ( in_array( 'subscriber', $roles, true ) ) {
		$my_subscription_url = get_permalink( get_page_by_path( 'subscriptions' ) );
		if ( ! empty( $my_subscription_url ) ) {
			return esc_url_raw( $my_subscription_url );
		}
	}

	return $redirect_to;
}


/**
 * Capture the redirect_to parameter from the login form.
 *
 * @param WP_User|WP_Error|null $user The user object or WP_Error if authentication failed.
 *
 * @return WP_User|WP_Error|null
 */
function capture_redirect_to_early( $user ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Using wp_login_form(), WP validates nonce internally
	if ( isset( $_POST['redirect_to'] ) && is_string( $_POST['redirect_to'] ) ) {
		$redirect = esc_url_raw( $_POST['redirect_to'] );

		// Only allow internal URLs for security.
		if ( strpos( $redirect, home_url() ) === 0 ) {
			if ( ! session_id() && ! headers_sent() ) {
				session_start();
			}
			$_SESSION['custom_redirect_to'] = $redirect;

			error_log( 'authenticate captured redirect_to: ' . $redirect );
		}
	}
	return $user;
}
