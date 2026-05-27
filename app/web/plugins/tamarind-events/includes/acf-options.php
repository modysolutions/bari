<?php
/**
 * ACF Options for Events
 *
 * @package Tamarind_Events
 */

namespace tamarind_events;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Registers the ACF Options fields.
 */
if ( function_exists( 'acf_add_options_page' ) ) {
	add_action( 'acf/init', __NAMESPACE__ . '\register_events_acf_fields' );
}

/**
 * Registers the ACF fields for the Events post type.
 * This function adds a local field group with a URL field for related links.
 * @return void
 */
function register_events_acf_fields(): void {
	$event_place_name = array(
		'key'          => 'field_event_place_name',
		'label'        => __( 'Place name', 'tamarind-events' ),
		'name'         => 'event_place_name',
		'type'         => 'text',
		'instructions' => __( 'Add the name of the place where the event will take place.', 'tamarind-events' ),
		'wrapper'      => array(
			'width' => '50',
		),
	);

	$event_website = array(
		'key'          => 'field_event_website',
		'label'        => __( 'Website', 'tamarind-events' ),
		'name'         => 'event_website',
		'type'         => 'url',
		'instructions' => __( 'Add a relevant URL related to this event.', 'tamarind-events' ),
		'placeholder'  => 'https://example.com',
		'wrapper'      => array(
			'width' => '50',
		),
	);

	$event_date_start = array(
		'key'            => 'field_event_date_start',
		'label'          => __( 'Start Date', 'tamarind-events' ),
		'name'           => 'event_date_start',
		'type'           => 'date_picker',
		'instructions'   => __( 'Select the start date for the event.', 'tamarind-events' ),
		'display_format' => 'd/m/Y',
		'return_format'  => 'Y-m-d',
		'wrapper'        => array(
			'width' => '33',
		),
	);

	$event_date_end = array(
		'key'            => 'field_event_date_end',
		'label'          => __( 'End Date', 'tamarind-events' ),
		'name'           => 'event_date_end',
		'type'           => 'date_picker',
		'instructions'   => __( 'Select the end date for the event.', 'tamarind-events' ),
		'display_format' => 'd/m/Y',
		'return_format'  => 'Y-m-d',
		'wrapper'        => array(
			'width' => '33',
		),
	);

	$event_featured = array(
		'key'          => 'field_event_featured',
		'label'        => __( 'Featured Event', 'tamarind-events' ),
		'name'         => 'event_featured',
		'type'         => 'true_false',
		'ui'           => 1,
		'instructions' => __( 'If checked more options will be shown', 'tamarind-events' ),
		'wrapper'      => array(
			'width' => '33',
		),
	);

	$event_picture = array(
		'key'           => 'field_event_picture',
		'label'         => __( 'Picture or logo', 'tamarind-events' ),
		'name'          => 'event_picture',
		'type'          => 'image',
		'instructions'  => __( 'Add a picture o logo for the event.', 'tamarind-events' ),
		'return_format' => 'array',
		'save_format'   => 'id',
		'preview_size'  => 'medium',
		'wrapper'       => array(
			'width' => '50',
		),
		'conditions'    => array(
			array(
				'field'    => 'field_event_featured',
				'operator' => '==',
				'value'    => '1',
			),
		),
	);

	$event_button_text = array(
		'key'           => 'field_event_button_text',
		'label'         => __( 'Button text', 'tamarind-events' ),
		'name'          => 'event_button_text',
		'type'          => 'text',
		'instructions'  => __( 'Add the text for the button that will be displayed on the event.', 'tamarind-events' ),
		'placeholder'   => __( 'Meet us here', 'tamarind-events' ),
		'default_value' => __( 'Meet us here', 'tamarind-events' ),
		'wrapper'       => array(
			'width' => '50',
		),
		'conditions'    => array(
			array(
				'field'    => 'field_event_featured',
				'operator' => '==',
				'value'    => '1',
			),
		),
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_event_fields',
			'title'    => __( 'Event Additional Fields', 'tamarind-events' ),
			'fields'   => array(
				$event_place_name,
				$event_website,
				$event_date_start,
				$event_date_end,
				$event_featured,
				$event_picture,
				$event_button_text,
			),
			'location' => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'events',
					),
				),
			),
		)
	);
}
