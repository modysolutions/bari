<?php
/**
 * Permalinks functions
 *
 * @package Tamarind_UserArea
 * phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed
 */

namespace tamarind_userarea;

defined( 'ABSPATH' ) || exit;

/**
 * Register custom route for steps userarea
 */
function register_route_userarea() {
    if ( get_field( 'url_settings', 'option' ) ) {
        $url_settings = get_field( 'url_settings', 'option' );
        foreach ( $url_settings as $url_setting ) {
            $url_key_slug = str_replace( '_', '-', $url_setting['url_key_slug'] );
            $url_slug     = strtolower( $url_setting['url_slug'] );
            add_rewrite_rule( '^' . $url_slug . '$', 'index.php?userarea=' . $url_key_slug, 'top' );
        }
    }
}

/**
 * Add userarea query var
 *
 * @param array $vars The query vars.
 * @return array
 */
function add_userarea_query_var( $vars ) {
	$vars[] = 'userarea';
	return $vars;
}

/**
 * Load the step template
 */
function load_userarea_template() {
	if ( get_query_var( 'userarea' ) ) :
		include PLUGIN_PATH . '/templates/dynamic-url.php';
		exit;
	endif;
}

/**
 * Add action route userarea
 */
function add_action_route_userarea() {
	add_action( 'init', __NAMESPACE__ . '\register_route_userarea' );
	add_filter( 'query_vars', __NAMESPACE__ . '\add_userarea_query_var' );
	add_action( 'template_redirect', __NAMESPACE__ . '\load_userarea_template' );
}

/**
 * Remove action route userarea
 */
function remove_action_route_userarea() {
	remove_action( 'init', __NAMESPACE__ . '\register_route_userarea' );
	remove_filter( 'query_vars', __NAMESPACE__ . '\add_userarea_query_var' );
	remove_action( 'template_redirect', __NAMESPACE__ . '\load_userarea_template' );
}

/**
 * Get the key by slug
 *
 * @param string $slug The slug.
 * @return string
 */
function get_key_by_slug( $slug ) {
	if ( get_field( 'url_settings', 'option' ) ) {
		$url_settings = get_field( 'url_settings', 'option' );
		foreach ( $url_settings as $url_setting ) {
			if ( $url_setting['url_slug'] === $slug ) {
				return $url_setting['url_key_slug'];
			}
		}
	}
	return false;
}
