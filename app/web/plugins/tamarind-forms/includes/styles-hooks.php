<?php
/**
 * Gravity Forms Classes for Tamarind Forms.
 *
 * @package Tamarind_Forms
 *
 * phpcs:disable WordPress.Files.FileName
 */

namespace tamarind_forms\styles_hooks;

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'GFAPI' ) ) {
	add_filter( 'gform_field_css_class', __NAMESPACE__ . '\add_custom_class_to_fields_container', 10, 3 );
}

/**
 * Add custom class to fields container.
 *
 * @param string $classes Classes.
 * @param object $field Field.
 * @param object $form Form.
 *
 * @return string
 */
function add_custom_class_to_fields_container( $classes, $field, $form ) {

	switch ( $field->type ) {
		case 'text':
			$classes .= ' tm-input';
			break;
		case 'name':
			$classes .= ' tm-name';
			break;
		case 'phone':
			$classes .= ' tm-phone';
			break;
		case 'email':
			$classes .= ' tm-email';
			break;
		case 'website':
			$classes .= ' tm-website';
			break;
		case 'select':
			$classes .= ' tm-select';
			break;
		case 'textarea':
			$classes .= ' tm-textarea';
			break;
		case 'hidden':
			$label    = sanitize_string( $field->label );
			$classes .= ' tm-hidden tm-hidden-' . $label;
			break;
		default:
			$classes .= ' tm-default';
			break;
	}
	return $classes;
}

/**
 * Sanitize string.
 *
 * @param string $custom_string String.
 *
 * @return string
 */
function sanitize_string( $custom_string ) {
	$custom_string = preg_replace( '/[^a-zA-Z0-9\s]/', '', $custom_string ); // Delete special characters and spaces.
	$custom_string = strtolower( $custom_string ); // string to lower case.
	$custom_string = trim( $custom_string ); // Delete spaces at the beginning and end.
	$custom_string = str_replace( ' ', '_', $custom_string ); // Replace spaces with underscores (optional).
	return $custom_string;
}
