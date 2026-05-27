<?php
/**
 * Handle common plugin actions
 *
 * @package Tamarind_UserArea
 * phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed
 */

namespace tamarind_userarea;

defined( 'ABSPATH' ) || exit;

/**
 * Custom template locator for WooCommerce templates
 */
add_filter( 'woocommerce_locate_template', __NAMESPACE__ . '\tamarind_wc_locate_template', 10, 3 );

/**
 * Flush rewrite rules
 */
function flush_rewrite_rules_user_area() {
    delete_option( 'rewrite_rules' );
}

/**
 * The code that runs during plugin activation.
 */
function activate_tm_user_area() {
	add_action_route_userarea();
	flush_rewrite_rules_user_area();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_tm_user_area() {
	remove_action_route_userarea();
	flush_rewrite_rules_user_area();
}

/**
 * Register plugin activation, deactivation and uninstall hooks
 *
 * @param string $file The main plugin file path.
 */
function init_actions( $file ) {
	register_activation_hook( $file, __NAMESPACE__ . '\activate_tm_user_area' );
	register_deactivation_hook( $file, __NAMESPACE__ . '\deactivate_tm_user_area' );
}

/**
 * Detecta si la página actual es del User Area
 */
function is_userarea() {
	if ( ! function_exists( 'get_field' ) ) {
		return false;
	}

    // Obtener la URL actual relativa (sin dominio).
	$current_slug = '';
	if ( isset( $_SERVER['REQUEST_URI'] ) ) {
		$request_uri = sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		$current_slug = trim( wp_parse_url( $request_uri, PHP_URL_PATH ), '/' );
	}

    $url_settings = get_field( 'url_settings', 'option' );
    if ( $url_settings && is_array( $url_settings ) ) {
        foreach ( $url_settings as $setting ) {
            if ( isset( $setting['url_slug'] ) && $setting['url_slug'] === $current_slug ) {
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

/**
 * Custom template locator for WooCommerce templates
 *
 * @param string $template The path to the template.
 * @param string $template_name The name of the template.
 * @param string $template_path The path to the template directory.
 * @return string The path to the located template.
 */
function tamarind_wc_locate_template( $template, $template_name, $template_path ) {
    $plugin_path = plugin_dir_path( __FILE__ ) . '../templates/woocommerce/';
    if ( file_exists( $plugin_path . $template_name ) ) {
        return $plugin_path . $template_name;
    }
    return $template;
}
