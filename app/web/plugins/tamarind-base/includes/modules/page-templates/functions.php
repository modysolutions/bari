<?php
/**
 * Functions for Taxonomies
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\page_templates;

defined( 'ABSPATH' ) || exit;

const PLUGIN_PATH = __DIR__;

add_filter( 'theme_page_templates', __NAMESPACE__ . '\tamarind_register_page_templates' );
add_filter( 'template_include', __NAMESPACE__ . '\tamarind_load_page_templates' );

/**
 * Register custom page templates.
 *
 * @param array $templates Existing page templates.
 *
 * @return array Modified page templates.
 */
function tamarind_register_page_templates( array $templates ): array {
	$plugin_templates = array(
		'page-video-demos.php' => __( 'Page Video Demos', TM_LANGUAGE_DOMAIN ),
	);

	return array_merge( $templates, $plugin_templates );
}

/**
 * Load custom page templates from the plugin.
 *
 * @param string $template Current template.
 *
 * @return string Modified template path if custom template is used, otherwise original template.
 */
function tamarind_load_page_templates( string $template ): string {
	global $post;

	if ( ! $post || ! is_page() ) {
		return $template;
	}

	$page_template = get_page_template_slug( $post->ID );

	$templates_to_check = array( 'page-video-demos.php' );

	if ( in_array( $page_template, $templates_to_check, true ) ) {
		$plugin_template_path = plugin_dir_path( __FILE__ ) . 'templates/' . $page_template;
		
		if ( file_exists( $plugin_template_path ) ) {
			return $plugin_template_path;
		}
	}

	return $template;
}
