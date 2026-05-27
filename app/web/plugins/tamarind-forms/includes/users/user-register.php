<?php
/**
 * Actions when a user registers.
 *
 * @package Tamarind_Forms
 *
 * phpcs:disable WordPress.Files.FileName
 */

namespace tamarind_forms\users\register;

defined( 'ABSPATH' ) || exit;

add_action( 'wp_new_user_notification_email', __NAMESPACE__ . '\tamarind_new_user_notification', 10, 3 );

/**
 * Registers a new user based on the submitted form data.
 *
 * @param array $entry Entry.
 * @param array $fields Fields.
 *
 * @return void
 */
function registrar_usuario( $entry, $fields ) {
	// Extract and map form fields.
	foreach ( $fields as $field ) {
		if ( 'email' === $field->type ) {
			$email    = sanitize_email( rgar( $entry, $field->id ) );
			$username = $email;
		}
		if ( 'name' === $field->type ) {
			foreach ( $field->inputs as $input ) {
				if ( 'First' === $input['label'] ) {
					$first_name = sanitize_text_field( rgar( $entry, $input['id'] ) );
				}
				if ( 'Last' === $input['label'] ) {
					$last_name = sanitize_text_field( rgar( $entry, $input['id'] ) );
				}
			}
			$name = $first_name . ' ' . $last_name;
		}
		if ( 'Company Name' === $field['label'] ) {
			$company = sanitize_text_field( rgar( $entry, $field['id'] ) );
		}
		if ( 'Department' === $field['label'] ) {
			$department = sanitize_text_field( rgar( $entry, $field['id'] ) );
		}
		if ( 'Industry' === $field['label'] ) {
			$industry = sanitize_text_field( rgar( $entry, $field['id'] ) );
		}
		if ( 'Country code' === $field['label'] ) {
			$country = sanitize_text_field( rgar( $entry, $field['id'] ) );
		}
		if ( 'Work Telephone' === $field['label'] ) {
			$phone = sanitize_text_field( rgar( $entry, $field['id'] ) );
		}
		if ( 'How did you hear about us?' === $field['label'] ) {
			$lead_source = sanitize_text_field( rgar( $entry, $field['id'] ) );
		}
		if ( 'Zoho lead action' === $field['label'] ) {
			$lead_action = sanitize_text_field( rgar( $entry, $field['id'] ) );
		}
	}

	// Validate data: ensure that required fields are not empty.
	if ( empty( $username ) || empty( $email ) || empty( $first_name ) || empty( $last_name ) ) {
		return;
	}
	// Valid email.
	if ( ! is_email( $email ) ) {
		return;
	}
	// Check if the username or email already exists.
	if ( username_exists( $username ) || email_exists( $email ) ) {
		return;
	}

	// Define the role according to the record type.
	$role = ( 'Register shop' === $lead_action ) ? 'customer' : 'subscriber';

	// Check if you are a customer and use the corresponding method.
	if ( 'customer' === $role ) {
		// Create the WooCommerce client.
		$user_id = wc_create_new_customer(
			$email,
			$username,
			wp_generate_password(),
			array(
				'first_name'   => $first_name,
				'last_name'    => $last_name,
				'display_name' => $name,
				'role'         => $role,
			)
		);

		// Check for an error.
		if ( is_wp_error( $user_id ) ) {
			$error_message = $user_id->get_error_message();
			error_log( 'Error creating user: ' . $error_message );
			return;
		}

		// Autologin the user.
		wp_set_auth_cookie( $user_id );
		wp_set_current_user( $user_id );
	} else {
		// User data.
		$userdata = array(
			'user_login'   => $username,
			'user_email'   => $email,
			'first_name'   => $first_name,
			'last_name'    => $last_name,
			'display_name' => $name,
			'role'         => $role,
		);

		// Add the user.
		$user_id = wp_insert_user( $userdata );

		if ( is_wp_error( $user_id ) ) {
			$error_message = $user_id->get_error_message();
			error_log( 'Error creating user: ' . $error_message );
			return;
		}
	}

	// Add user meta data.
	if ( ! empty( $company ) ) {
		update_user_meta( $user_id, 'company_name', $company );
	}
	if ( ! empty( $department ) ) {
		update_user_meta( $user_id, 'job_title', $department );
	}
	if ( ! empty( $industry ) ) {
		update_user_meta( $user_id, 'industry_user', $industry );
	}
	if ( ! empty( $country ) ) {
		update_user_meta( $user_id, 'phone_prefix', $country );
	}
	if ( ! empty( $phone ) ) {
		update_user_meta( $user_id, 'work_telephone', $phone );
	}
	if ( ! empty( $lead_source ) ) {
		update_user_meta( $user_id, 'lead_main_source', $lead_source );
	}
	if ( ! empty( $lead_action ) ) {
		update_user_meta( $user_id, 'lead_action', $lead_action );
	}

	// Notify the user about their registration.
	wp_new_user_notification( $user_id, null, 'both' );
}

/**
 * Custom Welcome Email to the New User with reset password link.
 *
 * @param array   $wp_new_user_notification_email The email parameters.
 * @param WP_User $user The user object.
 * @param string  $blogname The name of the site.
 * @return array Modified email parameters.
 */
function tamarind_new_user_notification( $wp_new_user_notification_email, $user, $blogname ) {
	$blogname = get_bloginfo( 'name' );
	$key      = get_password_reset_key( $user );
	if ( is_wp_error( $key ) ) {
		return $wp_new_user_notification_email;
	}

	// Get the reset URL from the ACF, or use the default URL.
	$reset_page_url = get_field( 'user_label_reset_page', 'option' );
	$reset_base_url = $reset_page_url ? $reset_page_url : network_site_url( 'wp-login.php?action=rp' );

	// Generate reset URL with the appropriate base.
	$password_url = add_query_arg(
		array(
			'key'   => $key,
			'login' => rawurlencode( $user->user_login ),
		),
		$reset_base_url
	);

	$message  = sprintf( __( 'Username: %s', 'tamarind-forms' ), $user->user_login ) . '<br><br>';
	$message .= __( 'To set your password, visit the following address:', 'tamarind-forms' ) . '<br><br>';
	$message .= "<a href='" . $password_url . "'>" . $password_url . '</a><br><br>';
	$message .= wp_login_url() . '<br><br>';
	$message .= __( 'Thanks', 'tamarind-forms' ) . '<br>';
	$message .= $blogname . '<br>';

	// Set email parameters.
	$subject = sprintf( __( 'Your login details - Access to %s', 'tamarind-forms' ), $blogname );
	$headers = array( 'Content-Type: text/html; charset=UTF-8' );

	// Apply email text domain and send email.
	$wp_new_user_notification_email['message'] = $message;
	$wp_new_user_notification_email['subject'] = $subject;
	$wp_new_user_notification_email['headers'] = $headers;

	return $wp_new_user_notification_email;
}
