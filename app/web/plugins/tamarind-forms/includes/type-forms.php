<?php
/**
 * Type Forms for Tamarind Forms.
 *
 * @package Tamarind_Forms
 *
 * phpcs:disable WordPress.Files.FileName
 */

namespace tamarind_forms\types;

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'GFAPI' ) ) {
	add_action( 'gform_pre_submission', __NAMESPACE__ . '\validate_email_register_form', 10, 1 );
	add_action( 'gform_pre_submission', __NAMESPACE__ . '\add_hidden_field_submit_key_to_register_form', 10, 1 );
	add_action( 'gform_after_submission', __NAMESPACE__ . '\do_type_form', 10, 2 );
}

/**
 * Validate Email Register Form.
 *
 * @param array $form Form.
 *
 * @return void
 */
function validate_email_register_form( $form ) {

	foreach ( $form['fields'] as $field ) {
		if ( ( 'hidden' === $field->type ) && ( 'type-form' === $field->name ) ) {
			$type_form_value = rgpost( 'input_' . $field['id'] );
		}
		if ( 'email' === $field->type ) {
			$email_value = sanitize_email( rgpost( 'input_' . $field['id'] ) );
		}
	}

	if ( ( 'register-user' === $type_form_value ) && ! empty( $type_form_value ) && ! empty( $email_value ) ) {
		// Check if the email is already registered or contains the '+' symbol.
		if ( email_exists( $email_value ) || username_exists( $email_value ) || strpos( $email_value, '+' ) !== false ) {
			echo esc_html__( 'Email already registered or contains the + symbol', 'tamarind-forms' ) . '<br />' . esc_html( $email_value );
			error_log( '[TamarindForms] Email_already_registered or contains + simbol: ' . $email_value );
			exit;
		}

		// Apply custom corporate email validation.
		$is_corporate_email = \tamarind_forms\validation_hooks\validate_corporate_email( $email_value );
		if ( ! $is_corporate_email['is_valid'] ) {
			echo $is_corporate_email['message'] . ': ' . $email_value;
			error_log( '[TamarindForms] ' . $is_corporate_email['message'] . ': ' . $email_value );
			exit;
		}
	}
}

/**
 * Add Hidden Field Submit Key To Register Form.
 *
 * @param array $form Form.
 *
 * @return array
 */
function add_hidden_field_submit_key_to_register_form( $form ) {

	$id_field_submit_key = '';
	$url_submit          = '';
	$type_form_value     = '';

	foreach ( $form['fields'] as $field ) {
		if ( ( 'hidden' === $field->type ) && ( 'type-form' === $field->name ) ) {
			$type_form_value = rgpost( 'input_' . $field['id'] );
		}
		if ( ( 'hidden' === $field->type ) && ( 'submit-key' === $field->name ) ) {
			$id_field_submit_key = $field['id'];
			$url_submit          = rgpost( 'input_' . $field['id'] );
		}
	}

	if ( ( 'register-user' === $type_form_value ) && ! empty( $type_form_value ) && ! empty( $id_field_submit_key ) && ! empty( $url_submit ) ) {
		$register_key = get_field( 'register_key', 'option' );
		// This is to prevent the form from being submitted by bots.
		$_POST[ 'input_' . $id_field_submit_key ] = $url_submit . '?key=' . $register_key;
	}

	return $form;
}

/**
 * Do Type Form.
 *
 * @param array $entry Entry.
 * @param array $form Form.
 *
 * @return void
 */
function do_type_form( $entry, $form ) {

	foreach ( $form['fields'] as $field ) {

		if ( 'hidden' === $field->type ) {

			switch ( $field->name ) {
				case 'type-form':
					$type_form_value = rgar( $entry, $field['id'] );
					switch ( $type_form_value ) {
						case 'download':
							$button = download_file( $entry, $form['fields'] );
							if ( ! empty( $button ) ) {
								echo $button;
							}
							break;
						case 'china-download':
							$button_china = get_field( 'field_tamarind_forms_field_china_label_download_button', 'option' );
							$button       = download_file( $entry, $form['fields'], $button_china );
							if ( ! empty( $button ) ) {
								echo $button;
							}
							break;
						case 'show-media':
							show_media( $entry, $form['fields'] );
							break;
						case 'register-user':
							\tamarind_forms\users\register\registrar_usuario( $entry, $form['fields'] );
							break;
						case 'update-user':
							\tamarind_forms\users\update\update_user( $entry, $form['fields'] );
							break;
						default:
							// echo 'There is no special behavior for ' . $field->name . ' TYPE FORM!!!!<br />';
					}
					break;

				default:
					// echo 'THERE IS NO NAME IN HIDDEN!!!! ' . $field->name . '<br />';
			}
		}
	}

	// TODO: Remove this option from the type_forms file and create one for Zoho.
	// Send the form data to Zoho Submissions.
	if ( get_field( 'zoho_submissions', 'option' ) &&
	( 'reset-password' !== $type_form_value ) &&
	( 'forgot-password' !== $type_form_value ) &&
	( 'change-password' !== $type_form_value ) &&
	( 'update-user' !== $type_form_value ) ) {
		\tamarind_forms\zoho\send_to_zoho_submission( $entry, $form );
	}
}

/**
 * Download File.
 *
 * @param array  $entry Entry.
 * @param array  $fields Fields.
 * @param string $link_text_china Optional link text for China.
 *
 * @return string
 */
function download_file( $entry, $fields, $link_text_china = '' ) {

	$open_download = false;
	foreach ( $fields as $field ) {
		if ( ( 'hidden' === $field->type ) && ( 'file' === $field->name ) && ( ! $open_download ) ) {
			$file = rgar( $entry, $field->id );

			if ( ! empty( $file ) ) {
				echo '<script>
					const tab = window.open("about:blank");
					tab.location = "' . $file . '";
					tab.focus();
				</script>';
			}
			$open_download = true;
		}
	}

	$link_text = 'Download';
	if ( ! empty( $link_text_china ) ) {
		$link_text = $link_text_china;
	}

	return '<a href="' . $file . '">' . $link_text . '</a>';
}

/**
 * Show Media content when submit form.
 *
 * @param array $entry Entry.
 * @param array $fields Fields.
 *
 * @return void
 */
function show_media( $entry, $fields ) {

	foreach ( $fields as $field ) {
		if ( ( 'hidden' === $field->type ) && ( 'file' === $field->name ) ) {
			$file = rgar( $entry, $field->id );

			if ( ! empty( $file ) ) {
				echo '<div class="module-downloadform-form-media">' . $file . '</div>';
			}
		}
	}
}
