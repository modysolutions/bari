<?php
/**
 * Handle common rest api functions / Endpoint Countries
 *
 * @package Omitsis_Data_Tamarind_Api
 */

namespace omitsis\rest_api\product_restrictions;

defined( 'ABSPATH' ) || exit;

/**
 * The function retrieves data from a REST API endpoint based on the year, type, and country
 * parameters.
 *
 * @param request The `` parameter is an instance of the `\WP_REST_Request` class, which is
 * used to handle REST API requests in WordPress. It contains information about the request, such as
 * the HTTP method, request parameters, headers, and more.
 *
 * @return the data obtained from the \omitsis\rest_api\feature_type\get_data_endpoint() function.
 */
function get_data_api_endpoint ( \WP_REST_Request $request ) {
	$year = $request->get_param( 'year' );
	$type = 'Product restrictions';
	$country = $request->get_param( 'country' );
	$data = \omitsis\rest_api\feature_type\get_data_endpoint($year, $type, $country );
	return $data;
}
