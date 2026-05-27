<?php
/**
 * Functions for Polaris Widget.
 *
 * Renders a floating navigation FAB that toggles between the homepage
 * and a configured internal landing target.
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\polaris;

defined( 'ABSPATH' ) || exit;

add_action( 'wp_footer', __NAMESPACE__ . '\render_polaris_widget', 21 );

/**
 * Render the Polaris floating navigation widget in the page footer.
 *
 * Reads configuration from ACF option fields and outputs a container
 * element with data-attributes consumed by the TMPolaris JS class.
 */
function render_polaris_widget(): void {
	if ( ! function_exists( 'get_field' ) ) {
		return;
	}

	$enabled = get_field( 'polaris_enabled', 'option' );
	if ( ! $enabled ) {
		return;
	}

	$landing_target_id = get_landing_target_id();
	if ( ! $landing_target_id ) {
		return;
	}

	if ( is_static_homepage_target( $landing_target_id ) ) {
		return;
	}

	$landing_url = get_permalink( $landing_target_id );
	if ( ! $landing_url ) {
		return;
	}

	$user_visibility = get_field( 'polaris_user_visibility', 'option' ) ?: 'all';
	$is_logged_in    = is_user_logged_in();

	if ( 'logged_in' === $user_visibility && ! $is_logged_in ) {
		return;
	}
	if ( 'logged_out' === $user_visibility && $is_logged_in ) {
		return;
	}

	$home_url      = trailingslashit( home_url( '/' ) );
	$position      = get_field( 'polaris_position', 'option' ) ?: 'right';
	$device        = get_field( 'polaris_device_visibility', 'option' ) ?: 'all';
	$bottom_offset = (int) ( get_field( 'polaris_bottom_offset', 'option' ) ?? 32 );
	$is_on_landing = (int) get_queried_object_id() === $landing_target_id;

	$label_to_landing = get_field( 'polaris_label_to_landing', 'option' ) ?: __( 'Learn more', 'tamarind-base' );
	$label_to_home    = get_field( 'polaris_label_to_home', 'option' ) ?: __( 'Back to home', 'tamarind-base' );
	$target_url       = $is_on_landing ? $home_url : $landing_url;
	$target_label     = $is_on_landing ? $label_to_home : $label_to_landing;

	?>
	<div class="tm-polaris-widget"
		style="--tm-polaris-bottom: <?php echo esc_attr( $bottom_offset ); ?>px"
		data-state="<?php echo esc_attr( $is_on_landing ? 'on-landing' : 'on-home' ); ?>"
		data-position="<?php echo esc_attr( $position ); ?>"
		data-device="<?php echo esc_attr( $device ); ?>"
	>
		<a class="tm-polaris-fab" href="<?php echo esc_url( $target_url ); ?>" aria-label="<?php echo esc_attr( $target_label ); ?>" title="<?php echo esc_attr( $target_label ); ?>">
			<span class="tm-polaris-fab__label"><?php echo esc_html( $target_label ); ?></span>
			<span class="tm-polaris-fab__icon tm-polaris-fab__icon--to-landing" aria-hidden="true">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
			</span>
			<span class="tm-polaris-fab__icon tm-polaris-fab__icon--to-home" aria-hidden="true">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
			</span>
		</a>
	</div>
	<?php
}

/**
 * Resolve the selected internal landing target as a post ID.
 *
 * @return int
 */
function get_landing_target_id(): int {
	$target = get_field( 'polaris_landing_target', 'option' );

	if ( $target instanceof \WP_Post ) {
		return (int) $target->ID;
	}

	$target_id = absint( $target );
	if ( $target_id ) {
		return $target_id;
	}

	return 0;
}
