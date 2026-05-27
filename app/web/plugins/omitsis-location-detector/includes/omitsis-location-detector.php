<?php
/**
 * Location Detector
 *
 * @package OmitsisLocationDetector
 */

namespace omitsis\location_detector;

const DATABASE_FILE = __DIR__ . '/../database/IP2LOCATION-LITE-DB1.BIN';

defined( 'ABSPATH' ) || exit;

/**
 * Check if the IP is in a set of countries.
 *
 * @param string|array $country_codes Country code or array of country codes.
 * @param string|bool  $ip            IP address to check. Defaults to current user.
 *
 * @return bool
 */
function ip_is_from( $country_codes, $ip = false ) {
	if ( ! $ip ) {
		$ip = guess_ip();
	}
	if ( ! $ip ) {
		return false;
	}
	$country_from_ip = get_country_from_ip( $ip );
	if ( ! $country_from_ip ) {
		return false;
	}
	if ( ! is_array( $country_codes ) ) {
		$country_codes = array( $country_codes );
	}
	// Ensure all country codes are lowercase.
	$country_codes = array_map( 'strtolower', $country_codes );
	return in_array( strtolower( $country_from_ip ), $country_codes, true );
}

/**
 * Guess the IP address of the current user.
 *
 * @return string|bool
 */
function guess_ip( $ip = false ) {
	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}

/**
 * Get the country code from an IP address.
 *
 * @param string $ip IP address.
 *
 * @return string The country code, if found. '-' if not found.
 */
function get_country_from_ip( $ip ) {
	$db     = new \IP2Location\Database( DATABASE_FILE, \IP2Location\Database::FILE_IO );
	$record = $db->lookup( $ip, \IP2Location\Database::COUNTRY_CODE );
	return $record;
}
