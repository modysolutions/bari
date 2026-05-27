<?php
/**
 * ACF Options for WhatsApp Widget.
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\whatsapp;

defined( 'ABSPATH' ) || exit;

add_action( 'acf/init', __NAMESPACE__ . '\register_whatsapp_acf_fields' );

/**
 * Register ACF fields and options page for the WhatsApp widget.
 */
function register_whatsapp_acf_fields(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$general_messages_tab = array(
		'key'       => 'field_67f0000000001',
		'label'     => __( 'General & Messages', TM_LANGUAGE_DOMAIN ),
		'name'      => '',
		'type'      => 'tab',
		'placement' => 'left',
	);

	$business_hours_tab = array(
		'key'       => 'field_67f0000000002',
		'label'     => __( 'Business Hours', TM_LANGUAGE_DOMAIN ),
		'name'      => '',
		'type'      => 'tab',
		'placement' => 'left',
	);

	$legal_tab = array(
		'key'       => 'field_67f0000000003',
		'label'     => __( 'Legal', TM_LANGUAGE_DOMAIN ),
		'name'      => '',
		'type'      => 'tab',
		'placement' => 'left',
	);

	// -------------------------------------------------------------------------
	// Group 1: General & Messages
	// -------------------------------------------------------------------------

	$whatsapp_enabled = array(
		'key'           => 'field_67f0001000001',
		'label' => __( 'Enable WhatsApp Widget', TM_LANGUAGE_DOMAIN ),
		'name'          => 'whatsapp_enabled',
		'type'          => 'true_false',
		'instructions' => __( 'Activate the floating WhatsApp widget on this site.', TM_LANGUAGE_DOMAIN ),
		'wrapper'       => array( 'width' => '20' ),
		'default_value' => 0,
		'ui'            => 1,
	);

	$whatsapp_phone_number = array(
		'key'          => 'field_67f0001000002',
		'label' => __( 'WhatsApp Phone Number', TM_LANGUAGE_DOMAIN ),
		'name'         => 'whatsapp_phone_number',
		'type'         => 'text',
		'instructions' => __( 'Full number with country code, no spaces or symbols (e.g. 34612345678).', TM_LANGUAGE_DOMAIN ),
		'wrapper'      => array( 'width' => '40' ),
		'placeholder' => __( '34612345678', TM_LANGUAGE_DOMAIN ),
	);

	$whatsapp_position = array(
		'key'     => 'field_67f0001000003',
		'label' => __( 'Widget Position', TM_LANGUAGE_DOMAIN ),
		'name'    => 'whatsapp_position',
		'type'    => 'select',
		'wrapper' => array( 'width' => '20' ),
		'choices' => array(
			'right' => __( 'Right', TM_LANGUAGE_DOMAIN ),
			'left' => __( 'Left', TM_LANGUAGE_DOMAIN ),
		),
		'default_value' => 'right',
		'return_format' => 'value',
	);

	$whatsapp_appearance_delay = array(
		'key'           => 'field_67f0001000004',
		'label' => __( 'Appearance Delay (seconds)', TM_LANGUAGE_DOMAIN ),
		'name'          => 'whatsapp_appearance_delay',
		'type'          => 'number',
		'instructions' => __( 'Seconds after page load before the widget becomes visible. Use 0 to show immediately.', TM_LANGUAGE_DOMAIN ),
		'wrapper'       => array( 'width' => '20' ),
		'default_value' => 3,
		'min'           => 0,
		'step'          => 1,
	);

	$whatsapp_device_visibility = array(
		'key'     => 'field_67f0001000005',
		'label' => __( 'Device Visibility', TM_LANGUAGE_DOMAIN ),
		'name'    => 'whatsapp_device_visibility',
		'type'    => 'select',
		'wrapper' => array( 'width' => '20' ),
		'choices' => array(
			'all' => __( 'All devices', TM_LANGUAGE_DOMAIN ),
			'mobile' => __( 'Mobile only', TM_LANGUAGE_DOMAIN ),
			'desktop' => __( 'Desktop only', TM_LANGUAGE_DOMAIN ),
		),
		'default_value' => 'all',
		'return_format' => 'value',
	);

	$whatsapp_fab_text = array(
		'key'          => 'field_67f0001000013',
		'label'        => __( 'Floating Button Text', 'tamarind-base' ),
		'name'         => 'whatsapp_fab_text',
		'type'         => 'text',
		'instructions' => __( 'Optional text shown before the WhatsApp logo in the floating button. Leave empty to keep the current icon-only button.', 'tamarind-base' ),
		'wrapper'      => array( 'width' => '40' ),
		'placeholder'  => __( 'Chat with us', 'tamarind-base' ),
	);

	$whatsapp_welcome_title = array(
		'key'          => 'field_67f0001000006',
		'label' => __( 'Welcome Title', TM_LANGUAGE_DOMAIN ),
		'name'         => 'whatsapp_welcome_title',
		'type'         => 'text',
		'instructions' => __( 'Heading shown in the popup window.', TM_LANGUAGE_DOMAIN ),
		'wrapper'      => array( 'width' => '50' ),
		'placeholder' => __( 'Chat with us', TM_LANGUAGE_DOMAIN ),
	);

	$whatsapp_welcome_message = array(
		'key'          => 'field_67f0001000007',
		'label' => __( 'Welcome Message', TM_LANGUAGE_DOMAIN ),
		'name'         => 'whatsapp_welcome_message',
		'type'         => 'textarea',
		'instructions' => __( 'Greeting message displayed inside the popup.', TM_LANGUAGE_DOMAIN ),
		'wrapper'      => array( 'width' => '50' ),
		'rows'         => 3,
	);

	$whatsapp_avatar_logo = array(
		'key'           => 'field_67f0001000014',
		'label'         => __( 'Chat Avatar Logo', 'tamarind-base' ),
		'name'          => 'whatsapp_avatar_logo',
		'type'          => 'image',
		'instructions'  => __( 'Optional circular avatar logo shown in the chat header. If empty, the widget will try to use the default logo for the active site.', 'tamarind-base' ),
		'wrapper'       => array( 'width' => '30' ),
		'return_format' => 'id',
		'preview_size'  => 'thumbnail',
		'library'       => 'all',
		'mime_types'    => 'jpg,jpeg,png,webp,svg',
	);

	$whatsapp_cta_text = array(
		'key'          => 'field_67f0001000008',
		'label' => __( 'CTA Button Text', TM_LANGUAGE_DOMAIN ),
		'name'         => 'whatsapp_cta_text',
		'type'         => 'text',
		'instructions' => __( 'Label for the button that opens WhatsApp.', TM_LANGUAGE_DOMAIN ),
		'wrapper'      => array( 'width' => '30' ),
		'placeholder' => __( 'Start Chat', TM_LANGUAGE_DOMAIN ),
	);

	$whatsapp_prefilled_message = array(
		'key'          => 'field_67f0001000009',
		'label' => __( 'Pre-filled Message', TM_LANGUAGE_DOMAIN ),
		'name'         => 'whatsapp_prefilled_message',
		'type'         => 'textarea',
		'instructions' => __( 'Message automatically loaded in WhatsApp when the user opens the chat. Include the vertical name to help the agent identify the source.', TM_LANGUAGE_DOMAIN ),
		'wrapper'      => array( 'width' => '70' ),
		'rows'         => 2,
		'placeholder' => __( 'Hello! I found you through ECig Intelligence and I\'d like to know more.', TM_LANGUAGE_DOMAIN ),
	);

	$whatsapp_away_message = array(
		'key'          => 'field_67f0001000010',
		'label' => __( 'Away Message', TM_LANGUAGE_DOMAIN ),
		'name'         => 'whatsapp_away_message',
		'type'         => 'textarea',
		'instructions' => __( 'Shown instead of the welcome message when the team is outside business hours.', TM_LANGUAGE_DOMAIN ),
		'wrapper'      => array( 'width' => '50' ),
		'rows'         => 3,
		'placeholder' => __( 'We\'re currently away. Leave us a message and we\'ll get back to you.', TM_LANGUAGE_DOMAIN ),
	);

	$whatsapp_away_next_availability = array(
		'key'          => 'field_67f0001000011',
		'label' => __( 'Next Availability Text', TM_LANGUAGE_DOMAIN ),
		'name'         => 'whatsapp_away_next_availability',
		'type'         => 'text',
		'instructions' => __( 'Optional short note about when the team will be back (e.g. "Back Monday 9:00 AM").', TM_LANGUAGE_DOMAIN ),
		'wrapper'      => array( 'width' => '50' ),
		'placeholder' => __( 'Back Monday at 9:00 AM (GMT)', TM_LANGUAGE_DOMAIN ),
	);

	$whatsapp_away_cta_text = array(
		'key'          => 'field_67f0001000012',
		'label' => __( 'Away CTA Button Text', TM_LANGUAGE_DOMAIN ),
		'name'         => 'whatsapp_away_cta_text',
		'type'         => 'text',
		'instructions' => __( 'Label for the button that opens WhatsApp when the team is outside business hours.', TM_LANGUAGE_DOMAIN ),
		'wrapper'      => array( 'width' => '50' ),
		'placeholder' => __( 'Leave a Message', TM_LANGUAGE_DOMAIN ),
	);

	// -------------------------------------------------------------------------
	// Group 2: Business Hours
	// -------------------------------------------------------------------------

	$whatsapp_timezone = array(
		'key'          => 'field_67f0002000001',
		'label' => __( 'Timezone', TM_LANGUAGE_DOMAIN ),
		'name'         => 'whatsapp_timezone',
		'type'         => 'text',
		'instructions' => __( 'IANA timezone identifier used to evaluate business hours (e.g. Europe/London, America/New_York).', TM_LANGUAGE_DOMAIN ),
		'wrapper'      => array( 'width' => '30' ),
		'placeholder' => __( 'Europe/London', TM_LANGUAGE_DOMAIN ),
	);

	$whatsapp_schedule = array(
		'key'          => 'field_67f0002000002',
		'label' => __( 'Weekly Schedule', TM_LANGUAGE_DOMAIN ),
		'name'         => 'whatsapp_schedule',
		'type'         => 'repeater',
		'instructions' => __( 'Define open/close times for each day of the week.', TM_LANGUAGE_DOMAIN ),
		'wrapper'      => array( 'width' => '70' ),
		'layout'       => 'table',
		'button_label' => __( 'Add Day', TM_LANGUAGE_DOMAIN ),
		'sub_fields'   => array(
			array(
				'key'     => 'field_67f0002000003',
				'label' => __( 'Day', TM_LANGUAGE_DOMAIN ),
				'name'    => 'whatsapp_day',
				'type'    => 'select',
				'wrapper' => array( 'width' => '20' ),
				'choices' => array(
					'monday' => __( 'Monday', TM_LANGUAGE_DOMAIN ),
					'tuesday' => __( 'Tuesday', TM_LANGUAGE_DOMAIN ),
					'wednesday' => __( 'Wednesday', TM_LANGUAGE_DOMAIN ),
					'thursday' => __( 'Thursday', TM_LANGUAGE_DOMAIN ),
					'friday' => __( 'Friday', TM_LANGUAGE_DOMAIN ),
					'saturday' => __( 'Saturday', TM_LANGUAGE_DOMAIN ),
					'sunday' => __( 'Sunday', TM_LANGUAGE_DOMAIN ),
				),
				'return_format' => 'value',
			),
			array(
				'key'           => 'field_67f0002000004',
				'label' => __( 'Open', TM_LANGUAGE_DOMAIN ),
				'name'          => 'whatsapp_is_open',
				'type'          => 'true_false',
				'wrapper'       => array( 'width' => '10' ),
				'default_value' => 1,
				'ui'            => 1,
			),
			array(
				'key'           => 'field_67f0002000005',
				'label' => __( 'Open Time', TM_LANGUAGE_DOMAIN ),
				'name'          => 'whatsapp_open_time',
				'type'          => 'time_picker',
				'wrapper'       => array( 'width' => '35' ),
				'display_format' => 'H:i',
				'return_format'  => 'H:i',
			),
			array(
				'key'            => 'field_67f0002000006',
				'label' => __( 'Close Time', TM_LANGUAGE_DOMAIN ),
				'name'           => 'whatsapp_close_time',
				'type'           => 'time_picker',
				'wrapper'        => array( 'width' => '35' ),
				'display_format' => 'H:i',
				'return_format'  => 'H:i',
			),
		),
	);

	$whatsapp_holidays = array(
		'key'          => 'field_67f0002000007',
		'label' => __( 'Holidays (Away Days)', TM_LANGUAGE_DOMAIN ),
		'name'         => 'whatsapp_holidays',
		'type'         => 'repeater',
		'instructions' => __( 'Specific dates when the widget should show the away message regardless of the weekly schedule.', TM_LANGUAGE_DOMAIN ),
		'layout'       => 'table',
		'button_label' => __( 'Add Holiday', TM_LANGUAGE_DOMAIN ),
		'sub_fields'   => array(
			array(
				'key'            => 'field_67f0002000008',
				'label' => __( 'Date', TM_LANGUAGE_DOMAIN ),
				'name'           => 'whatsapp_holiday_date',
				'type'           => 'date_picker',
				'wrapper'        => array( 'width' => '50' ),
				'display_format' => 'd/m/Y',
				'return_format'  => 'Y-m-d',
				'first_day'      => 1,
			),
			array(
				'key'     => 'field_67f0002000009',
				'label' => __( 'Label', TM_LANGUAGE_DOMAIN ),
				'name'    => 'whatsapp_holiday_label',
				'type'    => 'text',
				'wrapper' => array( 'width' => '50' ),
			),
		),
	);

	// -------------------------------------------------------------------------
	// Group 3: Legal
	// -------------------------------------------------------------------------

	$whatsapp_privacy_notice = array(
		'key'          => 'field_67f0003000001',
		'label' => __( 'Privacy Notice Text', TM_LANGUAGE_DOMAIN ),
		'name'         => 'whatsapp_privacy_notice',
		'type'         => 'textarea',
		'instructions' => __( 'Short notice displayed in the popup footer (e.g. "By starting a chat, you accept our privacy policy.").', TM_LANGUAGE_DOMAIN ),
		'wrapper'      => array( 'width' => '60' ),
		'rows'         => 2,
		'placeholder' => __( 'By starting a chat, you accept our privacy policy.', TM_LANGUAGE_DOMAIN ),
	);

	$whatsapp_privacy_link = array(
		'key'          => 'field_67f0003000002',
		'label' => __( 'Privacy Policy URL', TM_LANGUAGE_DOMAIN ),
		'name'         => 'whatsapp_privacy_link',
		'type'         => 'url',
		'instructions' => __( 'Link to the site privacy policy. Shown alongside the privacy notice.', TM_LANGUAGE_DOMAIN ),
		'wrapper'      => array( 'width' => '40' ),
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'group_67f0000000001',
			'title'                 => __( 'WhatsApp Widget', TM_LANGUAGE_DOMAIN ),
			'fields'                => array(
				$general_messages_tab,
				$whatsapp_enabled,
				$whatsapp_phone_number,
				$whatsapp_position,
				$whatsapp_appearance_delay,
				$whatsapp_device_visibility,
				$whatsapp_fab_text,
				$whatsapp_welcome_title,
				$whatsapp_welcome_message,
				$whatsapp_avatar_logo,
				$whatsapp_cta_text,
				$whatsapp_prefilled_message,
				$whatsapp_away_message,
				$whatsapp_away_next_availability,
				$whatsapp_away_cta_text,
				$business_hours_tab,
				$whatsapp_timezone,
				$whatsapp_schedule,
				$whatsapp_holidays,
				$legal_tab,
				$whatsapp_privacy_notice,
				$whatsapp_privacy_link,
			),
			'location'              => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'tm-widgets',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'field',
			'hide_on_screen'        => '',
			'active'                => true,
			'description'           => '',
		)
	);
}
