<?php
/**
 * Omitsis Location Detector
 *
 * @package           OmitsisLocationDetector
 * @author            Omitsis
 * @copyright         2023 Omitsis
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Omitsis Location Detector
 * Plugin URI:
 * Description:       Implements utilities to detect browser language and IP based location. This product includes IP2Location LITE data available from <a href="https://www.ip2location.com">https://www.ip2location.com</a>.
 * Version:           0.1.0
 * Requires at least: 6.1
 * Requires PHP:      7.4
 * Author:            Omitsis
 * Author URI:        https://www.omitsis.com
 * Text Domain:       om-location-detector
 * License:           2023 Omitsis
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Update URI:
 */

/* defined( 'ABSPATH' ) || exit; */

require_once __DIR__ . '/includes/omitsis-language-detector.php';
require_once __DIR__ . '/includes/omitsis-location-detector.php';
require_once __DIR__ . '/includes/omitsis-acfs-detector.php';
require_once __DIR__ . '/includes/omitsis-content-detector.php';

require __DIR__ . '/vendor/autoload.php';

use omitsis\location_detector as loc;
use omitsis\language_detector as lang;
use omitsis\acfs_detector as acfs;
use omitsis\content_detector as china_content;

/**
 * Check if the user agent reports a given language among the accepted ones.
 *
 * @param string $lang_code Language code to check.
 * @param bool   $strict    Whether to check for exact match or just the first part.
 * @param string $http_accept_language Optional. The HTTP Accept-Language header to check. Defaults to the current user agent.
 *
 * @return bool
 */
function om_location_supports_language( $lang_code, $strict = false, $http_accept_language = null ) {
	return lang\browser_supports_language( $lang_code, $strict, $http_accept_language );
}

/**
 * Check if the user IP belongs to a given country.
 *
 * @param string $country_code Country code to check.
 *
 * @return bool
 */
function om_location_ip_is_from( $country_code ) {
	return loc\ip_is_from( $country_code );
}

/**
 * Get the country code from the user IP.
 *
 * @return string
 */
function om_location_get_country_from_ip() {
	return loc\get_country_from_ip( loc\guess_ip() );
}

/**
 * Register ACF Fields and Settings.
 *
 * @return void
 */
add_action('init', 'omitsis\acfs_detector\init_acfs');
add_action('acf/include_fields', 'omitsis\acfs_detector\china_option_page_fields' );

/**
 * Register the API endpoint.
 *
 * @return void
 */
add_action('rest_api_init', function () {
	// Endpoint to get the support country by IP or language navigator.
	register_rest_route(
		'tamarind/v1',
		'/support-country',
		array(
			'methods'  => 'GET',
			'callback' => __NAMESPACE__ . 'support_country',
			'args' => array(
				'code' => array(
					'validate_callback' => function($param, $request, $key) {
						return is_string($param);
					}
				),
			),
            'permission_callback' => '__return_true',
		)
	);
	// Endpoint to get Widget to China
	register_rest_route(
		'tamarind/v1',
		'/get-china-widget',
		array(
			'methods'  => 'GET',
			'callback' => __NAMESPACE__ . '\get_api_china_widget',
            'permission_callback' => '__return_true',
		)
	);
	// Endpoint to get Popup Video to China
	register_rest_route(
		'tamarind/v1',
		'/get-china-video',
		array(
			'methods'  => 'GET',
			'callback' => __NAMESPACE__ . 'get_api_china_video',
			'args' => array(
				'post_id' => array(
					'validate_callback' => function($param, $request, $key) {
						return is_numeric($param);
					}
				),
			),
            'permission_callback' => '__return_true',
		)
	);
	// Endpoint to get Popup Form to China
	register_rest_route(
		'tamarind/v1',
		'/get-china-form',
		array(
			'methods'  => 'GET',
			'callback' => __NAMESPACE__ . 'get_api_china_form',
			'args' => array(
				'post_id' => array(
					'validate_callback' => function($param, $request, $key) {
						return is_numeric($param);
					}
				),
			),
            'permission_callback' => '__return_true',
		)
	);
});

/**
 * Get the support_country by IP or language navigator served by the API.
 *
 * @return array Associative array with keys 'country-ip', 'lang-nav' and 'code'.
 */

function support_country ( WP_REST_Request $request ) {

	$code = $request['code'];

	$country_code = om_location_get_country_from_ip();
	// Cuando el navegador tiene un lenguaje ZH-xx es chino, para mirar el caso de la IP es el código CN
	$code_ip = (strpos(strtoupper($code), 'ZH') !== false) ? 'CN' : $code;
	$country_access = (strtoupper($country_code) === strtoupper($code_ip) ) ? true : false;

	$lang_code_access = om_location_supports_language(strtolower($code), true);

	// get cookie from wordpress
	if (isset($_COOKIE['wordpress_logged_in_'])) {
		$cookie = $_COOKIE['wordpress_logged_in_'];
	} else {
		$cookie = '';
	}
	
	if ( !empty($cookie) ) {
		$country_access = false;
		$lang_code_access = false;
	}
	
	$res = array(
		'code' => $code,
		'country_ip' => $country_access,
		'country_ip_code' => $country_code,
		'lang_nav' => $lang_code_access,
		'access' => ($country_access || $lang_code_access) ? true : false,
	);
	return $res;
}

function get_api_china_widget () {
	$china_widget = china_content\china_widget();
	$res = array(
		'china_widget' => $china_widget,
	);
	return $res;
}

function get_api_china_video ( WP_REST_Request $request ) {

	$post_id = $request['post_id'];
	$china_popup_video = china_content\china_popup_video( $post_id );
	$res = array(
		'china_video' => $china_popup_video,
	);
	return $res;
}

function get_api_china_form ( WP_REST_Request $request ) {

	$post_id = $request['post_id'];
	$china_popup_form = china_content\china_popup_form( $post_id );
	$china_popup_form_download = china_content\china_popup_form_download( $post_id );

	$res = array(
		'china_form' => $china_popup_form,
		'china_form_download' => $china_popup_form_download,
	);
	return $res;
}

function add_js_check_china () {
	wp_enqueue_script( 'check-china', plugin_dir_url( __FILE__ ) . 'js/check-china.js', array( 'jquery' ), '1.8', true );

	// obtén el ID del post global
    global $post;
    $post_id = null;
    if ($post) {
        $post_id = $post->ID;
    }

	// pasa el ID del post a tu script
    wp_localize_script('check-china', 'china_vars', array(
        'post_id' => $post_id
    ));
}
add_action( 'wp_enqueue_scripts', 'add_js_check_china' );

function add_css_china () {
	wp_enqueue_style( 'china-css', plugin_dir_url( __FILE__ ) . 'css/style.css', array(), '1.8', 'all' );
}
add_action( 'wp_enqueue_scripts', 'add_css_china' );

function set_china_dummy () {
	$dummy = china_content\china_dummy();
	return $dummy;
}
add_action('wp_footer', 'set_china_dummy');

// add_filter( 'rest_authentication_errors', function( $result ) {
//     // Si ya hay un error de WordPress, déjalo pasar
//     if ( true === $result || is_wp_error( $result ) ) {
//         return $result;
//     }

//     // Define endpoint
//     $my_endpoint = 'support-country';

//     // Comprueba si la solicitud actual es al endpoint
//     if ( strpos( $_SERVER['REQUEST_URI'], $my_endpoint ) !== false ) {
//         // Verifica si el usuario está logueado
//         if ( is_user_logged_in() ) {
//             // Si el usuario está logueado, devolvemos un error
//             return new WP_Error( 'rest_not_logged_in', 'You are not currently logged in.', array( 'status' => 401 ) );
//         }
//     }
//     // Devuelve el resultado original si no es una solicitud a tu endpoint
//     return $result;
// });
