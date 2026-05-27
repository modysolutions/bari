<?php
/**
 * Dinamic Gravity Forms Fields for Tamarind Forms.
 *
 * @package Tamarind_Forms
 *
 * phpcs:disable WordPress.Files.FileName
 */

namespace tamarind_forms\dinamic_fields;

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'GFAPI' ) ) {
	add_filter( 'gform_pre_render', __NAMESPACE__ . '\full_dinamic_fields' );
	add_filter( 'gform_pre_validation', __NAMESPACE__ . '\full_dinamic_fields' );
	add_filter( 'gform_pre_submission_filter', __NAMESPACE__ . '\full_dinamic_fields' );
	add_filter( 'gform_admin_pre_render', __NAMESPACE__ . '\full_dinamic_fields' );

	/*
	* Control ACF fields for Gravity Forms in backend.
	*/
	add_filter( 'acf/load_field/key=field_tamarind_forms_select_form', __NAMESPACE__ . '\populate_select_form_field' );
	add_filter( 'acf/load_field/key=field_tamarind_forms_select_type_form', __NAMESPACE__ . '\populate_select_type_form_field' );
	add_filter( 'acf/load_field/key=field_tamarind_forms_select_zoho_action_form', __NAMESPACE__ . '\populate_select_zoho_action_field' );

}


/**
 * Get dinamic choices for select field.
 *
 * @param string $gf_param_field The Gravity Forms field name.
 * @return array The choices for the select field.
 */
function get_dinamic_choices( $gf_param_field ) {
	$choices      = array();
	$forms_fields = get_field( 'forms_fields', 'option' );
	foreach ( $forms_fields as $form_field ) {
		if ( $form_field['gf_name'] === $gf_param_field ) {
			$options = $form_field['options'];
			foreach ( $options as $option ) {
				$choices[] = array(
					'text'  => $option['option_name'],
					'value' => $option['option_value']
				);
			}
		}
	}
	return $choices;
}

/**
 * Check if form is China form.
 *
 * @param array $form The Gravity Forms form array.
 * @return bool True if the form is a China form, false otherwise.
 */
function is_china_form ( $form ) {
	if ( $form['fields'] ) {
		foreach ( $form['fields'] as &$field ) {
			if ( 'hidden' === $field->type && 'type-form' === $field->name && ( 'china' === $field->defaultValue || 'china-download' === $field->defaultValue ) ) {
				return true;
			}
		}
	}
	return false;
}

/**
 * Full dinamic fields.
 *
 * @param array $form The Gravity Forms form array.
 * @return array The modified form with dinamic fields.
 */
function full_dinamic_fields( $form ) {

	foreach ( $form['fields'] as &$field ) {

		if ( ( 'select' !== $field->type ) || '' === $field->inputName ) {
			continue;
		}

		if ( 'select' === $field->type ) {
			$choices = array();
			$choices = get_dinamic_choices( $field->inputName );

			$field->placeholder = $field->label;

			if ( is_china_form( $form ) ) {
				switch ( $field->label ) {
					case 'Department':
						$field->placeholder = get_field( 'field_tamarind_forms_field_china_label_department', 'option' );
						break;
					case 'Industry':
						$field->placeholder = get_field( 'field_tamarind_forms_field_china_label_industry', 'option' );
						break;
				}
			}
			$field->choices = $choices;
		}
	}
	return $form;
}

/**
 * Populate select form field with ACF defined in settings.
 *
 * @param array $field The field array to populate.
 * @return array The populated field array.
 */
function populate_select_form_field( $field ) {
	if ( class_exists( 'GFFormsModel' ) ) {
		$forms = \GFAPI::get_forms();
		if ( $forms && ! is_wp_error( $forms ) ) {
			$choices = array();
			foreach ( $forms as $form ) {
				$choices[ $form['id'] ] = $form['title'];
			}
			$field['choices'] = $choices;
		}
	}
	return $field;
}

/**
 * Populate select type form field with ACF defined in settings.
 *
 * @param array $field The field array to populate.
 * @return array The populated field array.
 */
function populate_select_type_form_field( $field ) {
	if ( get_field( 'define_actions_forms', 'option' ) ) {
		$type_forms = get_field( 'define_actions_forms', 'option' );
		if ( $type_forms ) {
			$choices = array();
			foreach ( $type_forms as $type ) {
				$choices[$type['name']] = $type['name'];
			}
			$field['choices'] = $choices;
		}
	}

	return $field;
}

/**
 * Populate select zoho action field with ACF defined in settings.
 *
 * @param array $field The field array to populate.
 * @return array The populated field array.
 */
function populate_select_zoho_action_field( $field ) {
	if ( get_field( 'zoho_actions', 'option' ) ) {
		$actions_forms = get_field( 'zoho_actions', 'option' );
		if ( $actions_forms ) {
			$choices = array();
			foreach ( $actions_forms as $action ) {
				$choices[$action['action_name']] = $action['action_name'];
			}
			$field['choices'] = $choices;
		}
	}

	return $field;
}
