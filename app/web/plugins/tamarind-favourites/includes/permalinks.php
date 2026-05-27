<?php
/**
 * Permalinks functions
 *
 * @package Tamarind_Favourites
 */

namespace tamarind_favourites;

defined( 'ABSPATH' ) || exit;

/**
 * Register custom route for steps favourites.
 */
function register_route_favourites() {
	if ( get_field( 'favourites_url_settings', 'option' ) ) {
		$url_settings = get_field( 'favourites_url_settings', 'option' );
		foreach ( $url_settings as $url_setting ) {
			$url_key_slug = str_replace( '_', '-', $url_setting['favourites_url_key_slug'] );
			$url_slug     = strtolower( $url_setting['favourites_url_slug'] );
			add_rewrite_rule( '^' . $url_slug . '$', 'index.php?favourites=' . $url_key_slug, 'top' );
		}
	}
}

/**
 * Add favourites query var.
 *
 * @param array $vars The query vars.
 * @return array
 */
function add_favourites_query_var( $vars ) {
	$vars[] = 'favourites';
	return $vars;
}

/**
 * Load the step template.
 */
function load_favourites_template() {
	if ( get_query_var( 'favourites' ) ) :
		include PLUGIN_PATH . '/templates/dynamic-url.php';
		exit;
	endif;
}

/**
 * Add action route favourites.
 */
function add_action_route_favourites() {
	add_action( 'init', __NAMESPACE__ . '\register_route_favourites' );
	add_filter( 'query_vars', __NAMESPACE__ . '\add_favourites_query_var' );
	add_action( 'template_redirect', __NAMESPACE__ . '\load_favourites_template' );
}

/**
 * Remove action route favourites.
 */
function remove_action_route_favourites() {
	remove_action( 'init', __NAMESPACE__ . '\register_route_favourites' );
	remove_filter( 'query_vars', __NAMESPACE__ . '\add_favourites_query_var' );
	remove_action( 'template_redirect', __NAMESPACE__ . '\load_favourites_template' );
}

/**
 * Get the slug by key.
 *
 * @param string $key The key.
 * @return string
 */
function get_slug_by_key( $key ) {
	if ( get_field( 'favourites_url_settings', 'option' ) ) {
		$url_settings = get_field( 'favourites_url_settings', 'option' );
		foreach ( $url_settings as $url_setting ) {
			if ( $url_setting['favourites_url_key_slug'] === $key ) {
				return $url_setting['favourites_url_slug'];
			}
		}
	}
	return false;
}

/**
 * Add domain to url.
 *
 * @param string $url The url.
 * @return string
 */
function add_domain_to_url( $url ) {
	// if $url contains a domain, return it, else add the domain.
	if ( strpos( $url, 'http' ) !== false ) {
		return $url;
	}
	return get_home_url() . '/' . $url;
}

/**
 * Get the menu link by key.
 *
 * @param string $key The key.
 * @return array
 */
function get_menu_link_by_key ( $key ) {

	$slug = get_slug_by_key( $key );

	if ( $slug ) {
		$link = strtolower( add_domain_to_url( $slug ) );
		return esc_url( $link );
	}

	return false;
}
