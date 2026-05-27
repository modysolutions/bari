<?php
/**
 * Handle common rest api functions / Endpoint Countries
 *
 * @package Omitsis_Data_Tamarind_Api
 */

namespace omitsis\rest_api\feature_type;

defined( 'ABSPATH' ) || exit;

global $wpdb;
define( 'PREFIX_TABLE_PLUGIN_FEAT', $wpdb->prefix . 'omitsis_data_api_' );

function omitsis_cache_name ( $year, $type, $country ) {
	global $wpdb;

	$conditions = array();
	if ( !empty( $type ) ) {
		$conditions[] = $wpdb->prepare( '%s|feature', $type );
	}

	if ( !empty( $year ) ) {
		$conditions[] = $wpdb->prepare( '%s|year', $year );
	}

	if ( !empty( $country ) ) {
		$conditions[] = $wpdb->prepare( '%s|country', $country );
	}

	if ( empty( $conditions ) ) {
		return false;
	}
	return 'omitsis-data-api:' . implode( ';', $conditions );
}

function omitsis_transcient_get ( $key ) {
	return get_transient( $key );
}

function omitsis_transient_set ( $key, $data, $expire = 0 ) {
	$user_id = get_current_user_id();
	// if ( user_can( $user_id, 'administrator' ) ) {
	// 	echo "This is not cached. Let's proceed to " . $expire . " minuts.<br><br>";
	// }
	set_transient( $key, $data, $expire * MINUTE_IN_SECONDS );

	// $option_name = 'omitsis_data_api_cache';
	// $option_value = get_option( $option_name );
	// if ( false === $option_value ) {
	// 	$option_value[] = $key;
	// } else {
	// 	if ( !in_array( $key, $option_value ) ) {
	// 		$option_value[] = $key;
	// 	}
	// }
	// update_option( $option_name, $option_value );

	return true;
}


function get_data_endpoint ( $year, $type, $country ) {

	global $wpdb;

	$cache_name = omitsis_cache_name( $year, $type, $country );
	$ordered_data = omitsis_transcient_get ( $cache_name );

	$user_id = get_current_user_id();
	// if ( user_can( $user_id, 'administrator' ) ) {
	// 	echo 'Cache name: ' . $cache_name . '<br>';
	// }

	if ( false !== $ordered_data ) {
		// if ( user_can( $user_id, 'administrator' ) ) {
		// 	echo 'This is cached.<br><br>';
		// }
		return $ordered_data;
	}

	$conditions = array();

	if ( !empty( $type ) ) {
		$conditions[] = $wpdb->prepare( 'features.feature_type = %s', $type );
	}

	if ( !empty( $year ) ) {
		$conditions[] = $wpdb->prepare( 'meta_value.year = %d', $year );
	}

	if ( !empty( $country ) ) {
		$conditions[] = $wpdb->prepare( 'country.name = %s', $country );
	}

	$sql_where = '';
	if ( !empty( $conditions ) ) {
		$sql_where = ' WHERE ' . implode( ' AND ', $conditions );
	}

	$sql = "SELECT 	meta_value.country_id,
					country.name as country,
					country.code,
					country.code_char,
					region.name as region,
					meta_value.year,
					meta_value.value,
					features.slug_feature_name,
					features.feature_name,
					features.feature_order,
					features.feature_type
			FROM ". PREFIX_TABLE_PLUGIN_FEAT . "meta_value meta_value
			JOIN ". PREFIX_TABLE_PLUGIN_FEAT . "features features ON meta_value.slug_feature_name = features.slug_feature_name
			JOIN ". PREFIX_TABLE_PLUGIN_FEAT . "country country ON country.id = meta_value.country_id
			JOIN ". PREFIX_TABLE_PLUGIN_FEAT . "region region ON region.id = country.region_id
			$sql_where
			ORDER BY meta_value.country_id ASC, features.feature_order ASC";

	// echo '<pre>';
	// print_r( $sql );
	// echo '</pre>';
	$data = $wpdb->get_results( $sql );

	$ordered_data = [];
	foreach ($data as $item) {
		// $country_id = $item->country_id;
		$country = $item->country;
		$code = $item->code;
		$code_char = $item->code_char;
		$region = $item->region;
		$feature_order = $item->feature_order;
		$year_value = $item->year;
		$slug_feature_name = $item->slug_feature_name;
		$feature_name = $item->feature_name;
		$value = $item->value;
		$feature_type = $item->feature_type;
		$feature_type_normalize = strtolower( str_replace( ' ', '-', $feature_type ) );

		if (!isset($ordered_data[$country])) {
			$ordered_data[$country] = [];

			$ordered_data[$country]['country_data']['country_name'] = $country;
			$ordered_data[$country]['country_data']['country_code'] = $code;
			$ordered_data[$country]['country_data']['code_char'] = $code_char;
			$ordered_data[$country]['country_data']['region'] = $region;
		}

		$ordered_data[$country]['features'][$feature_type_normalize]['feature_type_group_name'] = $feature_type;
		$ordered_data[$country]['features'][$feature_type_normalize]['feature_type_group_name_slug'] = $feature_type_normalize;

		if (!isset($ordered_data[$country][$feature_type_normalize]['values'][$feature_order]['slug_feature_name'])) {
			$ordered_data[$country]['features'][$feature_type_normalize]['values'][$feature_order]['feature_name'] = $feature_name;
			$ordered_data[$country]['features'][$feature_type_normalize]['values'][$feature_order]['slug_feature_name'] = $slug_feature_name;
		}
		$ordered_data[$country]['features'][$feature_type_normalize]['values'][$feature_order][$year_value] = $value;
	}

	// convert $ordered_data to array
	$ordered_data = array_values($ordered_data);

	// convert all feature_type_group_name_slug to array
	foreach ($ordered_data as $key => $value) {

		foreach ($value as $key2 => $value2) {
			if ( $key2 == 'features' ) {
				$contador = 0;
				foreach ($value2 as $key3 => $value3) {
					$ordered_data[$key][$key2][$contador] = $value3;
					unset($ordered_data[$key][$key2][$key3]);
					$contador++;
				}
			}
		}
	}

	omitsis_transient_set( $cache_name, $ordered_data, 5 );
	return $ordered_data;
}


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
	$type = $request->get_param( 'feature_type' );
	$country = $request->get_param( 'country' );
	$data = get_data_endpoint($year, $type, $country );

	/* `return rest_ensure_response(  );` is returning a REST API response with the data obtained
	from the `get_data_endpoint` function. The `rest_ensure_response` function ensures that the data is
	formatted correctly for the REST API response. */
	return rest_ensure_response( $data );
	// return new \WP_REST_Response( $data, 200 );
}
