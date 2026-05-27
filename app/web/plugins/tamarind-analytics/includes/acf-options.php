<?php
/**
 * ACF Fields Options for Tamarind Analytics.
 *
 * @package Tamarind_Analytics
 *
 * phpcs:disable WordPress.Files.FileName
 * phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed
 */

namespace tamarind_analytics;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the ACF fields.
 */
if ( function_exists( 'acf_add_options_page' ) ) {
	add_filter( 'acf/init', __NAMESPACE__ . '\register_acf_fields' );
}

/**
 * Registers the ACF fields for Options Page.
 *
 * @return void
 */
function register_acf_fields(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_options_sub_page(
		array(
			'page_title'  => 'Google Analytics Settings',
			'menu_title'  => 'Google Analytics',
			'menu_slug'   => 'tm-google-analytics',
			'capability'  => 'manage_options',
			'parent_slug' => 'tamarind-base',
			'redirect'    => false,
		)
	);

	$gtag_pref = array(
		'key'           => 'field_623da91c66497',
		'label'         => __( 'Google tag events', 'tm-google-analytics' ),
		'name'          => 'gtag_pref',
		'type'          => 'true_false',
		'default_value' => 0,
		'ui'            => 1,
		'ui_on_text'    => __( 'GTM tracking events', 'tm-google-analytics' ),
		'ui_off_text'   => __( 'GA tracking events', 'tm-google-analytics' ),
	);

	$ua_tracking_id = array(
		'key'           => 'tm_ga_ua_tracking_id',
		'label'         => __( 'GA UA Tracking ID', 'tm-google-analytics' ),
		'name'          => 'ga_ua_tracking_id',
		'type'          => 'text',
		'wrapper'       => array(
			'width' => '50',
		),
		'default_value' => 'UA-49606656-1',
	);

	$ua_domain = array(
		'key'           => 'tm_ga_ua_domain',
		'label'         => __( 'GA UA Domain', 'tm-google-analytics' ),
		'name'          => 'ga_ua_domain',
		'type'          => 'text',
		'wrapper'       => array(
			'width' => '50',
		),
		'default_value' => 'ecigintelligence.com',
	);

	$gtm_container_id = array(
		'key'           => 'tm_gtm_container_id',
		'label'         => __( 'GTM Container ID', 'tm-google-analytics' ),
		'name'          => 'gtm_container_id',
		'type'          => 'text',
		'wrapper'       => array(
			'width' => '50',
		),
		'default_value' => 'GTM-NBRZWRN',
	);

	$consent_wait_for_update_ms = array(
		'key'           => 'tm_consent_wait_for_update_ms',
		'label'         => __( 'Consent wait for update (ms)', 'tm-google-analytics' ),
		'name'          => 'consent_wait_for_update_ms',
		'type'          => 'number',
		'wrapper'       => array(
			'width' => '50',
		),
		'default_value' => 2000,
	);

	// Register the ACF fields.
	$acf_fields = array(
		$gtag_pref,
		$ua_tracking_id,
		$ua_domain,
		$gtm_container_id,
		$consent_wait_for_update_ms,
	);

	acf_add_local_field_group(
		array(
			'key'      => 'tm_group_google_analytics_options',
			'title'    => __( 'Google Analytics Group', 'tm-google-analytics' ),
			'fields'   => $acf_fields,
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'tm-google-analytics',
					),
				),
			),
			'style'    => 'default',
		)
	);
}
