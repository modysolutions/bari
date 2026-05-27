<?php
/**
 * Omitsis Cookie
 *
 * @package           Omitsis_Cookie
 *
 * @wordpress-plugin
 * Plugin Name:       Omitsis_Cookie
 * Version:           0.1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Description:       Sets a cookie on the user's browser when they request a password reset. This cookie is used to deny password reset requests from bots.
 */

namespace Omitsis_Cookie;

defined( 'ABSPATH' ) || exit;

const COOKIE_NAME = 'ga_omsess';

function set_cookie() {
	if ( isset( $_COOKIE[ COOKIE_NAME ] ) ) {
		return;
	}
	$next_month = time() + (86400 * 30);
	$value = md5( COOKIE_NAME . time() );
	setcookie( COOKIE_NAME, $value, $next_month, '/' );
}

function maybe_deny_password_request() {
	if ( ! isset( $_COOKIE[ COOKIE_NAME ] ) ) {
		return;
	}
	status_header( 403 );
	exit();
}

add_action( 'wp_ajax_request_password', __NAMESPACE__ . '\set_cookie', -10 );
add_action( 'wp_ajax_nopriv_request_password', __NAMESPACE__ . '\set_cookie', -10 );
add_action( 'wp_ajax_request_password', __NAMESPACE__ . '\maybe_deny_password_request', -1 );
add_action( 'wp_ajax_nopriv_request_password', __NAMESPACE__ . '\maybe_deny_password_request', -1 );
