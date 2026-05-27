<?php
/**
 * Get SVG icon.
 *
 * @package Tamarind_Base
 */

namespace tamarind_base;

defined( 'ABSPATH' ) || exit;


/**
 * Get SVG icon.
 *
 * @param string $file File name (without .svg extension).
 * @param string $icon_class Optional class to add to the SVG.
 * @param string $title Optional title for accessibility.
 *
 * @return string SVG contents or empty string if not found.
 */
function get_svg_icon( string $file, string $icon_class = '', string $title = '' ): string {
	// 1. Basic validation.
	if ( ! $file ) {
		return '';
	}

	// 2. File path.
	$file = basename( $file );
	$path = plugin_dir_path( __DIR__ ) . 'assets/icons/' . $file . '.svg';

	if ( ! file_exists( $path ) ) {
		return '';
	}

	// 3. Load the SVG.
	// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
	$content = file_get_contents( $path );
	if ( false === $content ) {
		return '';
	}

	// 4. Normalize the SVG.
	$content = preg_replace( '/\s+/', ' ', $content );
	$content = trim( $content );

	// 5. Add class if specified
	if ( ! empty( $icon_class ) ) {
		$content = preg_replace( '/<svg([^>]*)>/', '<svg$1 class="' . esc_attr( $icon_class ) . '">', $content, 1 );
	}

	// 6. Add accessibility (with hidden <text>).
	if ( ! empty( $title ) ) {
		$unique_id = 'desc-' . uniqid();

		// Insert ARIA attributes into the SVG.
		$content = preg_replace(
			'/<svg([^>]*)>/',
			'<svg$1 role="img" aria-labelledby="' . esc_attr( $unique_id ) . '">',
			$content,
			1
		);

		// Insert hidden text as the first element.
		$hidden_text = '<text id="' . esc_attr( $unique_id ) . '" style="font-size:0; opacity:0;">'
					. esc_html( $title ) . '</text>';

		$content = preg_replace(
			'/<svg([^>]*)>/',
			'<svg$1>' . $hidden_text,
			$content,
			1
		);
	}

	// 7. Return the content
	return $content;
}
