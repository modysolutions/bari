<?php
/**
 * Plugin name: Tamarind Rest API
 * Description: Tamarind Intelligence Rest API additions.
 * Author: Tamarind Intelligence.
 * Author URI: https://tamarindintelligence.com
 * Text Domain: tamarind-rest-api
 * Domain Path: /languages
 * version: 0.0.1
 *
 * @package tamarind_rest_api
 */

namespace tamarind_rest_api;

defined( 'ABSPATH' ) || exit;

const TR_API_VERSION = '0.0.1';
define('TR_API_DIR', plugin_dir_path( __FILE__ ));
define('TR_API_URL', plugin_dir_url( __FILE__ ));

$includes = glob( TR_API_DIR . 'includes/*.php' );
if(count($includes) > 0) {
	foreach($includes as $include) {
		require_once $include;
	}
}

define('TR_API_THEME_VERSION', get_theme_version());
