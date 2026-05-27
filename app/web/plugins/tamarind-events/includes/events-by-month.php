<?php
/**
 * Displays upcoming events grouped by month.
 *
 * @return void
 *
 * @package Tamarind_Events
 */

namespace tamarind_events;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Displays upcoming events grouped by month.
 *
 * @param bool $show_filter Whether to display the month filter. Default true.
 * @return void
 */
function display_upcoming_events_grouped_by_month( $show_filter = true ) {
	$available_months = get_available_months_with_events_query();

	echo '<div class="events-by-month-wrapper">';

	if ( empty( $available_months ) ) {
		echo '<p>' . esc_html__( 'No upcoming events found.', 'tm-events' ) . '</p>';
		return;
	}

	// Show the month filter if enabled.
	if ( $show_filter ) {
		display_month_filter( $available_months );
	}

	// Loop through each month.
	foreach ( $available_months as $month_data ) {
		// Calculate first and last day of the month.
		$first_day = $month_data['year'] . '-' . $month_data['month'] . '-01';
		$last_day  = gmdate( 'Y-m-t', strtotime( $first_day ) );

		// Get events for this month.
		$events_query = get_events_by_range_query( $first_day, $last_day );

		if ( $events_query->have_posts() ) {
			// Display month header.
			echo '<div class="events-month-group" id="' . esc_attr( strtolower( date_i18n( 'F-Y', strtotime( $first_day ) ) ) ) . '">';
			echo '<h2 class="events-month-title">' . esc_html( date_i18n( 'F Y', strtotime( $first_day ) ) ) . '</h2>';

			// Display events for this month.
			\tamarind_base\print_grid( $events_query, 'default', 'event', 'dark' );

			echo '</div>'; // .events-month-group

			wp_reset_postdata();
		}
	}
	echo '</div>'; // .events-by-month-wrapper
}

/**
 * Displays a dropdown filter for months.
 *
 * @param array $months Array of months with event data.
 * @return void
 */
function display_month_filter( $months ) {
	if ( empty( $months ) ) {
		return;
	}
	?>
	<div class="month-dropdown-wrapper position-sticky" style="top:55px;z-index:1;">
		<select id="month-list">
			<option disabled selected><?php esc_html_e( 'Jump to month:', 'tm-events' ); ?></option>
			<?php
			foreach ( $months as $month_data ) {
				$first_day  = $month_data['year'] . '-' . $month_data['month'] . '-01';
				$month_name = date_i18n( 'F Y', strtotime( $first_day ) );
				$month_id   = strtolower( date_i18n( 'F-Y', strtotime( $first_day ) ) );
				echo "<option value='" . esc_attr( $month_id ) . "'>" . esc_html( $month_name ) . '</option>';
			}
			?>
		</select>
	</div>
	<script>
	document.addEventListener('DOMContentLoaded', function() {
		var monthSelect = document.getElementById('month-list');
		if (monthSelect) {
			monthSelect.addEventListener('change', function() {
				var val = this.value;
				var targetElement = document.getElementById(val);
				if (targetElement) {
					var offset = targetElement.getBoundingClientRect().top + window.pageYOffset - 150;
					window.scrollTo({
						top: offset,
						behavior: 'smooth'
					});
				}
			});
		}
	});
	</script>
	<?php
}
