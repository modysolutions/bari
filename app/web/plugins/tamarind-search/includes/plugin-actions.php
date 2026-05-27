<?php
/**
 * Handle common plugin actions
 *
 * @package Tamarind_Search
 */

namespace tamarind_search;

defined( 'ABSPATH' ) || exit;


/**
 * Flush rewrite rules
 */
function flush_rewrite_rules_search(): void {
	delete_option( 'rewrite_rules' );
}

/**
 * The code that runs during plugin activation.
 */
function activate_tm_search(): void {
	add_action_route_search();
	flush_rewrite_rules_search();
	search_history\create_table();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_tm_search(): void {
	remove_action_route_search();
	flush_rewrite_rules_search();
}

/**
 * Register plugin activation, deactivation and uninstall hooks
 *
 * @param string $file The main plugin file path.
 */
function init_actions( string $file ): void {
	register_activation_hook( $file, __NAMESPACE__ . '\activate_tm_search' );
	register_deactivation_hook( $file, __NAMESPACE__ . '\deactivate_tm_search' );
}

/**
 * Detect if the current page is a Search section
 */
function is_search_section(): bool {
	if ( ! function_exists( 'get_field' ) ) {
		return false;
	}

	// Get the current relative URL (without the domain).
	$current_slug = trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );

	$url_settings = get_field( 'search_url_settings', 'option' );
	if ( $url_settings && is_array( $url_settings ) ) {
		foreach ( $url_settings as $setting ) {
			if ( isset( $setting['search_url_slug'] ) && $setting['search_url_slug'] === $current_slug ) {
				return true;
			}
		}
	}

	return false;
}

/**
 * Redirect if not logged in
 */
function redirect_if_not_logged_in(): void {
	if ( ! is_user_logged_in() ) {
		if ( is_plugin_active( 'tamarind-forms/tamarind-forms.php' ) ) {
			$login_page = get_field( 'user_label_login_page', 'option' );
			wp_safe_redirect( $login_page );
		} else {
			wp_safe_redirect( home_url() );
		}
		exit;
	}
}
