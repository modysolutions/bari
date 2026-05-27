<?php
/**
 * Omitsis Log Ajax Requests
 *
 * @package           Omitsis_Log_Ajax_Requests
 *
 * @wordpress-plugin
 * Plugin Name:       Omitsis_Log_Ajax_Requests
 * Version:           0.1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Description:
 */

namespace Omitsis\LogAjaxRequests;

add_action( 'admin_init', __NAMESPACE__ . '\maybe_log_request', 10, 2 );

function maybe_log_request() {
	$url = get_actual_url();
	// Do not log if not ajax request.
	if ( false === strpos( $url, 'wp-admin/admin-ajax.php' ) ) {
		return;
	}

	$max_length  = 200;
	$filename    = get_log_filename();
	$post_data   = file_get_contents( 'php://input' );
	$cookie_data = get_cookie_data();

	// Shorten $post_data and $cookie_data if too long.
	$post_data   = strlen( $post_data ) > $max_length ? substr( $post_data, 0, $max_length ) . '...' : $post_data;
	$cookie_data = strlen( $cookie_data ) > $max_length ? substr( $cookie_data, 0, $max_length ) . '...' : $cookie_data;

	$referer     = $_SERVER['HTTP_REFERER'];
	$ip          = $_SERVER['REMOTE_ADDR'];
	$message     = $url . ' - ' . date( 'm/d/Y h:i:s a', time() ) . ' - ' . $post_data . ' - ' . $cookie_data . ' - ' . $referer . ' - ' . $ip . "\n";

	file_put_contents( $filename, $message, FILE_APPEND );
}


function get_actual_url() {
	$actual_link = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http' ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	return $actual_link;
}

function get_log_filename() {
	$dir = WP_CONTENT_DIR . '/omitsis-logs/';
	wp_mkdir_p( $dir );
	$filename = date( 'Y-m-d' ) . '.log';
	return $dir . $filename;
}

function get_cookie_data() {
	$cookie_data = '';
	if ( isset( $_COOKIE ) ) {
		$cookie_data = join(
			'&',
			array_map(
				function ( $v, $k ) {
					return sprintf( '%s=%s', $k, $v );
				},
				$_COOKIE,
				array_keys( $_COOKIE )
			)
		);
	}
	return $cookie_data;
}
