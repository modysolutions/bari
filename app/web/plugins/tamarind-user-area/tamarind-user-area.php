<?php
/**
 * Plugin Name:     Tamarind User Area
 * Plugin URI:      https://www.omitsis.com
 * Description:     User Area Settings for Tamarind.
 * Author:          Omitsis
 * Author URI:      https://www.omitsis.com
 * Text Domain:     tamarind-user-area
 * Domain Path:     /languages
 * Version:         1.1.0
 *
 * @package         Tamarind_UserArea
 */

namespace tamarind_userarea;

defined( 'ABSPATH' ) || exit;

$version = '1.1.0';
if(file_exists(dirname(__FILE__) . '/dist/css/tamarind-user-area.min.css')) {
    $version = filemtime(dirname(__FILE__) . '/dist/css/tamarind-user-area.min.css');
}
define('TM_UA_VERSION', $version);

/**
 * Path to the plugin
 */
const PLUGIN_PATH = __DIR__;

require_once PLUGIN_PATH . '/includes/plugin-actions.php';
require_once PLUGIN_PATH . '/includes/acf-options.php';
require_once PLUGIN_PATH . '/includes/permalinks.php';

require_once PLUGIN_PATH . '/includes/role-access.php';

require_once PLUGIN_PATH . '/includes/userarea-menu.php';

require_once PLUGIN_PATH . '/includes/dashboard.php';
require_once PLUGIN_PATH . '/includes/my-subscription.php';
require_once PLUGIN_PATH . '/includes/newsletter.php';
require_once PLUGIN_PATH . '/includes/woocommerce.php';
require_once PLUGIN_PATH . '/includes/header-icons.php';

/**
 * Enqueue the styles and scripts
 *
 * @return void
 */
function tamarind_userarea_enqueue_scripts() {
	$plugin_url = plugin_dir_url( __FILE__ );

	wp_enqueue_style( 'tm-user-area', $plugin_url . 'dist/css/tamarind-user-area.min.css', array(), TM_UA_VERSION );
	wp_enqueue_script( 'tm-user-area', $plugin_url . 'dist/js/tamarind-user-area.min.js', array(), TM_UA_VERSION,
        true );

	wp_localize_script(
		'tm-user-area',
		'tmUserArea',
		array(
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'tm_user_area_nonce' ),
		)
	);
}

/**
 * Detect if the current page is from the User Area
 */
add_action(
	'wp',
	function() {
		if ( is_userarea() ) {
			add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\tamarind_userarea_enqueue_scripts' );
		}
	}
);

/**
 * Init the actions
 */
init_actions( __FILE__ );

if ( \is_plugin_active( 'tamarind-user-area/tamarind-user-area.php' ) ) {
	add_action_route_userarea();
}
