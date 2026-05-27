<?php
/**
 * Functions for Case Studies
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\case_studies;

defined( 'ABSPATH' ) || exit;


add_filter( 'template_include', __NAMESPACE__ . '\case_studies_single_template' );

/**
 * Loads the Case Studies Single template
 *
 * @param string $template Current template.
 *
 * @return string Case Studies single template.
 */
function case_studies_single_template( string $template ): string {
	if ( is_singular( 'case_studies' ) ) {
		$plugin_path   = plugin_dir_path( __FILE__ );
		$template_path = $plugin_path . '/templates/single-case_studies.php';
		if ( file_exists( $template_path ) ) {
			return $template_path;
		}
	}
	return $template;
}
