<?php
/**
 * Actions when a user updates their data in the User Area
 *
 * @package Tamarind_Forms
 *
 * phpcs:disable WordPress.Files.FileName
 */

namespace tamarind_forms\users\update;

defined( 'ABSPATH' ) || exit;


add_filter( 'gform_pre_render', __NAMESPACE__ . '\check_form_type', 20, 1 );
add_filter( 'gform_field_value', __NAMESPACE__ . '\fill_fields_dynamically', 10, 3 );



// Global variable to store if the form is of type 'update-user'.
$is_update_user_form = false;

/**
 * Check the form type before filling in the values
 *
 * @param array $form Form.
 *
 * @return array
 */
function check_form_type( $form ) {
	global $is_update_user_form;
	// Iterate over the fields to check the 'Type form' field.
	foreach ( $form['fields'] as $field ) {
		if (
			isset( $field->type, $field->label, $field->defaultValue ) &&
			'hidden' === $field->type &&
			'Type form' === $field->label &&
			'update-user' === $field->defaultValue
		) {
			$is_update_user_form = true;
			break;
		}
	}

	return $form;
}


/**
 * Fill fields dynamically if the form is of type 'update-user'
 *
 * @param string $value Field value.
 * @param array  $field Field.
 * @param string $name Field name.
 *
 * @return string
 */
function fill_fields_dynamically( $value, $field, $name ) {
	global $is_update_user_form;

	// Exit if it's not an 'update-user' form.
	if ( ! $is_update_user_form ) {
		return $value;
	}

	// Get the current user ID.
	$user_id = get_current_user_id();

	// If there is no authenticated user, return the default value.
	if ( ! $user_id ) {
		return $value;
	}

	// Get user data.
	$user_data = get_userdata( $user_id );
	$user_meta = get_user_meta( $user_id );

	// Define available dynamic values.
	$user_values = array(
		'gf_email'            => sanitize_email( $user_data->user_email ),
		'gf_firstname'        => sanitize_text_field( $user_data->first_name ),
		'gf_lastname'         => sanitize_text_field( $user_data->last_name ),
		'gf_company'          => isset( $user_meta['company_name'][0] ) ? sanitize_text_field( $user_meta['company_name'][0] ) : '',
		'gf_department'       => isset( $user_meta['job_title'][0] ) ? sanitize_text_field( $user_meta['job_title'][0] ) : '',
		'gf_industry'         => isset( $user_meta['industry_user'][0] ) ? sanitize_text_field( $user_meta['industry_user'][0] ) : '',
		'gf_telephone'        => isset( $user_meta['work_telephone'][0] ) ? sanitize_text_field( $user_meta['work_telephone'][0] ) : '',
		'gf_code'             => isset( $user_meta['phone_prefix'][0] ) ? sanitize_text_field( $user_meta['phone_prefix'][0] ) : '',
		'gf_lead_main_source' => isset( $user_meta['lead_main_source'][0] ) ? sanitize_text_field( $user_meta['lead_main_source'][0] ) : '',
	);

	// Load the values for 'preferred_contact_method'.
	if ( 'User contact preferences' === $field['label'] ) {
		// Get the value from the meta and deserialize if necessary.
		$raw_value = isset( $user_meta['preferred_contact_method'][0] ) ? $user_meta['preferred_contact_method'][0] : '';
		$preferred_contact_method = maybe_unserialize( $raw_value );

		// Validate that it is an array and return it.
		if ( is_array( $preferred_contact_method ) && ! empty( $preferred_contact_method ) ) {
			return $preferred_contact_method;
		}
	}

	// Return the corresponding value for the field name, if it exists.
	return isset( $user_values[$name] ) ? $user_values[$name] : $value;
}


/**
 * Update user data
 *
 * @param array $entry Form entry.
 * @param array $fields Form fields.
 *
 * @return void
 */
function update_user( $entry, $fields ) {
	// Get the current user ID.
	$user_id = get_current_user_id();

	// If there is no authenticated user, exit.
	if ( ! $user_id ) {
		error_log( 'Error: Unauthenticated user trying to update.' );
		return;
	}

	// Initialize variables for other user data.
	$email = $first_name = $last_name = $company = $department = $industry = $country = $phone = $lead_source = $lead_action = '';

	// Detect if the form is the one that updates the preferred contact method.
	$is_preferred_contact_form = false;

	// Extract and assign values from fields.
	foreach ( $fields as $field ) {
		if ( isset( $field->label ) && 'User contact preferences' === $field->label && isset( $field->inputs ) ) {
			$selected_values = array();

			// We loop through the subentries of the checkbox field.
			foreach ( $field->inputs as $input ) {
				$input_id = (string) $input['id']; // input ID.
				$value    = rgar( $entry, $input_id );

				if ( ! empty( $value ) ) {
					$selected_values[] = $value;
				}
			}

			$is_preferred_contact_form = true;

		} elseif ( ! $is_preferred_contact_form ) {
			// Process other fields only if this is not the User contact preferences form.
			if ( isset( $field->type ) && 'email' === $field->type ) {
				$email = sanitize_email( rgar( $entry, $field->id ) );
			}
			if ( isset( $field->type ) && 'name' === $field->type && isset( $field->inputs ) ) {
				foreach ( $field->inputs as $input ) {
					if ( isset( $input['label'] ) && 'First' === $input['label'] ) {
						$first_name = sanitize_text_field( rgar( $entry, $input['id'] ) );
					}
					if ( isset( $input['label'] ) && 'Last' === $input['label'] ) {
						$last_name = sanitize_text_field( rgar( $entry, $input['id'] ) );
					}
				}
			}
			if ( isset( $field->label ) ) {
				switch ( $field->label ) {
					case 'Company Name':
						$company = sanitize_text_field( rgar( $entry, $field->id ) );
						break;
					case 'Department':
						$department = sanitize_text_field( rgar( $entry, $field->id ) );
						break;
					case 'Industry':
						$industry = sanitize_text_field( rgar( $entry, $field->id ) );
						break;
					case 'Country code':
						$country = sanitize_text_field( rgar( $entry, $field->id ) );
						break;
					case 'Work Telephone':
						$phone = sanitize_text_field( rgar( $entry, $field->id ) );
						break;
					case 'How did you hear about us?':
						$lead_source = sanitize_text_field( rgar( $entry, $field->id ) );
						break;
					case 'Zoho lead action':
						$lead_action = sanitize_text_field( rgar( $entry, $field->id ) );
						break;
				}
			}
		}
	}

	if ( $is_preferred_contact_form ) {
		// Update only the preferred contact method.
		if ( ! empty( $selected_values ) ) {
			update_user_meta( $user_id, 'preferred_contact_method', $selected_values );
		}
	} else {
		// Prepare basic user data.
		$userdata = array_filter(
			array(
				'ID'           => $user_id,
				'user_email'   => $email,
				'first_name'   => $first_name,
				'last_name'    => $last_name,
				'display_name' => trim( "$first_name $last_name" ),
			)
		);

		// Update user data.
		$result = wp_update_user( $userdata );

		// Handle update errors.
		if ( is_wp_error( $result ) ) {
			$error_message = $result->get_error_message();
			error_log( 'Error updating user: ' . $error_message );
			return;
		}

		// Update user meta data.
		$meta_data = array(
			'company_name'     => $company,
			'job_title'        => $department,
			'industry_user'    => $industry,
			'phone_prefix'     => $country,
			'work_telephone'   => $phone,
			'lead_main_source' => $lead_source,
			'lead_action'      => $lead_action,
		);

		foreach ( $meta_data as $meta_key => $meta_value ) {
			if ( ! empty( $meta_value ) ) {
				update_user_meta( $user_id, $meta_key, $meta_value );
			}
		}
	}
}
