<?php
/**
 * Gravity Forms Classes for Tamarind Forms.
 *
 * @package Tamarind_Forms
 *
 * phpcs:disable WordPress.Files.FileName
 */

namespace tamarind_forms\validation_hooks;

use WP_REST_Request;

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'GFAPI' ) ) {
	// add_filter( 'gform_field_validation', __NAMESPACE__ . '\validate_corporate_email', 10, 1 );
	// add_filter('gform_field_validation', __NAMESPACE__ . '\validate_required_fields', 10, 4);
	// TODO: check if this filter is necessary or is already applied from the custom_validate_field	add_filter( 'gform_field_validation', __NAMESPACE__ . '\validate_input_fields_length', 10, 2 );.
	add_action( 'rest_api_init', __NAMESPACE__ . '\register_endpoint_validate' );
}

/**
 * Validates that the field is required
 *
 * @param array  $result Validation result.
 * @param mixed  $value Field value.
 * @param array  $form Form.
 * @param object $field Field to validate.
 *
 * @return array Validation result.
 */
function validate_required_fields( $result, $value, $form, $field)  {
	if ( $field->isRequired && empty( $value ) ) {
		$result['is_valid'] = false;
		$result['message']  = __( 'This field is required.', 'tamarind-forms' );
	}
	return $result;
}

/**
 * Validates that the email field is a corporate email.
 *
 * @param string $value The email value to validate.
 * @return array An array containing the validation result and message.
 */
function validate_corporate_email( $value ) {

	$result = array(
		'is_valid' => true,
		'message'  => '',
	);

	// Extract the domain from the entered email and convert to lowercase.
	$email_domain = strtolower( substr( strrchr( $value, '@' ), 1 ) );

	// Get the list of blocked domains from ACF.
	$blocked_domains = array();
	if ( have_rows( 'blocked_domains', 'option' ) ) {
		while ( have_rows( 'blocked_domains', 'option' ) ) {
			the_row();
			$blocked_domains[] = strtolower( get_sub_field( 'blocked_domain' ) ); // Convert to lowercase for case-insensitive comparison.
		}
	}

	// Check if the email domain is on the blocked domains list.
	foreach ( $blocked_domains as $blocked_domain ) {
		// If the blocked domain has no extension, validate any domain that begins with that value.
		if ( strpos( $blocked_domain, '.' ) === false ) {
			// The blocked domain has no extension.
			if ( strpos( $email_domain, $blocked_domain ) === 0 ) {
				$result['is_valid'] = false;
				$result['message']  = __( 'Sorry, only corporate Email', 'tamarind-forms' );
				return $result;
			}
		} elseif ( $email_domain === $blocked_domain ) {
			// Domain matches exactly.
			$result['is_valid'] = false;
			$result['message']  = __( 'Sorry, only corporate Email', 'tamarind-forms' );
			return $result;
		}
	}
	return $result;
}


/**
 * Validates the maximum character size of text fields.
 *
 * @param string|array $value The value of the field to validate.
 * @param object       $field The field object containing the type and other properties.
 * @return array An array containing the validation result and message.
 */
function validate_input_fields_length( $value, $field ) {

	$result = array(
		'is_valid' => true,
		'message'  => '',
	);

	// Input types we want to validate.
	$input_types_to_validate = array( 'text', 'email', 'url', 'tel', 'name' );

	if ( is_object( $field ) && property_exists( $field, 'type' ) && in_array( $field->type, $input_types_to_validate ) ) {
		$max_length = ( get_field( 'max-size', 'option' ) ) ? get_field( 'max-size', 'option' ) : 250;
		// if value is array - 'name' field.
		if ( is_array( $value ) ) {
			foreach ( $value as $val ) {
				if ( strlen( $val ) > $max_length ) {
					$result['is_valid'] = false;
					$result['message']  = 'The field cannot have more than ' . $max_length . ' characters.';
				}
			}
		} else {
			// if value is string.
			if ( strlen( $value ) > $max_length ) {
				$result['is_valid'] = false;
				$result['message']  = 'The field cannot have more than ' . $max_length . ' characters.';
			}
		}
	}
	return $result;
}

/**
 * Registers the REST API endpoint for field validation.
 *
 * This endpoint allows for custom validation of Gravity Forms fields via a POST request.
 * It checks the field value against the form's validation rules and returns the result.
 */
function register_endpoint_validate(): void {
	register_rest_route(
		'custom/v1',
		'/validate-field',
		array(
			'methods'  => 'POST',
			'callback' => __NAMESPACE__ . '\custom_validate_field',
			'permission_callback' => '__return_true',
		),
	);
}

/**
 * Custom validation function for Gravity Forms fields.
 *
 * @param WP_REST_Request $request The REST request object containing the form ID, field ID, and field value.
 * @return WP_REST_Response The response containing the validation result.
 */
function custom_validate_field( WP_REST_Request $request ) {
	$form_id     = $request->get_param( 'form_id' );
	$field_id    = $request->get_param( 'field_id' );
	$field_value = $request->get_param( 'field_value' );

	// Verify that the required parameters are present.
	if ( ! $form_id || ! $field_id || ! isset( $field_value ) ) {
		return new \WP_REST_Response(
			array(
				'error'   => true,
				'message' => 'Incomplete parameters.',
			),
			400
		); // 400 Bad Request.
	}

	// Exclude validations for submit buttons and other invalid parameters.
	if ( 'button' === $field_id || 'submit' === $form_id ) {
		return new \WP_REST_Response(
			array(
				'error'   => false,
				'message' => 'No validation required for this field.',
			),
			200
		); // 200 OK
	}

	// Obtain the form using GFAPI.
	$form = \GFAPI::get_form( $form_id );
	if ( is_wp_error( $form ) || ! $form ) {
		return new \WP_REST_Response(
			array(
				'error'   => true,
				'message' => 'Form not found.',
			),
			404
		); // 404 Not Found.
	}

	// Get the specific field from the form.
	$field = \GFAPI::get_field( $form, $field_id );
	if ( ! $field ) {
		return new \WP_REST_Response(
			array(
				'error'   => true,
				'message' => 'Field not found in the form.',
			),
			404
		); // 404 Not Found
	}

	// Validate the field using the field's validate method.
	$field->failed_validation = false; // Reset validation status.
	$entry                    = array( $field_id => $field_value );
	if ( 'name' !== $field->type ) {
		// Exclude Name because it is a special field and causes conflicts.
		$field->validate( $field_value, $form, $entry );
	}

	// Apply email validation only if 'type-form' is 'register-user'.
	if ( 'email' === $field->type && 'register-user' === \tamarind_forms\display_form\get_type_form( $form ) ) {
		// Check if the email is already registered.
		if ( email_exists( $field_value ) ) {
			$field->failed_validation  = true;
			$field->validation_message = __( 'This email address is already registered.', 'tamarind-forms' );
		}
		// Check if the username (same as email) is already registered.
		if ( username_exists( $field_value ) ) {
			$field->failed_validation  = true;
			$field->validation_message = __( 'This email is already used as a username. Please choose another email.', 'tamarind-forms' );
		}

		// Check if the field contains the '+' symbol.
		if ( strpos( $field_value, '+' ) !== false ) {
			$field->failed_validation  = true;
			$field->validation_message = __( 'The "+" symbol is not allowed.', 'tamarind-forms' );
		}
	}

	// Apply custom corporate email validation.
	if ( 'email' === $field->type ) {
		$is_corporate_email = validate_corporate_email( sanitize_email( $field_value ) );
		if ( ! $is_corporate_email['is_valid'] ) {
			$field->failed_validation  = true;
			$field->validation_message = $is_corporate_email['message'];
		}
	}

	// Apply custom field length validation.
	$is_valid_length = validate_input_fields_length( $field_value, $field );
	if ( ! $is_valid_length['is_valid'] ) {
		$field->failed_validation  = true;
		$field->validation_message = $is_valid_length['message'];
	}

	// Check if the field is required and empty.
	if ( $field->isRequired ) {
		if ( is_array( $field_value ) ) {
			if ( empty( $field_value ) ) {
				$field->failed_validation  = true;
				$field->validation_message = __( 'This field is required.', 'tamarind-forms' );
			}
		} else {
			if ( empty( $field_value ) ) {
				$field->failed_validation  = true;
				$field->validation_message = __( 'This field is required.', 'tamarind-forms' );
			}
		}
	}

	// Prepare the response based on the validation result.
	if ( $field->failed_validation ) {
		$message = $field->validation_message ?: __( 'Invalid field', 'tamarind-forms' );
		return new \WP_REST_Response(
			array(
				'error'    => true,
				'field_id' => $field_id,
				'message'  => $message,
			),
			200
		);
	}

	return new \WP_REST_Response(
		array(
			'error'    => false,
			'field_id' => $field_id,
			'message'  => 'Valid field.',
		),
		200
	);
}
