<?php
/**
 * Permalinks functions
 *
 * @package Tamarind_Search
 * 
 */

namespace tamarind_search;

defined( 'ABSPATH' ) || exit;


/**
 * Register a custom route for step search
 */
function register_route_search(): void {
    if ( get_field( 'search_url_settings', 'option' ) ) {
        $url_settings = get_field( 'search_url_settings', 'option' );
        foreach ( $url_settings as $url_setting ) {
            $url_key_slug = str_replace( '_', '-', $url_setting['search_url_key_slug'] );
            $url_slug     = strtolower( $url_setting['search_url_slug'] );
            add_rewrite_rule( '^' . $url_slug . '$', 'index.php?search=' . $url_key_slug, 'top' );
        }
    }
}

/**
 * Add search query var
 *
 * @param array $vars The query vars.
 *
 * @return array
 */
function add_search_query_var( array $vars ): array {
	$vars[] = 'search';
	return $vars;
}

/**
 * Load the step template
 */
function load_search_template(): void {
	if ( get_query_var( 'search' ) ) :
		include PLUGIN_PATH . '/templates/dynamic-url.php';
		exit;
	endif;
}

/**
 * Add action route search
 */
function add_action_route_search(): void {
	add_action( 'init', __NAMESPACE__ . '\register_route_search' );
	add_filter( 'query_vars', __NAMESPACE__ . '\add_search_query_var' );
	add_action( 'template_redirect', __NAMESPACE__ . '\load_search_template' );
}

/**
 * Remove action route search
 */
function remove_action_route_search(): void {
	remove_action( 'init', __NAMESPACE__ . '\register_route_search' );
	remove_filter( 'query_vars', __NAMESPACE__ . '\add_search_query_var' );
	remove_action( 'template_redirect', __NAMESPACE__ . '\load_search_template' );
}

/**
 * Get the slug by key
 *
 * @param string $key The key.
 *
 * @return bool|string
 */
function get_slug_by_key( string $key ): bool|string {
	if ( get_field( 'search_url_settings', 'option' ) ) {
		$url_settings = get_field( 'search_url_settings', 'option' );
		foreach ( $url_settings as $url_setting ) {
			if ( $url_setting['search_url_key_slug'] === $key ) {
				return $url_setting['search_url_slug'];
			}
		}
	}
	return false;
}

/**
 * Add domain to url.
 *
 * @param string $url The url.
 *
 * @return string
 */
function add_domain_to_url( string $url ): string {
    // if $url contains a domain, return it, else add the domain
    if ( str_contains( $url, 'http' ) ) {
        return $url;
    }
    return get_home_url() . '/' . $url;
}

/**
 * Get the menu link by key.
 *
 * @param string $key The key.
 *
 * @return false|string
 */
function get_menu_link_by_key ( string $key ): bool|string {

	$slug = get_slug_by_key( $key );

	if ( $slug ) {
		$link = strtolower(add_domain_to_url( $slug ));
		return esc_url($link);
	}

	return false;
}