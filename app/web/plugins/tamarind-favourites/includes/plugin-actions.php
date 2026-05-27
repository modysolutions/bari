<?php
/**
 * Handle common plugin actions
 *
 * @package Tamarind_Favourites
 */

namespace tamarind_favourites;

defined( 'ABSPATH' ) || exit;


/**
 * Flush rewrite rules.
 */
function flush_rewrite_rules_favourites() {
	delete_option( 'rewrite_rules' );
}

/**
 * The code that runs during plugin activation.
 */
function activate_tm_favourites() {
	create_favourites_history_table();
	add_action_route_favourites();
	flush_rewrite_rules_favourites();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_tm_favourites() {
	remove_action_route_favourites();
	flush_rewrite_rules_favourites();
}

/**
 * Register plugin activation, deactivation and uninstall hooks.
 *
 * @param string $file The main plugin file path.
 */
function init_actions( $file ) {
	register_activation_hook( $file, __NAMESPACE__ . '\activate_tm_favourites' );
	register_deactivation_hook( $file, __NAMESPACE__ . '\deactivate_tm_favourites' );
}

/**
 * Detect if the current page is a Favourites section.
 */
function is_favourites_menu() {
	if ( ! function_exists( 'get_field' ) ) {
		return false;
	}

	// Get the current relative URL (without the domain).
	$current_slug = trim( wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );

	$url_settings = get_field( 'favourites_url_settings', 'option' );
	if ( $url_settings && is_array( $url_settings ) ) {
		foreach ( $url_settings as $setting ) {
			if ( isset( $setting['favourites_url_slug'] ) && $setting['favourites_url_slug'] === $current_slug ) {
				return true;
			}
		}
	}
	return false;
}

/**
 * Redirect if not logged in
 */
function redirect_if_not_logged_in() {
	if ( ! is_user_logged_in() ) {
		if ( is_plugin_active( 'tamarind-forms/tamarind-forms.php' ) ) {
			$login_page = get_field( 'user_label_login_page', 'option' );
			wp_safe_redirect( $login_page );
			exit;
		} else {
			wp_safe_redirect( home_url() );
			exit;
		}
	}
	return false;
}
