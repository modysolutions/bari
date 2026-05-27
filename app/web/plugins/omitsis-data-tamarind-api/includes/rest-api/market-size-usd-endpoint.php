<?php
/**
 * Handle common rest api functions / Endpoint market_size_usd
 *
 * @package Omitsis_Data_Tamarind_Api
 */

namespace omitsis\rest_api\market_size_usd;

defined( 'ABSPATH' ) || exit;

/**
 * The function retrieves data from a REST API endpoint based on the year, type, and country
 * parameters.
 *
 * @param request The `` parameter is an instance of the `\WP_REST_Request` class, which
 * represents the REST API request being made. It contains information about the request, such as the
 * HTTP method, headers, and query parameters.
 *
 * @return the data obtained from the \omitsis\rest_api\feature_type\get_data_endpoint() function.
 */
function get_data_api_endpoint ( \WP_REST_Request $request ) {
	$year = $request->get_param( 'year' );
	$type = 'Market Sizes';
	$country = $request->get_param( 'country' );
	$data = \omitsis\rest_api\feature_type\get_data_endpoint($year, $type, $country );
	return $data;
}
