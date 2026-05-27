<?php

/**
 * Consent Fields for Tamarind Forms.
 *
 * @package Tamarind_Forms
 *
 * phpcs:disable WordPress.Files.FileName
 */

namespace tamarind_forms\display_form;

/**
 * Add Consent Field to Form.
 *
 * @param array $form Form.
 *
 * @return array
 */
function add_consent_field_to_form( $form )
{
	global $consent_fields_global;
	return consent_field_type( $form, $consent_fields_global );
}

/**
 * Consent Field Type. Create if not exists. Assign values.
 *
 * @param array $form Form.
 * @param array $fields Fields.
 *
 * @return array
 */
function consent_field_type( $form, $fields ) {

	// Exception: Do NOT add the field if the value of 'type-form' is 'login-user', 'forgot-password', 'reset-password', 'change-password' or 'update-user'.
	$excluded_forms = array( 'login-user', 'forgot-password', 'reset-password', 'change-password', 'update-user' );
	if ( in_array( get_type_form( $form ), $excluded_forms, true ) ) {
		return $form;
	}

	foreach ( $fields as $field_name => $field_value ) {
		// Checking if the field already exists.
		if ( ! in_array( $field_name, array_column( $form['fields'], 'name' ), true ) ) {
			// Create field consent.
			$new_field_id = \GFFormsModel::get_next_field_id( $form['fields'] );

			$consent_field = array(
				'type'          => 'consent',
				'name'          => $field_name,
				'label'         => ucfirst( str_replace( '-', ' ', $field_name ) ),
				'id'            => $new_field_id,
				'checkboxLabel' => 'I agree to <a href="#">Terms and Conditions</a>',
				'description'   => '',
				'isRequired'    => true,
				'inputs'        => array(
					array(
						'id'    => $new_field_id . '.1',
						'label' => 'Consent',
						'name'  => '',
					),
					array(
						'id'       => $new_field_id . '.2',
						'label'    => 'Text',
						'name'     => '',
						'isHidden' => 1,
					),
					array(
						'id'       => $new_field_id . '.3',
						'label'    => 'Description',
						'name'     => '',
						'isHidden' => 1,
					),
				),
				'inputType'     => 'consent',
				'choices'       => array(),
				'pageNumber'    => 1,
			);
			$new_field        = \GF_Fields::create( $consent_field );
			$form['fields'][] = $new_field;
		}
	}
	// Save form.
	\GFAPI::update_form( $form );

	// Assign values to consent fields.
	foreach ( $fields as $field_name => $field_value ) {
		foreach ( $form['fields'] as $field ) {
			if ( 'consent' === $field->type && $field_name === $field->name ) {
				$field->checkboxLabel = $field_value;
				$field->label         = '';
			}
		}
	}

	return $form;
}

/**
 * Add Filter Consent Fields.
 *
 * @param array $fields Fields.
 *
 * @return void
 */
function add_filter_consent_fields( $fields ) {
	global $consent_fields_global;
	$consent_fields_global = $fields;
	if ( ! empty( $fields ) ) {
		add_filter( 'gform_pre_render', __NAMESPACE__ . '\add_consent_field_to_form', 10, 1 );
		add_filter( 'gform_pre_validation', __NAMESPACE__ . '\add_consent_field_to_form', 10, 1 );
		add_filter( 'gform_pre_submission_filter', __NAMESPACE__ . '\add_consent_field_to_form', 10, 1 );
		add_filter( 'gform_admin_pre_render', __NAMESPACE__ . '\add_consent_field_to_form', 10, 1 );
	}
}

/**
 * Reset Filter Consent Fields.
 *
 * @return void
 */
function reset_filter_consent_fields()
{
	global $consent_fields_global;
	if ( ! empty( $consent_fields_global ) ) {
		remove_filter( 'gform_pre_render', __NAMESPACE__ . '\add_consent_field_to_form', 10, 1 );
		remove_filter( 'gform_pre_validation', __NAMESPACE__ . '\add_consent_field_to_form', 10, 1 );
		remove_filter( 'gform_pre_submission_filter', __NAMESPACE__ . '\add_consent_field_to_form', 10, 1 );
		remove_filter( 'gform_admin_pre_render', __NAMESPACE__ . '\add_consent_field_to_form', 10, 1 );
	}
	$consent_fields_global = array();
}
