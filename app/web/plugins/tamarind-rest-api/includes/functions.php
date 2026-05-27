<?php
/**
 * Functions for Tamarind Rest API Plugin.
 *
 * @package tamarind_rest_api;
 */

namespace tamarind_rest_api;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * @todo move this function to tamarind-base to use the theme version as the global hash cache.
 *
 * @return bool|array|string
 */
function get_theme_version(): bool|array|string {
	$theme = wp_get_theme();
	
	return $theme->get( 'Version' );
}
