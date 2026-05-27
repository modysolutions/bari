<?php
/**
 * Handle common rest api functions / Endpoint Countries
 *
 * @package Omitsis_Data_Tamarind_Api
 */

namespace omitsis\rest_api\countries;

defined( 'ABSPATH' ) || exit;

global $wpdb;
define( 'PREFIX_TABLE_PLUGIN', $wpdb->prefix . 'omitsis_data_api_' );

/**
 * The function "get_data_countries" retrieves data on countries' population and number of smokers for
 * a given year from a WordPress database.
 *
 * @param year The year parameter is used to specify the year for which you want to retrieve data. If
 * no year is provided, it will default to the current year.
 *
 * @return the result of the SQL query, which is an array of objects containing the country ID, name,
 * population, and number of smokers for a given year.
 */
function get_data_countries () {

	global $wpdb;
	$table_name_country	   = PREFIX_TABLE_PLUGIN . 'country';

	$sql = "SELECT * FROM $table_name_country";
	$result = $wpdb->get_results( $sql );
	return $result;
}

/**
 * The function "get_data_api_countries" retrieves data for a specific year and merges it with the year
 * information.
 *
 * @param request The parameter `` is an instance of the `WP_REST_Request` class. It represents
 * the HTTP request made to the API endpoint. It contains information such as the request method,
 * headers, query parameters, and request body. In this function, it is used to retrieve the value of
 * the `
 *
 * @return the data retrieved from the `get_data_countries` function, with each item in the data array
 * having a new property `year` added to it.
 */
function get_data_api_countries ( \WP_REST_Request $request ) {

	$data = get_data_countries();
	return $data;
}
