<?php
/**
 * Upcoming Events Module
 *
 * @package Tamarind_Events
 */

namespace tamarind_events;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Register all action hooks at the beginning.
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\register_events_module_assets' );
add_action( 'wp_ajax_get_events_by_month', __NAMESPACE__ . '\get_events_by_month_ajax' );
add_action( 'wp_ajax_nopriv_get_events_by_month', __NAMESPACE__ . '\get_events_by_month_ajax' );
add_action( 'wp_ajax_get_available_months_with_events', __NAMESPACE__ . '\get_available_months_with_events_ajax' );
add_action( 'wp_ajax_nopriv_get_available_months_with_events', __NAMESPACE__ . '\get_available_months_with_events_ajax' );

/**
 * Register and enqueue module assets
 *
 * Registers the JavaScript file and localizes script data
 */
function register_events_module_assets() {
	$plugin_url = plugin_dir_url( __FILE__ );

	wp_register_script(
		'upcoming-events',
		$plugin_url . '../src/js/upcoming-events.js',
		array(),
		TM_BASE_VERSION,
		true
	);

	$first_month = get_first_month_with_events();

	wp_localize_script(
		'upcoming-events',
		'eventsModuleData',
		array(
			'ajaxurl'          => admin_url( 'admin-ajax.php' ),
			'defaultMonth'     => $first_month['month'],
			'defaultYear'      => $first_month['year'],
			'defaultMonthName' => date_i18n( 'F Y', strtotime( $first_month['year'] . '-' . $first_month['month'] . '-01' ) ),
			'security'         => wp_create_nonce( 'events_module_nonce' ),
		)
	);
}

/**
 * Display the upcoming events module
 *
 * @param int         $events_limit Number of events to show per month (default is -1 for no limit).
 * @param string|bool $link_events_page URL for "Show more" button or false to disable.
 * @return string HTML markup for the events module
 */
function display_upcoming_events_module( $events_limit = -1, $link_events_page = false ) {
	wp_enqueue_script( 'upcoming-events' );

	// Localize the limit for JS.
	wp_add_inline_script(
		'upcoming-events',
		'var eventsModuleConfig = ' . wp_json_encode(
			array(
				'eventsLimit'    => (int) $events_limit,
				'linkEventsPage' => $link_events_page,
			)
		) . ';',
		'before'
	);

	ob_start(); ?>
	<div class="events-module">
		<div class="events-header">
			<button class="nav-arrow prev-month" aria-label="<?php esc_attr_e( 'Previous month', 'tamarind-events' ); ?>">
				<?php
				$nav_left = function_exists( 'tamarind_base\get_svg_icon' ) ? \tamarind_base\get_svg_icon( 'nav-left', '', esc_attr__( 'Left', 'tm-events' ) ) : '<i class="fa fa-chevron-left"></i>';
				echo $nav_left; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			</button>
			<h2 class="current-month"><?php esc_html_e( 'Loading...', 'tamarind-events' ); ?></h2>
			<button class="nav-arrow next-month" aria-label="<?php esc_attr_e( 'Next month', 'tamarind-events' ); ?>">
				<?php
				$nav_right = function_exists( 'tamarind_base\get_svg_icon' ) ? \tamarind_base\get_svg_icon( 'nav-right', '', esc_attr__( 'Right', 'tm-events' ) ) : '<i class="fa fa-chevron-right"></i>';
				echo $nav_right; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			</button>
		</div>

		<div class="events-loading" style="display: none;">
			<div class="loading-spinner"></div>
		</div>

		<div class="events-list-container">
			<!-- Events will be loaded here via AJAX -->
		</div>
	</div>
	<dialog id="event-meetup-form" class="tm-modal">
		<div class="tm-modal-content">
			<button class="tm-modal-close" type="button">×</button>
				<?php
				if ( is_plugin_active( 'tamarind-forms/tamarind-forms.php' ) ) {
					\tamarind_forms\display_form\display_form( 'events_form', true, get_the_id() );
				}
				?>
		</div>
	</dialog>
	<?php

	return ob_get_clean();
}

/**
 * AJAX handler to fetch events by month
 */
function get_events_by_month_ajax() {
	check_ajax_referer( 'events_module_nonce', 'security' );

	$month            = isset( $_POST['month'] ) ? sanitize_text_field( wp_unslash( $_POST['month'] ) ) : '';
	$year             = isset( $_POST['year'] ) ? sanitize_text_field( wp_unslash( $_POST['year'] ) ) : '';
	$limit            = isset( $_POST['limit'] ) ? (int) $_POST['limit'] : -1;
	$link_events_page = isset( $_POST['link_events_page'] ) ? sanitize_text_field( wp_unslash( $_POST['link_events_page'] ) ) : false;

	if ( empty( $month ) || empty( $year ) ) {
		wp_send_json_error( __( 'Invalid parameters', 'tamarind-events' ) );
	}

	if ( ! preg_match( '/^\d{2}$/', $month ) || ! preg_match( '/^\d{4}$/', $year ) ) {
		wp_send_json_error( __( 'Invalid date format', 'tamarind-events' ) );
	}

	$first_day = gmdate( 'Y-m-01', strtotime( "$year-$month-01" ) );
	$last_day  = gmdate( 'Y-m-t', strtotime( "$year-$month-01" ) );

	// Count the total number of events.
	$count_query  = get_events_by_range_query( $first_day, $last_day, -1 );
	$total_events = $count_query->post_count;
	wp_reset_postdata();

	// Limited events.
	$events_query = get_events_by_range_query( $first_day, $last_day, $limit );
	$events       = array();
	$has_more     = false;

	if ( $events_query->have_posts() ) {
		while ( $events_query->have_posts() ) {
			$events_query->the_post();
			$id = get_the_ID();

			$img_id  = get_field( 'event_picture', $id );
			$img_obj = $img_id ? wp_get_attachment_image_src( $img_id, 'medium' ) : false;
			$img     = ! empty( $img_obj[0] ) ? $img_obj[0] : '';

			$events[] = array(
				'title'     => get_the_title(),
				'start'     => get_field( 'event_date_start', $id ),
				'end'       => get_field( 'event_date_end', $id ),
				'place'     => get_field( 'event_place_name', $id ),
				'url'       => get_field( 'event_website', $id ),
				'feat'      => get_field( 'event_featured', $id ),
				'img'       => $img,
				'btn'       => get_field( 'event_button_text', $id ),
				'permalink' => get_permalink(),
			);
		}
		// Determine if there are more events.
		$has_more = $limit > 0 && $total_events > $limit;
	}

	wp_reset_postdata();

	// "Show more" link.
	$archive_link = false;
	if ( ! empty( $link_events_page ) && 'false' !== $link_events_page ) {
		$archive_link = esc_url( $link_events_page );
	}

	wp_send_json_success(
		array(
			'events'       => $events,
			'month_name'   => date_i18n( 'F Y', strtotime( "$year-$month-01" ) ),
			'has_more'     => $has_more,
			'more_count'   => $has_more ? $total_events - $limit : 0,
			'archive_link' => $archive_link . '#' . strtolower( date_i18n( 'F-Y', strtotime( "$year-$month-01" ) ) ),
		)
	);
}

/**
 * AJAX handler to fetch available months with events
 */
function get_available_months_with_events_ajax() {
	check_ajax_referer( 'events_module_nonce', 'security' );

	$months = get_available_months_with_events_query();

	if ( empty( $months ) ) {
		wp_send_json_error( __( 'No upcoming events found', 'tamarind-events' ) );
	}

	wp_send_json_success( $months );
}
