<?php
/**
 * Omitsis Cookie Yes
 *
 * @package           OmitsisCookieYes
 * @author            Omitsis Consulting
 * @copyright         2025 Omitsis Consulting
 * @license           GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Omitsis Cookie Yes
 * Plugin URI:        https://wp-addons.omitsis.com/plugins/omitsis-cookie-yes/
 * Description:       A simple plugin to insert the CookieYes banner script.
 * Version:           1.2.0
 * Requires at least: 5.9
 * Requires PHP:      7.4
 * Author:            Omitsis Consulting
 * Author URI:        https://www.omitsis.com/
 * License:           GPL-3.0-or-later
 * License URI:       https://spdx.org/licenses/GPL-3.0-or-later.html
 * Text Domain:       omitsis-cookie-yes
 * Domain Path:       /languages
 * Update URI:        false
 *
 * @phpcs:disable WordPress.NamingConventions.ValidHookName
 */

namespace OmitsisCookieYes;

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/includes/settings.php';


// Print the CookieYes script in the HTML head.
add_action( 'wp_head', __NAMESPACE__ . '\insert_cookieyes_script', 10 );
// Prefer an environment set value.
add_filter( 'pre_option_omitsis_cookie_yes_id', __NAMESPACE__ . '\override_id_from_env' );

/**
 * Prints the CookieYes script in the HTML head.
 */
function insert_cookieyes_script() {
	$cookie_yes_id = get_option( 'omitsis_cookie_yes_id', '' );

	if ( $cookie_yes_id ) {
		?>
		<script>
			window.dataLayer = window.dataLayer || [];
			function gtag() {
				dataLayer.push(arguments);
			}
			gtag("consent", "default", {
				ad_storage: "denied",
				ad_user_data: "denied",
				ad_personalization: "denied",
				analytics_storage: "denied",
				functionality_storage: "denied",
				personalization_storage: "denied",
				security_storage: "granted",
				wait_for_update: 2000,
			});
			gtag("set", "ads_data_redaction", true);
			gtag("set", "url_passthrough", true);
		</script>
		<script id="cookieyes" type="text/javascript" src="https://cdn-cookieyes.com/client_data/<?php echo esc_attr( $cookie_yes_id ); ?>/script.js"></script> <?php // phpcs:ignore ?>
		<?php
	}
}

/**
 * Prefers an environment set value.
 *
 * @param string $value Current value.
 */
function override_id_from_env( $value = '' ) {
	$env_value = getenv( 'OMITSIS_COOKIE_YES_ID' );
	if ( $env_value ) {
		return $env_value;
	}
	return $value;
}
