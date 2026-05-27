<?php
/**
 * ACF User Fields for Tamarind Forms.
 *
 * @package Tamarind_Forms
 *
 * phpcs:disable WordPress.Files.FileName
 */

namespace tamarind_forms\users\acf_users;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the ACF fields.
 */
if ( function_exists( 'acf_add_local_field_group' ) ) {
	add_filter( 'acf/init', __NAMESPACE__ . '\add_user_acf_fields' );
}

/**
 * ACF User Fields for Tamarind Forms.
 *
 * @return void
 */
function add_user_acf_fields() {
	// Campo Select para Department.
	$field_job_title = array(
		'key'           => 'field_job_title',
		'label'         => __( 'Department', 'tamarind-forms' ),
		'name'          => 'job_title',
		'type'          => 'select',
		'instructions'  => 'Select the department.',
		'required'      => 0,
		'choices'       => array(), // Se completará dinámicamente.
		'wrapper'       => array(
			'width' => '33%',
		),
		'allow_null'    => 1,
		'multiple'      => 0,
		'ui'            => 1,
		'return_format' => 'value',
		'ajax'          => 0,
		'placeholder'   => 'Select Department',
	);

	// Campo Select para Industry.
	$field_industry_user = array(
		'key'           => 'field_industry_user',
		'label'         => __( 'Industry', 'tamarind-forms' ),
		'name'          => 'industry_user',
		'type'          => 'select',
		'instructions'  => 'Select the industry.',
		'required'      => 0,
		'choices'       => array(), // Se completará dinámicamente.
		'wrapper'       => array(
			'width' => '33%',
		),
		'allow_null'    => 1,
		'multiple'      => 0,
		'ui'            => 1,
		'return_format' => 'value',
		'ajax'          => 0,
		'placeholder'   => '',
	);

	// Campo de texto para Company Name.
	$field_company_name = array(
		'key'     => 'field_company_name',
		'label'   => __( 'Company Name', 'tamarind-forms' ),
		'name'    => 'company_name',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '33%',
		),
	);

	// Campo de texto para Prefix.
	$field_phone_prefix = array(
		'key'     => 'field_phone_prefix',
		'label'   => __( 'Prefix', 'tamarind-forms' ),
		'name'    => 'phone_prefix',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '8%',
		),
	);

	// Campo de texto para Work Telephone Number.
	$field_work_telephone = array(
		'key'     => 'field_work_telephone',
		'label'   => __( 'Work Telephone Number', 'tamarind-forms' ),
		'name'    => 'work_telephone',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '25%',
		),
	);

	// Campo Select para Lead Main Source.
	$field_lead_main_source = array(
		'key'           => 'field_lead_main_source',
		'label'         => __( 'How did you hear about us?', 'tamarind-forms' ),
		'name'          => 'lead_main_source',
		'type'          => 'select',
		'instructions'  => 'Select the lead main source.',
		'required'      => 0,
		'choices'       => array(), // Se completará dinámicamente.
		'wrapper'       => array(
			'width' => '33%',
		),
		'allow_null'    => 1,
		'multiple'      => 0,
		'ui'            => 1,
		'return_format' => 'value',
		'ajax'          => 0,
		'placeholder'   => 'Select Lead Main Source',
	);

	// Campo Select para Lead Action.
	$field_lead_action = array(
		'key'           => 'field_lead_action',
		'label'         => __( 'Lead Action', 'tamarind-forms' ),
		'name'          => 'lead_action',
		'type'          => 'select',
		'instructions'  => '',
		'required'      => 0,
		'choices'       => array(), // Se completará dinámicamente.
		'wrapper'       => array(
			'width' => '33%',
		),
		'allow_null'    => 1,
		'multiple'      => 0,
		'ui'            => 1,
		'return_format' => 'value',
		'ajax'          => 0,
		'placeholder'   => 'Select Lead Action',
	);

	// Campo Checkbox para Preferred Contact Method.
	$field_preferred_contact_method = array(
		'key'           => 'field_preferred_contact_method',
		'label'         => __( 'Preferred Contact Method', 'tamarind-forms' ),
		'name'          => 'preferred_contact_method',
		'type'          => 'checkbox',
		'instructions'  => 'Select your preferred contact methods.',
		'required'      => 0,
		'choices'       => array(
			'Phone'                 => 'Phone',
			'Email'                 => 'Email',
			'Video call'            => 'Video call',
			'Direct message / Chat' => 'Direct message / Chat',
		),
		'allow_custom'  => 0,
		'save_custom'   => 0,
		'default_value' => array(),
		'layout'        => 'vertical',
		'toggle'        => 0,
		'return_format' => 'value',
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'group_tamarind_user_fields',
			'title'                 => 'Tamarind User Fields',
			'fields'                => array(
				$field_company_name,
				$field_phone_prefix,
				$field_work_telephone,
				$field_lead_action,
				$field_job_title,
				$field_industry_user,
				$field_lead_main_source,
				$field_preferred_contact_method,
			),
			'location'              => array(
				array(
					array(
						'param'    => 'user_form',
						'operator' => '==',
						'value'    => 'all',
					),
				),
			),
			'menu_order'            => 1,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen'        => '',
			'active'                => true,
			'description'           => '',
		)
	);
}

/**
 * Get dinamic choices for ACF fields.
 *
 * @param string $gf_field_name The name of the Gravity Forms field.
 * @return array The choices for the ACF field.
 */
function get_dinamic_choices_for_acf( $gf_field_name ) {
	$choices      = array();
	$forms_fields = get_field( 'forms_fields', 'option' );

	if ( $forms_fields ) {
		foreach ( $forms_fields as $form_field ) {
			// Verificar que el 'gf_name' coincida con el campo actual.
			if ( isset( $form_field['gf_name'] ) && $form_field['gf_name'] === $gf_field_name ) {
				// Si coincide, obtener las opciones del campo.
				if ( isset( $form_field['options'] ) ) {
					foreach ( $form_field['options'] as $option ) {
						if ( isset( $option['option_value'] ) && isset( $option['option_name'] ) ) {
							$choices[ $option['option_value'] ] = $option['option_name'];
						}
					}
				}
			}
		}
	}

	return $choices;
}

/**
 * Get Zoho action choices.
 *
 * @return array The choices for the Zoho action field.
 */
function get_zoho_action_choices() {
	$choices      = array();
	$zoho_actions = get_field( 'zoho_actions', 'option' );

	if ( $zoho_actions ) {
		foreach ( $zoho_actions as $action ) {
			if ( isset( $action['action_value'] ) && isset( $action['action_name'] ) ) {
				$choices[ $action['action_value'] ] = $action['action_name'];
			}
		}
	}

	return $choices;
}

// Publicación dinámica para cada campo ACF.
add_filter(
	'acf/load_field/key=field_job_title',
	function ( $field ) {
		$field['choices'] = get_dinamic_choices_for_acf( 'gf_department' );
		return $field;
	}
);

add_filter(
	'acf/load_field/key=field_industry_user',
	function ( $field ) {
		$field['choices'] = get_dinamic_choices_for_acf( 'gf_industry' );
		return $field;
	}
);

add_filter(
	'acf/load_field/key=field_lead_main_source',
	function ( $field ) {
		$field['choices'] = get_dinamic_choices_for_acf( 'gf_lead_main_source' );
		return $field;
	}
);

add_filter(
	'acf/load_field/key=field_lead_action',
	function ( $field ) {
		$field['choices'] = get_zoho_action_choices(); // Lead Action.
		return $field;
	}
);
