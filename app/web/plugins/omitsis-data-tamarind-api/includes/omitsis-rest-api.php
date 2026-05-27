<?php
/**
 * Handle common rest api functions
 *
 * @package Omitsis_Data_Tamarind_Api
 */

namespace omitsis\rest_api;

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/rest-api/countries-endpoint.php';
require_once __DIR__ . '/rest-api/feature-type-endpoint.php';

require_once __DIR__ . '/rest-api/vaping-populations-endpoint.php';

require_once __DIR__ . '/rest-api/consumption-endpoint.php';
require_once __DIR__ . '/rest-api/market-size-usd-endpoint.php';
require_once __DIR__ . '/rest-api/form-factors-endpoint.php';
require_once __DIR__ . '/rest-api/retail-channels-endpoint.php';
require_once __DIR__ . '/rest-api/online-pricing-local-currency-endpoint.php';
require_once __DIR__ . '/rest-api/online-pricing-usd-endpoint.php';
require_once __DIR__ . '/rest-api/affordability-endpoint.php';
require_once __DIR__ . '/rest-api/brands-and-associations-endpoint.php';
require_once __DIR__ . '/rest-api/traffic-endpoint.php';
require_once __DIR__ . '/rest-api/product-restrictions-endpoint.php';
require_once __DIR__ . '/rest-api/sales-channels-endpoint.php';
require_once __DIR__ . '/rest-api/taxation-endpoint.php';
require_once __DIR__ . '/rest-api/advertising-endpoint.php';

/**
 * The function checks if a user is logged in and authenticated using an application password in a
 * WordPress environment.
 *
 * @return a boolean value. It returns true if the user is authenticated and false if the user is not
 * authenticated.
 */
function rest_is_user_authenticated ( ) {

	if (is_user_logged_in() && function_exists('wp_authenticate_application_password')) {
		$user = wp_get_current_user();
		if ($user->ID > 0) {
			$authenticated = wp_authenticate_application_password($user, $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
			if (!is_wp_error($authenticated)) {
				return true;
			}
		}
	}
	return false;
}

function get_arguments_endpoint ( $type_arguments ) {
	$args = array();
	switch ( $type_arguments ) {
		case 'generic':
			$args['feature_type'] = array(
				'default' => '',
				'required' => false,
				'type' => 'string',
				'description' => esc_html__( 'This parameter allows users to focus on a particular group of market characteristics or features. When a feature group is defined, the endpoint will return data exclusively for that category. If "feature" is left unspecified, the endpoint will provide information on all available market features, ensuring a comprehensive view of diverse market attributes.' ),
			);
			$args['country'] = array(
				'default' => '',
				'required' => false,
				'type' => 'string',
				'description' => esc_html__( 'This parameter enables users to filter the results based on a specific country. When a country is specified, the endpoint will return data for that country alone. If no country is provided, the endpoint will retrieve data for all available countries, presenting a comprehensive view of market characteristics across the globe.' ),
			);
			$args['year'] = array(
				'default' => '',
				'required' => false,
				'type' => 'integer',
				'sanitize_callback' => 'absint',
				'validate_callback' => 'is_numeric',
				'description' => esc_html__( 'The "year" parameter allows users to narrow down their query to a specific year of interest. By specifying a particular year, the endpoint will return data relevant to that year. In the absence of a specified year, the endpoint will present results for all years available, offering a broader historical perspective of market features.' ),
			);
			break;
		case 'custom_feature':
			$args['country'] = array(
				'default' => '',
				'required' => false,
				'type' => 'string',
				'description' => esc_html__( 'This parameter enables users to filter the results based on a specific country. When a country is specified, the endpoint will return data for that country alone. If no country is provided, the endpoint will retrieve data for all available countries, presenting a comprehensive view of market characteristics across the globe.' ),
			);
			$args['year'] = array(
				'default' => '',
				'required' => false,
				'type' => 'integer',
				'sanitize_callback' => 'absint',
				'validate_callback' => 'is_numeric',
				'description' => esc_html__( 'The "year" parameter allows users to narrow down their query to a specific year of interest. By specifying a particular year, the endpoint will return data relevant to that year. In the absence of a specified year, the endpoint will present results for all years available, offering a broader historical perspective of market features.' ),
			);
			break;
		default:
	}
    return $args;
}

function init_rest_api () {

	/* IMPORTANT! This filter is used to enable the use of Application Passwords in WordPress. */
	add_filter( 'wp_is_application_passwords_available', '__return_true' );

	add_action('rest_api_init', function () {

		register_rest_route(
			'tamarind-data/v1',
			'/countries',
			array(
				'description' => esc_html__( 'Country Data Endpoint' ),
				'methods'  => 'GET',
				'callback' => '\omitsis\rest_api\countries\get_data_api_countries',
				'permission_callback' => __NAMESPACE__ . '\rest_is_user_authenticated',
			)
		);

		register_rest_route(
			'tamarind-data/v1',
			'/feature_type',
			array(
				'description' => esc_html__( 'The Market Feature Data Query Endpoint is a powerful tool that allows users to retrieve comprehensive market data from the Tamarind database. This endpoint is designed to provide valuable insights into various market characteristics, and it offers flexibility through three key parameters.' ),
				'methods'  => 'GET',
				'callback' => '\omitsis\rest_api\feature_type\get_data_api_endpoint',
				'permission_callback' => __NAMESPACE__ . '\rest_is_user_authenticated',
				'args' => get_arguments_endpoint( 'generic' ),
				'schema' => 'get_public_item_schema',
			),
		);

		register_rest_route(
			'tamarind-data/v1',
			'/vaping_populations',
			array(
				'description' => esc_html__( 'Vaping Populations Data Endpoint: This specialized tool within the Tamarind database exclusively delivers detailed insights into the "Vaping Populations" feature group. ' ),
				'methods'  => 'GET',
				'callback' => '\omitsis\rest_api\vaping_populations\get_data_api_endpoint',
				'permission_callback' => __NAMESPACE__ . '\rest_is_user_authenticated',
				'args' => get_arguments_endpoint( 'custom_feature' ),
				'schema' => 'get_public_item_schema',
			),
		);

		register_rest_route(
			'tamarind-data/v1',
			'/consumption',
			array(
				'description' => esc_html__( 'Consumption Data Endpoint: This specialized tool within the Tamarind database exclusively delivers detailed insights into the "Vaping Populations" feature group. ' ),
				'methods'  => 'GET',
				'callback' => '\omitsis\rest_api\consumption\get_data_api_endpoint',
				'permission_callback' => __NAMESPACE__ . '\rest_is_user_authenticated',
				'args' => get_arguments_endpoint( 'custom_feature' ),
				'schema' => 'get_public_item_schema',
			),
		);

		register_rest_route(
			'tamarind-data/v1',
			'/market_size_usd',
			array(
				'description' => esc_html__( 'Market Size Data Endpoint: This specialized tool within the Tamarind database exclusively delivers detailed insights into the "Vaping Populations" feature group. ' ),
				'methods'  => 'GET',
				'callback' => '\omitsis\rest_api\market_size_usd\get_data_api_endpoint',
				'permission_callback' => __NAMESPACE__ . '\rest_is_user_authenticated',
				'args' => get_arguments_endpoint( 'custom_feature' ),
				'schema' => 'get_public_item_schema',
			),
		);

		register_rest_route(
			'tamarind-data/v1',
			'/form_factors',
			array(
				'description' => esc_html__( 'Form Factors Data Endpoint: This specialized tool within the Tamarind database exclusively delivers detailed insights into the "Vaping Populations" feature group. ' ),
				'methods'  => 'GET',
				'callback' => '\omitsis\rest_api\form_factors\get_data_api_endpoint',
				'permission_callback' => __NAMESPACE__ . '\rest_is_user_authenticated',
				'args' => get_arguments_endpoint( 'custom_feature' ),
				'schema' => 'get_public_item_schema',
			),
		);

		register_rest_route(
			'tamarind-data/v1',
			'/retail_channels',
			array(
				'description' => esc_html__( 'Retail Channels Data Endpoint: This specialized tool within the Tamarind database exclusively delivers detailed insights into the "Vaping Populations" feature group. ' ),
				'methods'  => 'GET',
				'callback' => '\omitsis\rest_api\retail_channels\get_data_api_endpoint',
				'permission_callback' => __NAMESPACE__ . '\rest_is_user_authenticated',
				'args' => get_arguments_endpoint( 'custom_feature' ),
				'schema' => 'get_public_item_schema',
			),
		);

		register_rest_route(
			'tamarind-data/v1',
			'/online_pricing_local_currency',
			array(
				'description' => esc_html__( 'Online Pricing Local Currency Data Endpoint: This specialized tool within the Tamarind database exclusively delivers detailed insights into the "Vaping Populations" feature group. ' ),
				'methods'  => 'GET',
				'callback' => '\omitsis\rest_api\online_pricing_local_currency\get_data_api_endpoint',
				'permission_callback' => __NAMESPACE__ . '\rest_is_user_authenticated',
				'args' => get_arguments_endpoint( 'custom_feature' ),
				'schema' => 'get_public_item_schema',
			),
		);

		register_rest_route(
			'tamarind-data/v1',
			'/online_pricing_usd',
			array(
				'description' => esc_html__( 'Online Pricing USD Data Endpoint: This specialized tool within the Tamarind database exclusively delivers detailed insights into the "Vaping Populations" feature group. ' ),
				'methods'  => 'GET',
				'callback' => '\omitsis\rest_api\online_pricing_usd\get_data_api_endpoint',
				'permission_callback' => __NAMESPACE__ . '\rest_is_user_authenticated',
				'args' => get_arguments_endpoint( 'custom_feature' ),
				'schema' => 'get_public_item_schema',
			),
		);

		register_rest_route(
			'tamarind-data/v1',
			'/affordability',
			array(
				'description' => esc_html__( 'Affordability Data Endpoint: This specialized tool within the Tamarind database exclusively delivers detailed insights into the "Vaping Populations" feature group. ' ),
				'methods'  => 'GET',
				'callback' => '\omitsis\rest_api\affordability\get_data_api_endpoint',
				'permission_callback' => __NAMESPACE__ . '\rest_is_user_authenticated',
				'args' => get_arguments_endpoint( 'custom_feature' ),
				'schema' => 'get_public_item_schema',
			),
		);

		register_rest_route(
			'tamarind-data/v1',
			'/brands_and_associations',
			array(
				'description' => esc_html__( 'Brands and Associations Data Endpoint: This specialized tool within the Tamarind database exclusively delivers detailed insights into the "Vaping Populations" feature group. ' ),
				'methods'  => 'GET',
				'callback' => '\omitsis\rest_api\brands_and_associations\get_data_api_endpoint',
				'permission_callback' => __NAMESPACE__ . '\rest_is_user_authenticated',
				'args' => get_arguments_endpoint( 'custom_feature' ),
				'schema' => 'get_public_item_schema',
			),
		);

		register_rest_route(
			'tamarind-data/v1',
			'/traffic',
			array(
				'description' => esc_html__( 'Traffic Data Endpoint: This specialized tool within the Tamarind database exclusively delivers detailed insights into the "Vaping Populations" feature group. ' ),
				'methods'  => 'GET',
				'callback' => '\omitsis\rest_api\traffic\get_data_api_endpoint',
				'permission_callback' => __NAMESPACE__ . '\rest_is_user_authenticated',
				'args' => get_arguments_endpoint( 'custom_feature' ),
				'schema' => 'get_public_item_schema',
			),
		);

		register_rest_route(
			'tamarind-data/v1',
			'/product_restrictions',
			array(
				'description' => esc_html__( 'Product Restrictions Data Endpoint: This specialized tool within the Tamarind database exclusively delivers detailed insights into the "Vaping Populations" feature group. ' ),
				'methods'  => 'GET',
				'callback' => '\omitsis\rest_api\product_restrictions\get_data_api_endpoint',
				'permission_callback' => __NAMESPACE__ . '\rest_is_user_authenticated',
				'args' => get_arguments_endpoint( 'custom_feature' ),
				'schema' => 'get_public_item_schema',
			),
		);

		register_rest_route(
			'tamarind-data/v1',
			'/sales_channels',
			array(
				'description' => esc_html__( 'Sales Channels Data Endpoint: This specialized tool within the Tamarind database exclusively delivers detailed insights into the "Vaping Populations" feature group. ' ),
				'methods'  => 'GET',
				'callback' => '\omitsis\rest_api\sales_channels\get_data_api_endpoint',
				'permission_callback' => __NAMESPACE__ . '\rest_is_user_authenticated',
				'args' => get_arguments_endpoint( 'custom_feature' ),
				'schema' => 'get_public_item_schema',
			),
		);

		register_rest_route(
			'tamarind-data/v1',
			'/taxation',
			array(
				'description' => esc_html__( 'Taxation Data Endpoint: This specialized tool within the Tamarind database exclusively delivers detailed insights into the "Vaping Populations" feature group. ' ),
				'methods'  => 'GET',
				'callback' => '\omitsis\rest_api\taxation\get_data_api_endpoint',
				'permission_callback' => __NAMESPACE__ . '\rest_is_user_authenticated',
				'args' => get_arguments_endpoint( 'custom_feature' ),
				'schema' => 'get_public_item_schema',
			),
		);

		register_rest_route(
			'tamarind-data/v1',
			'/advertising',
			array(
				'description' => esc_html__( 'Advertising Data Endpoint: This specialized tool within the Tamarind database exclusively delivers detailed insights into the "Vaping Populations" feature group. ' ),
				'methods'  => 'GET',
				'callback' => '\omitsis\rest_api\advertising\get_data_api_endpoint',
				'permission_callback' => __NAMESPACE__ . '\rest_is_user_authenticated',
				'args' => get_arguments_endpoint( 'custom_feature' ),
				'schema' => 'get_public_item_schema',
			),
		);

	});
}
