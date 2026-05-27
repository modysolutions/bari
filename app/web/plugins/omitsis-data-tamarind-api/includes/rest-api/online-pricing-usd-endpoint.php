<?php
/**
 * Handle common rest api functions / Endpoint Countries
 *
 * @package Omitsis_Data_Tamarind_Api
 */

namespace omitsis\rest_api\online_pricing_usd;

defined( 'ABSPATH' ) || exit;

/**
 * The function retrieves data from an API endpoint based on the provided year and type parameters.
 *
 * @param request The `` parameter is an instance of the `\WP_REST_Request` class. It
 * represents the REST API request made by the client. It contains information about the request, such
 * as the HTTP method, request parameters, headers, and body. In this function, we are using it to
 * retrieve the
 *
 * @return the data obtained from the `get_data_endpoint` function.
 */
function get_data_api_endpoint ( \WP_REST_Request $request ) {
	$year = $request->get_param( 'year' );
	$type = 'Online Pricing';
	$country = $request->get_param( 'country' );
	$data = \omitsis\rest_api\feature_type\get_data_endpoint($year, $type, $country );
	return $data;
}
