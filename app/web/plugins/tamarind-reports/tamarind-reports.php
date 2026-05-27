<?php
/**
 * Plugin name: Tamarind Reports
 * Description: Tamarind Intelligence reports.
 * Author: Tamarind Intelligence.
 * Author URI: https://tamarindintelligence.com
 * Text Domain: tamarind-reports
 * Domain Path: /languages
 * version: 0.0.1
 *
 * @package tamarind_reports
 */

namespace tamarind_reports;

defined( 'ABSPATH' ) || exit;

const VERSION = '0.0.1';
define('TM_REPORTS_DIR', plugin_dir_path( __FILE__ ));
define('TM_REPORTS_TEMPLATE_PARTS_DIR', plugin_dir_path( __FILE__ ) . 'template-parts/');
define('TM_REPORTS_URL', plugin_dir_url( __FILE__ ));

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\wp_enqueue_scripts', 100 );

/**
 * Enqueue the styles and scripts
 *
 * @return void
 */
function wp_enqueue_scripts(): void {
	$reports_assets = TM_REPORTS_DIR . 'dist/tamarind_reports.asset.php';
	if(!file_exists($reports_assets)) {
		wp_die($reports_assets, 'tamarind_reports_assets', array('back_link' => true));
	}
	$assets = require $reports_assets;
	wp_enqueue_style( 'tm-reports', TM_REPORTS_URL . 'dist/tamarind_reports.css', array(), $assets['version'] );
	wp_register_script( 'tm-reports', TM_REPORTS_URL . 'dist/tamarind_reports.js', $assets['dependencies'], $assets['version'], array('in_footer' => true ) );
	if(current_user_can('read_tamarind_report') && is_page('usage-report')) {
		$args = array(
			'role' => 'client',
			'fields' => array('ID', 'user_email', 'display_name'),
		);

		$cache_args = array(
			'user_id' => get_current_user_id(),
			'args'    => $args,
		);
		$cache_key = 'users_' . md5( serialize( $cache_args ) );
		$cache_group = 'users';
		$users = wp_cache_get( $cache_key, $cache_group );
		if(!$users){
			$users = get_users($args);
			wp_cache_set( $cache_key, $users, $cache_group, WEEK_IN_SECONDS );
		}
		wp_localize_script( 'tm-reports', 'tamarind_reports_vars', array(
			'users' => array_map(function($user) {
				return array(
					'user_id' => $user->ID,
					'name' => $user->display_name,
					'email' => strtolower($user->user_email)
				);
			}, $users),
		));
	}
	wp_enqueue_script( 'tm-reports' );
}


$includes = glob( TM_REPORTS_DIR . 'includes/*.php' );
if(count($includes) > 0) {
	foreach($includes as $include) {
		require_once $include;
	}
}
