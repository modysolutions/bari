<?php
/**
 * Plugin Name:     Tamarind Analytics
 * Plugin URI:      https://www.omitsis.com
 * Description:     Analytics and Tracking for Tamarind.
 * Author:          Omitsis
 * Author URI:      https://www.omitsis.com
 * Text Domain:     tamarind-analytics
 * Domain Path:     /languages
 * Version:         1.1.0
 *
 * @package         Tamarind_Analytics
 */

namespace tamarind_analytics;

defined( 'ABSPATH' ) || exit;

/**
 * Path to the plugin
 */
const PLUGIN_PATH = __DIR__;

require_once PLUGIN_PATH . '/includes/acf-options.php';
require_once PLUGIN_PATH . '/includes/tracking.php';
require_once PLUGIN_PATH . '/includes/woocommerce.php';
require_once PLUGIN_PATH . '/includes/output-ua-gtm.php';

/**
 * Render a template part, allowing theme overrides.
 *
 * @param string $relative Relative path to the template file.
 * @param array  $args     Arguments to pass to the template.
 * @return void
 */
function tm_ga4_render_template( string $relative, array $args = array() ): void {
	$theme_template = locate_template( $relative );
	if ( $theme_template ) {
		require $theme_template;
		return;
	}

	$plugin_template = PLUGIN_PATH . '/' . $relative;
	if ( file_exists( $plugin_template ) ) {
		require $plugin_template;
	}
}
