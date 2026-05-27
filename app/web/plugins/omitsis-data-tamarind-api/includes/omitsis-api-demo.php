<?php
/**
 * Handle common rest api functions
 *
 * @package Omitsis_Data_Tamarind_Api
 */

namespace omitsis\rest_api_demo;

defined( 'ABSPATH' ) || exit;

function init_api_demo () {

	echo '<div class="wrap"><h1>API Demo</h1></div>';
	echo '<br><br>';

	$username = get_option('omitsis_data_api_settings_user_demo');
	$application_password = get_option('omitsis_data_api_settings_token_demo');

	$auth = base64_encode($username.':'.$application_password);
	$wp_request_headers = array(
		'Authorization' => 'Basic '.$auth,
		'Content-Type' => 'application/json'
	);

	// $username = 'artesans'; // site username
	// $application_password = 'wbhf qrcL k3Qt fNPb yj9B gX7t';

	// $params = array(
	// 	'year' => 2017,
	// 	'feature_type' => 'Market Sizes',
	// );
	// $url = 'http://ecigintelligence.com.local/wp-json/tamarind-data/v1/feature_type/';
	// $query_url = $url.(empty($params) ? '' : '?'.http_build_query($params));

	echo '<b>USERNAME:</b> <br>' . $username;
	echo '<br><br>';
	echo '<b>APPLICATION PASSWORD:</b> <br>' . $application_password;
	echo '<br><br>';
	echo '<b>AUTH:</b> <br>' . $auth;
	echo '<br><br><hr /><br>';

	$query_url = get_option('omitsis_data_api_settings_url');

	$request_args = array(
		'headers' => $wp_request_headers,
	);

	echo '<b>URL:</b> <br>' . $query_url;
	echo '<br><br>';

	// Realiza la solicitud GET
	$response = wp_remote_get($query_url, $request_args);

	// Obtiene el código de estado HTTP de la respuesta
	$status_code = wp_remote_retrieve_response_code($response);

	// Obtiene la respuesta como texto
	$result = wp_remote_retrieve_body($response);

	// Comprueba si la solicitud fue exitosa
	if (is_wp_error($response)) {
		// Hubo un error en la solicitud
		echo 'Error: ' . $response->get_error_message();
	} else {
		// La solicitud fue exitosa, $result contiene la respuesta
		echo '<b>SOLICITUD OK.</b><br> Código de estado HTTP: ' . $status_code;
		echo '<br><br>';
		echo '<b>RESPUESTA:</b><br> ' . $result;

		// echo '<br><br><hr /><br>';
		// echo '<b>RESPUESTA DECODIFICADA:</b><br> ';
		// $result_decoded = json_decode($result);
		// echo '<pre>';
		// print_r($result_decoded);
		// echo '</pre>';
	}

}
