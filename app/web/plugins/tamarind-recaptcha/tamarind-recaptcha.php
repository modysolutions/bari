<?php
/**
 * Tamarind Recaptcha
 *
 * @package           TamarindRecaptcha
 * @author            Omitsis
 * @copyright         2023 Omitsis
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Tamarind Recaptcha
 * Description:       Outputs the Google Recaptcha v3 script on wp_head
 * Version:           0.1.0
 * Requires at least: 6.0.3
 * Requires PHP:      7.4
 * Author:            Omitsis
 * Author URI:        https://example.com
 * Text Domain:       tamarind-recaptcha
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

namespace tamarind\recaptcha;

defined( 'ABSPATH' ) || exit;

const KEY_V2 = '6LchwdUpAAAAAOEx1te_E43fFKt0Y4NJmgVevYhE';
const KEY_V3 = '6LdFv9UpAAAAAJWxOk77YC8fEtfUxr8nRvscp7My';

/**
 * Return default values for the recaptcha keys when not found in the database.
 *
 * @param mixed  $default The default value to return if the option does not exist in the database.
 * @param string $option_name The option name.
 * @param bool   $passed_default Whether the default value was passed to the function.
 * @return mixed The value of the option.
 */
function get_default_recaptcha_key( $default, $option_name, $passed_default ) {
	if ( 'tamarind_recaptcha_key_v2' === $option_name ) {
		return KEY_V2;
	}
	if ( 'tamarind_recaptcha_key_v3' === $option_name ) {
		return KEY_V3;
	}
	return $default;
}

add_filter( 'default_option_tamarind_recaptcha_key_v2', __NAMESPACE__ . '\get_default_recaptcha_key', 10, 3 );
add_filter( 'default_option_tamarind_recaptcha_key_v3', __NAMESPACE__ . '\get_default_recaptcha_key', 10, 3 );

/**
 * Output the recaptcha v3 script.
 *
 * Attempt to improve the code that was used only on tobacco, harcoded in the theme.
 */
function recaptcha_v3_script() {
	$recaptcha_key = get_option( 'tamarind_recaptcha_key_v3' );
	// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	wp_enqueue_script( 'recaptcha-v3', 'https://www.google.com/recaptcha/api.js?render=' . $recaptcha_key, array(), null, false );
}

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\recaptcha_v3_script' );
