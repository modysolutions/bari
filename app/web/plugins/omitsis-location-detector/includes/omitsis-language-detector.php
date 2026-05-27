<?php
/**
 * Language Detector
 *
 * @package OmitsisLocationDetector
 */

namespace omitsis\language_detector;

/* defined( 'ABSPATH' ) || exit; */


/**
 * Check it the language code is supported by the browser.
 *
 * @param string $lang_code ISO 639-1 language code.
 * @param bool   $strict If true, the language code must match the browser language code.
 * @param string $http_accept_language The HTTP_ACCEPT_LANGUAGE header. Defaults to null, useable for testing.
 * @return bool
 */
function browser_supports_language( $lang_code, $strict = false, $http_accept_language = null ) {
	$browser_langs = array_keys( browser_supported_languages( $http_accept_language ) );
	if ( ! $strict ) {
		$browser_langs = array_unique(
			array_map(
				function( $lang ) {
			 		return substr( $lang, 0, 2 );
				},
				$browser_langs
			)
		);
	}
	return in_array( $lang_code, $browser_langs, true );
}

/**
 * Get the supported languages from the browser.
 *
 * @param string $http_accept_language The HTTP_ACCEPT_LANGUAGE header. Defaults to $_SERVER['HTTP_ACCEPT_LANGUAGE'].
 *
 * @return array An array of language codes with their quality value.
 */
function browser_supported_languages( $http_accept_language = null ) {
	$http_accept_language ??= isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ? wp_unslash( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) : '';

	$browser_langs = explode( ',', $http_accept_language );
	$result        = array();
	foreach ( $browser_langs as $lang ) {
		$parts = explode( ';q=', $lang );
		$result[ $parts[0] ]  = isset( $parts[1] ) ? floatval( $parts[1] ) : 1.0;
	}
	return $result;
}
