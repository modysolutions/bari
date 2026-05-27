<?php
/**
 * Global functions for Tamarind websites
 *
 * @package Tamarind_Base
 */

namespace tamarind_base;

add_filter( 'wp_new_user_notification_email_admin', '__return_false' );

/**
 * Sends a payload to the Zoho API based on the given configuration.
 *
 * @param array $config Configuration for the API request. Should include the following keys:
 *                      'function' (string): The Zoho API function to execute.
 *                      'payload' (string): The payload to send to the Zoho API.
 *                            Maybe an array when sending 'Content-Type: multipart/form-data'
 *                            Or a json_encoded array when default or 'Content-Type: application/json'
 *                      Optional keys such as 'CURLOPT_HTTPHEADER' can be provided for custom headers.
 *
 * @return bool True if the payload is sent successfully; false on failure.
 */
function send_payload_to_zoho( array $config = array() ) : bool {
	$base_url = get_field( 'field_tamarind_forms_field_zoho_api_url', 'option' );
	if ( empty( $base_url ) ) {
		error_log( 'Zoho API URL is not set.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		return false;
	}
	$api_key = get_field( 'field_tamarind_forms_field_zoho_api_key', 'option' );
	$default_config = array(
		'CURLOPT_HTTPHEADER' => array( 'Content-Type: application/json' ),
	);
	$config = array_merge( $default_config, $config );
	extract( $config ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
	if ( ! isset( $function ) || ! isset( $payload ) ) {
		return false;
	}

	$base_url .= "/{$function}/actions/execute?";
	$base_url .= http_build_query(
		array(
			'auth_type' => 'apikey',
			'zapikey'   => $api_key,
		)
	);

	$ch = curl_init(); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_init

	curl_setopt( $ch, CURLOPT_URL, $base_url ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
	curl_setopt( $ch, CURLOPT_POST, true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
	curl_setopt( $ch, CURLOPT_HTTPHEADER, $CURLOPT_HTTPHEADER ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt, WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase
	curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_setopt

	$response = curl_exec( $ch ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_exec

	$success = true;
	if ( curl_errno( $ch ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_errno
		error_log( 'cURL error: ' . print_r( json_decode( curl_error( $ch ) ), 1 ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log, WordPress.PHP.DevelopmentFunctions.error_log_print_r, WordPress.WP.AlternativeFunctions.curl_curl_error
		$success = false;
	} else {
		error_log( 'Zoho API response: ' . print_r( json_decode( $response ), 1 ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log, WordPress.PHP.DevelopmentFunctions.error_log_print_r
	}
	curl_close( $ch ); // phpcs:ignore WordPress.WP.AlternativeFunctions.curl_curl_close
	return $success;
}
