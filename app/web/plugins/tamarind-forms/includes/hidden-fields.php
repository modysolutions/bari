<?php
/**
 * Hidden Fields for Tamarind Forms.
 *
 * @package Tamarind_Forms
 *
 * phpcs:disable WordPress.Files.FileName
 */

namespace tamarind_forms\display_form;

/**
 * Add Hidden Field to Form.
 *
 * @param array $form Form.
 *
 * @return array
 */
function add_hidden_field_to_form( $form ) {
	global $hidden_fields_global;
	return hidden_field_type( $form, $hidden_fields_global );
}

/**
 * Hidden Field Type. Create if not exists. Assign values.
 *
 * @param array $form Form.
 * @param array $fields Fields.
 *
 * @return array
 */
function hidden_field_type( $form, $fields ) {

	foreach ( $fields as $field_name => $field_value ) {
		// Checking if the field already exists.
		if ( ! in_array( $field_name, array_column( $form['fields'], 'name' ),true ) ) {
			// Create field hidden.
			$new_field_id = \GFFormsModel::get_next_field_id( $form['fields'] );

			$hidden_field = array(
				'type'         => 'hidden',
				'name'         => $field_name,
				'label'        => esc_html__( ucfirst( str_replace( '-', ' ', $field_name ) ), 'tamarind' ),
				'id'           => $new_field_id,
				'defaultValue' => $field_name,
			);

			if ( ( 'country_code' !== $field_name ) || ( 'country_code' === $field_name && 1 === $field_value ) ) {
				// Create field hidden.
				$new_field        = \GF_Fields::create( $hidden_field );
				$form['fields'][] = $new_field;
			}
		}
	}
	// Save form.
	\GFAPI::update_form( $form );

	// Assign values to hidden fields.
	foreach ( $fields as $field_name => $field_value ) {
		foreach ( $form['fields'] as $field ) {
			if ( 'hidden' === $field->type && $field_name === $field->name ) {
				$field->defaultValue = $field_value;
			}
		}
	}

	return $form;
}

/**
 * Add Filter Hidden Fields.
 *
 * @param array $fields Fields.
 *
 * @return void
 */
function add_filter_hidden_fields( $fields ) {
	global $hidden_fields_global;
	$hidden_fields_global = $fields;
	if ( ! empty( $fields ) ) {
		add_filter( 'gform_pre_render', __NAMESPACE__ . '\add_hidden_field_to_form', 10, 1 );
		add_filter( 'gform_pre_validation', __NAMESPACE__ . '\add_hidden_field_to_form', 10, 1 );
		add_filter( 'gform_pre_submission_filter', __NAMESPACE__ . '\add_hidden_field_to_form', 10, 1 );
		add_filter( 'gform_admin_pre_render', __NAMESPACE__ . '\add_hidden_field_to_form', 10, 1 );
	}
}

/**
 * Reset Filter Hidden Fields.
 *
 * @return void
 */
function reset_filter_hidden_fields() {
	global $hidden_fields_global;
	if ( ! empty( $hidden_fields_global ) ) {
		remove_filter( 'gform_pre_render', __NAMESPACE__ . '\add_hidden_field_to_form', 10, 1 );
		remove_filter( 'gform_pre_validation', __NAMESPACE__ . '\add_hidden_field_to_form', 10, 1 );
		remove_filter( 'gform_pre_submission_filter', __NAMESPACE__ . '\add_hidden_field_to_form', 10, 1 );
		remove_filter( 'gform_admin_pre_render', __NAMESPACE__ . '\add_hidden_field_to_form', 10, 1 );
	}
	$hidden_fields_global = array();
}
