<?php
/**
 * Provides an extra layer of authentication for REST API.
 *
 * @package tamarind_rest_api
 */

namespace tamarind_rest_api;

defined( 'ABSPATH' ) || exit;

add_filter( 'rest_authentication_errors', __NAMESPACE__ . '\restrict_rest_api_access', 99 );

function restrict_rest_api_access( \WP_Error|null|bool $result ) : \WP_Error|null|bool {
	$request = new \WP_REST_Request( $_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI'] );
	// @todo: create a settings page for whitelisting paths into the tamarind base settings menu.
	$allowed_paths = array(
		'/gf/v2/',
		'/wp-json/custom/v1/',
	);
	$is_allowed_path = array_filter( $allowed_paths, function ( $path ) use ( $request ) {
		return str_starts_with( $request->get_route(), $path );
	});
	if ( count( $is_allowed_path ) > 0 ) {
		return $result;
	}

	if ( is_wp_error( $result ) ) {
		return $result;
	}

	if ( is_user_logged_in() ) {
		return $result;
	}

	$make_api_key_header = sanitize_text_field( $_SERVER['HTTP_IWC_API_KEY'] ?? '' );

	if ( class_exists( '\Integromat\Api_Token' ) && ! empty( $make_api_key_header ) ) {
		if ( \Integromat\Api_Token::is_valid( $make_api_key_header ) ) {
			return $result;
		}
	}

	return new \WP_Error(
		'rest_api_access_not_allowed',
		__( 'REST API access is not allowed.', 'tamarind-base' ),
		array( 'status' => 401 )
	);
}
