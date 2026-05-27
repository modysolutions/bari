<?php
/**
 * Functions for WhatsApp Widget.
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\whatsapp;

defined( 'ABSPATH' ) || exit;

add_action( 'wp_footer', __NAMESPACE__ . '\render_whatsapp_widget', 20 );

/**
 * Render the WhatsApp floating widget in the page footer.
 *
 * Reads all configuration from ACF option fields and outputs a container
 * element with data-attributes consumed by the TMWhatsapp JS class.
 */
function render_whatsapp_widget() {
	if ( ! function_exists( 'get_field' ) ) {
		return;
	}

	if ( is_user_logged_in() ) {
		return;
	}

	$enabled = get_field( 'whatsapp_enabled', 'option' );
	if ( ! $enabled ) {
		return;
	}

	$phone = get_field( 'whatsapp_phone_number', 'option' );
	if ( ! $phone ) {
		return;
	}

	// General settings.
	$position   = get_field( 'whatsapp_position', 'option' ) ?: 'right';
	$delay      = (int) get_field( 'whatsapp_appearance_delay', 'option' );
	$device     = get_field( 'whatsapp_device_visibility', 'option' ) ?: 'all';

	// Messages.
	$welcome_title  = get_field( 'whatsapp_welcome_title', 'option' ) ?: __( 'Chat with us', 'tamarind-base' );
	$welcome_msg    = get_field( 'whatsapp_welcome_message', 'option' ) ?: '';
	$cta_text       = get_field( 'whatsapp_cta_text', 'option' ) ?: __( 'Start Chat', 'tamarind-base' );
	$away_cta_text  = get_field( 'whatsapp_away_cta_text', 'option' ) ?: __( 'Leave a Message', 'tamarind-base' );
	$fab_text       = get_whatsapp_fab_text();
	$prefilled      = get_field( 'whatsapp_prefilled_message', 'option' ) ?: '';
	$away_msg       = get_field( 'whatsapp_away_message', 'option' ) ?: '';
	$away_next      = get_field( 'whatsapp_away_next_availability', 'option' ) ?: '';
	$fab_label      = $fab_text
		? sprintf( __( '%s on WhatsApp', 'tamarind-base' ), $fab_text )
		: __( 'Chat on WhatsApp', 'tamarind-base' );
	$popup_label    = __( 'WhatsApp chat window', 'tamarind-base' );
	$close_label    = __( 'Close chat window', 'tamarind-base' );
	$status_online  = __( 'Online', 'tamarind-base' );
	$status_away    = __( 'Away', 'tamarind-base' );

	// Business hours.
	$timezone = get_field( 'whatsapp_timezone', 'option' ) ?: 'UTC';
	$schedule = build_schedule_json();
	$holidays = build_holidays_json();

	// Legal.
	$privacy_notice = get_field( 'whatsapp_privacy_notice', 'option' ) ?: '';
	$privacy_link   = get_field( 'whatsapp_privacy_link', 'option' ) ?: '';
	$avatar_data    = get_whatsapp_avatar_data();

	?>
	<div class="tm-whatsapp-widget"
		data-phone="<?php echo esc_attr( $phone ); ?>"
		data-prefilled="<?php echo esc_attr( $prefilled ); ?>"
		data-position="<?php echo esc_attr( $position ); ?>"
		data-delay="<?php echo esc_attr( $delay ); ?>"
		data-device="<?php echo esc_attr( $device ); ?>"
		data-welcome-title="<?php echo esc_attr( $welcome_title ); ?>"
		data-welcome-message="<?php echo esc_attr( $welcome_msg ); ?>"
		data-cta-text="<?php echo esc_attr( $cta_text ); ?>"
		data-away-cta-text="<?php echo esc_attr( $away_cta_text ); ?>"
		data-fab-text="<?php echo esc_attr( $fab_text ); ?>"
		data-fab-label="<?php echo esc_attr( $fab_label ); ?>"
		data-popup-label="<?php echo esc_attr( $popup_label ); ?>"
		data-close-label="<?php echo esc_attr( $close_label ); ?>"
		data-status-online-label="<?php echo esc_attr( $status_online ); ?>"
		data-status-away-label="<?php echo esc_attr( $status_away ); ?>"
		data-away-message="<?php echo esc_attr( $away_msg ); ?>"
		data-away-next-availability="<?php echo esc_attr( $away_next ); ?>"
		data-timezone="<?php echo esc_attr( $timezone ); ?>"
		data-schedule="<?php echo esc_attr( $schedule ); ?>"
		data-holidays="<?php echo esc_attr( $holidays ); ?>"
		data-privacy-notice="<?php echo esc_attr( $privacy_notice ); ?>"
		data-privacy-link="<?php echo esc_url( $privacy_link ); ?>"
		data-avatar-image="<?php echo esc_url( $avatar_data['image'] ); ?>"
		data-avatar-alt="<?php echo esc_attr( $avatar_data['alt'] ); ?>"
	></div>
	<?php
}

/**
 * Build a JSON string from the weekly schedule ACF repeater.
 *
 * @return string JSON-encoded array.
 */
function build_schedule_json(): string {
	$rows = get_field( 'whatsapp_schedule', 'option' );
	if ( ! is_array( $rows ) ) {
		return '[]';
	}

	$schedule = array();
	foreach ( $rows as $row ) {
		$schedule[] = array(
			'day'        => $row['whatsapp_day'] ?? '',
			'is_open'    => ! empty( $row['whatsapp_is_open'] ),
			'open_time'  => $row['whatsapp_open_time'] ?? '',
			'close_time' => $row['whatsapp_close_time'] ?? '',
		);
	}

	return wp_json_encode( $schedule );
}

/**
 * Build a JSON string from the holidays ACF repeater.
 *
 * @return string JSON-encoded array.
 */
function build_holidays_json(): string {
	$rows = get_field( 'whatsapp_holidays', 'option' );
	if ( ! is_array( $rows ) ) {
		return '[]';
	}

	$holidays = array();
	foreach ( $rows as $row ) {
		$holidays[] = array(
			'date'  => $row['whatsapp_holiday_date'] ?? '',
			'label' => $row['whatsapp_holiday_label'] ?? '',
		);
	}

	return wp_json_encode( $holidays );
}

/**
 * Resolve the current vertical logo used in the WhatsApp popup header.
 *
 * @return array{image:string, alt:string}
 */
function get_whatsapp_avatar_data() {
	$custom_avatar = get_field( 'whatsapp_avatar_logo', 'option' );

	if ( $custom_avatar ) {
		$image = wp_get_attachment_image_url( (int) $custom_avatar, 'medium' );
		$alt   = trim( (string) get_post_meta( (int) $custom_avatar, '_wp_attachment_image_alt', true ) );

		if ( ! $alt ) {
			$alt = get_the_title( (int) $custom_avatar );
		}

		if ( ! $alt ) {
			$alt = sprintf( __( '%s logo', 'tamarind-base' ), get_bloginfo( 'name' ) );
		}

		if ( $image ) {
			return array(
				'image' => $image,
				'alt'   => $alt,
			);
		}
	}

	return array(
		'image' => '',
		'alt'   => '',
	);
}

/**
 * Get the optional FAB text, with a temporary fallback to the raw option value.
 *
 * The raw option fallback protects existing environments where the ACF field
 * reference may still point to the old duplicated field key until the options
 * page is re-saved after deployment.
 *
 * @return string
 */
function get_whatsapp_fab_text() {
	$fab_text = get_field( 'whatsapp_fab_text', 'option' );

	if ( is_string( $fab_text ) ) {
		$fab_text = trim( $fab_text );
	} else {
		$fab_text = '';
	}

	if ( '' !== $fab_text ) {
		return $fab_text;
	}

	$raw_fab_text = get_option( 'options_whatsapp_fab_text', '' );

	return is_string( $raw_fab_text ) ? trim( $raw_fab_text ) : '';
}
