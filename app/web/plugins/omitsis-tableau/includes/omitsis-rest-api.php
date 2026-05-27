<?php
/**
 * Handle common rest api functions
 *
 * @package Omitsis_Tableau
 */

namespace omitsis\tableau_rest_api;

defined( 'ABSPATH' ) || exit;

function get_token_with_jwt ( $jwt, $site ) {

	// echo '<br><b>JWT:</b> <br>' . $jwt;
	// echo '<br><br><b>SITE:</b><br>' . $site;
	// echo '<br>';

	$url = "https://prod-uk-a.online.tableau.com/api/3.21/auth/signin";

	$wp_request_headers = array(
		'Content-Type' => 'application/json',
		'Accept' => 'application/xml',
		'Accept-Encoding' => 'gzip, deflate, br',
		'Connection' => 'keep-alive',
	);

	$wp_json_body = json_encode(
		array(
			'credentials' => array(
				'jwt' => $jwt,
				'site' => array(
					'contentUrl' => $site,
				),
			),
		)
	);

	$wp_request_args = array(
		'headers' => $wp_request_headers,
		'body' => $wp_json_body,
	);
	$response = wp_remote_post($url, $wp_request_args);

	$response_code = wp_remote_retrieve_response_code($response);

	if ( $response_code == 200 ) {
		$xml_response = wp_remote_retrieve_body($response);
		$xml = simplexml_load_string($xml_response); // Cargar la respuesta XML
		$json_response = json_encode($xml); // Convertir a JSON
		$response_body = json_decode($json_response, true); // Convertir a array

		$token = $response_body['credentials']['@attributes']['token'];
		// echo '<br><b>TOKEN:</b> <br>' . $token;
		// echo '<br><br>';
		// echo '<pre>';
		// print_r($response_body);
		// echo '</pre>';
	} else {
		return false;
	}

	return $token;
}

function get_embed_html_code ( $jwt, $view_url, $width = '1920px', $height = '963px' ) {

	if ( empty( $view_url ) ) {
		return false;
	}

	$embed_script = "<script type='module' src='https://prod-uk-a.online.tableau.com/javascripts/api/tableau.embedding.3.latest.min.js'></script>";

	$embed_url = 'https://prod-uk-a.online.tableau.com/t/tamarindintelligencetableau/views/' . $view_url;
	$embed_tableau = "<tableau-viz id='tableau-viz' src='" . $embed_url. "' width='" . $width . "' height='" . $height . "' toolbar='hidden' token='" . $jwt . "' style='margin-bottom: 28px; border-bottom: 2px solid #dedede;'></tableau-viz>";
	$embed_html_code = $embed_script . $embed_tableau;

	return $embed_html_code;
}

function get_id_view ( $token, $project, $dashboard ) {

	// if ( empty( $view ) ) {
	// 	return false;
	// }

	$filter_params = 'projectName:eq:' . str_replace(' ', '+', $project) . ',name:eq:' . str_replace(' ', '+', $dashboard);
	$url = "https://prod-uk-a.online.tableau.com/api/3.21/sites/a2ad5302-9197-4099-96d3-70c7a83247b6/views" . '?filter=' . $filter_params;

	$wp_request_headers = array(
		'Accept' => '*/*',
		'Accept-Encoding' => 'gzip, deflate, br',
		'Connection' => 'keep-alive',
		'X-Tableau-Auth' => $token,
	);

	$wp_request_args = array(
		'headers' => $wp_request_headers,
	);

	$response = wp_remote_get($url, $wp_request_args);
	$response_code = wp_remote_retrieve_response_code($response);

	if ( $response_code == 200 ) {
		$xml_response = wp_remote_retrieve_body($response);
		$xml = simplexml_load_string($xml_response); // Cargar la respuesta XML
		$json_response = json_encode($xml); // Convertir a JSON
		$response_body = json_decode($json_response, true); // Convertir a array

		$views = $response_body['views'];
		$view_url = '';
		// cogerá el último ID si se trata de varios casos / aunque no debería ser así nunca
		foreach ( $views as $viewData ) {
			// if ( $viewData['@attributes']['viewUrlName'] == str_replace( '.', '_', str_replace( ' ', '', $dashboard )) ) {
			if ( str_replace( '.', '_', str_replace( ' ', '',$viewData['@attributes']['name'])) == str_replace( '.', '_', str_replace( ' ', '', $dashboard )) ) {
				// $view_url = str_replace( ' ', '', $project) . '/' . $viewData['@attributes']['viewUrlName'];
				$view_url = str_replace( '/sheets', '', $viewData['@attributes']['contentUrl']);
			}
		}
	} else {
		return false;
	}

	return $view_url;
}

function create_tableau_shortcode ( $atts, $content, $tag ) {


	$params = shortcode_atts(
		array(
			'project' => '',
			'dashboard' => '',
			'width' => '1920px',
			'height' => '963px',
		),
		$atts,
		'tableau'
	);

	// if (empty( $params['view'] )) {
	// 	return 'Por favor, especifica una vista';
	// }

	$project = $atts['project'];
	$dashboard = $atts['dashboard'];
	$width = $atts['width'];
	$height = $atts['height'];

	$jwt = TABLEAU_JWT;
	$site = TABLEAU_SITE;

	$token = get_token_with_jwt($jwt, $site);

	$view_url = get_id_view($token, $project, $dashboard);
	$embed_html_code = get_embed_html_code($jwt, $view_url, $width, $height);

	return $embed_html_code;
}

function shortcodes_init(){
	add_shortcode( 'my-tableau', __NAMESPACE__ . '\create_tableau_shortcode' );
}

function init_tableau_api () {
	add_action('init', __NAMESPACE__ . '\shortcodes_init');
}
