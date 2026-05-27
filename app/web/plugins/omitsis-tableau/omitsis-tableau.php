<?php
/**
 * Omitsis Base Plugin
 *
 * @package           Omitsis_Tableau
 * @author            Omitsis
 * @copyright         2024 Omitsis
 *
 * @wordpress-plugin
 * Plugin Name:       Omitsis Tableau
 * Author:            Omitsis
 * Author URI:        https://omitsis.com
 * Description:       Shortcode to include Tableau content.
 * Version:           1.0.1
 * Requires at least: 6.4
 * Requires PHP:      7.4
 * Text Domain:       omitsis-tableau
 */

namespace omitsis_tableau;

/**
 * Used to ensure that the plugin file is being accessed through the WordPress environment.
 */
defined( 'ABSPATH' ) || exit;

/**
 * Version number of the omitsis base plugin
*/
const VERSION = '1.0.1';

/**
 * The unique identifier of this plugin.
 */
const PLUGIN_NAME = 'omitsis-tableau';

/* The line `const PLUGIN_PATH = __DIR__;` is defining a constant variable named `PLUGIN_PATH` and
assigning it the value of the current directory path of the plugin file. The `__DIR__` constant
represents the directory path of the current file. So, `PLUGIN_PATH` will contain the absolute path
of the plugin directory. */
const PLUGIN_PATH = __DIR__;

require_once PLUGIN_PATH . '/includes/omitsis-plugin-actions.php';
require_once PLUGIN_PATH . '/admin/omitsis-admin.php';
require_once PLUGIN_PATH . '/includes/omitsis-acfs.php';
require_once PLUGIN_PATH . '/includes/omitsis-rest-api.php';

use omitsis\tableau_plugin_actions as plugin_actions;
use omitsis\tableau_admin as admin;
use omitsis\tableau_acfs as acfs;
use omitsis\tableau_rest_api as rest_api;

/* This file likely contains functions or definitions related to internationalization and localization. */
require_once PLUGIN_PATH . '/i18n/omitsis-languages.php';
use omitsis\tableau_i18n as i18n;

/* __FILE__ constant as an argument. This function is responsible for initializing the actions and hooks for the plugin. */
plugin_actions\init_actions( __FILE__ );

/* This function is responsible for initializing the actions and hooks related to the admin area of the plugin. */
admin\init_admin();

/* This function is responsible for initializing the actions and hooks related to the Advanced Custom Fields (ACF) plugin. */
acfs\init_acfs();

/* Initializing the actions and hooks related to internationalization and localization in the plugin. */
i18n\init_i18n();

require_once PLUGIN_PATH . '/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\JWK;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

function get_jwt () {

	$iss = '39807013-7d55-4ff2-b5a0-9d928bf18195';
	$kid = 'fff69b16-0a5e-4ec7-ba8c-7676283d52f5';
	$key = '4BAzgvbU9tRl4OY6n1BTShX5o5sVmz9ratslMEFkB4U=';

	$aud = 'tableau';
	$sub = 'ana@tamarindintelligence.com';
	$scp = ['tableau:views:embed', 'tableau:content:read'];

	$expire = 0;
	$jti = '';

	try {
		$uuid4 = Uuid::uuid4();
		$jti = $uuid4->toString();
		$date = new \DateTime('now', new \DateTimeZone('UTC'));
		$date->add(new \DateInterval('PT5M')); // añade 5 minutos
		$expire = $date->format('Y-m-d H:i:s.u');
		$expire = strtotime($expire);
	} catch (UnsatisfiedDependencyException $e) {
		error_log($e->getMessage());
	} catch ( \Exception $e ) {
	}

	$data = [
		'iss' => $iss,
		'exp' => $expire,
		'jti' => $jti,
		'aud' => $aud,
		'sub' => $sub,
		'scp' => $scp
	];

	$headers = [
		'kid' => $kid,
		'iss' => $iss
	];

	$jwt = JWT::encode($data, $key, 'HS256', null, $headers);

	return $jwt;
}

function get_site () {
	$site = 'tamarindintelligencetableau';
	return $site;
}

$jwt = get_jwt();
$site = get_site();

// set constants
define('TABLEAU_JWT', $jwt);
define('TABLEAU_SITE', $site);

/* This function is responsible for initializing the actions and hooks related to the REST API in the plugin. */
rest_api\init_tableau_api();
