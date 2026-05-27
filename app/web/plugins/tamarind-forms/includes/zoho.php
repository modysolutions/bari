<?php
/**
 * Zoho Options for Tamarind Forms.
 *
 * @package Tamarind_Forms
 *
 * phpcs:disable WordPress.Files.FileName
 */

namespace tamarind_forms\zoho;

defined( 'ABSPATH' ) || exit;

/**
 * Register the Zoho CRM Addon.
 *
 * @param array $addons The registered addons.
 *
 * @return array
 */
add_filter(
	'gform_is_feed_asynchronous',
	function ( $is_asynchronous, $feed ) {
		$target_slug = 'gravityformszohocrm';
		if ( rgar( $feed, 'addon_slug' ) === $target_slug ) {
			$is_asynchronous = false;
		}

		return $is_asynchronous;
	},
	10,
	2
);


/**
 * Processes a form submission and sends data to Zoho CRM.
 *
 * This function takes the entry and form data, maps them to Zoho-compatible fields,
 * and sends the resulting payload to Zoho CRM using a pre-configured endpoint.
 *
 * @param array $entry The submitted entry data from the form.
 * @param array $form The form definition and metadata.
 *
 * @return void
 */
function send_to_zoho_submission( array $entry, array $form ) : void {
	$json_data = [
		'lead_owner' => 'Erik Galavis',
		'lead_status' => 'New'
	];

	foreach ( $form['fields'] as $field ) {
		$label = strtolower( str_replace( ' ', '_', $field->label ) );
		$field_id = $field->id;

		if ( $label === 'type_form' ) {
			continue;
		}

		if ( $field->type === 'name' ) {
			$first_name = rgar( $entry, "{$field_id}.3" );
			$last_name = rgar( $entry, "{$field_id}.6" );

			if ( ! empty( $first_name ) ) {
				$json_data['first_name'] = $first_name;
			}
			if ( ! empty( $last_name ) ) {
				$json_data['last_name'] = $last_name;
			}
		} else {
			$value = rgar( $entry, $field_id );

			if ( ! empty( $value ) ) {
				if ( in_array( $label, ['do_you_have_any_questions_you_would_like_to_ask?', 'comment/feedback'] ) ) {
					$json_data['description'] = $value;
				} elseif ( $label === 'how_did_you_hear_about_us?' ) {
					$json_data['lead_source'] = $value;
				} elseif ( $label === 'user_email' ) {
					$json_data['corporate_email'] = $value;
				} else {
					$json_data[ $label ] = $value; // AÃ±ade el campo al JSON
				}
			}
		}
	}

	if(function_exists('\tamarind_base\send_payload_to_zoho')) {
		$zoho_config = array(
			'payload' => json_encode( $json_data ),
			'function' => 'cc_triage_endpoint',
		);
		\tamarind_base\send_payload_to_zoho($zoho_config);
	}
}
