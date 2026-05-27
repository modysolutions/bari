<?php
/**
 * Template for Upcoming Events layout.
 *
 * @package Tamarind_UserHome
 */

namespace tamarind_userhome;

defined( 'ABSPATH' ) || exit;


if ( is_plugin_active( 'tamarind-events/tamarind-events.php' ) ) {

	// Get the layout values.
	$title_module     = get_sub_field( 'title' );
	$number_of_items  = get_sub_field( 'number_of_items' );
	$container_style  = ( get_sub_field( 'container_style' ) ) ? ' tm-sidebar-module--' . get_sub_field( 'container_style' ) : '';
	$item_style       = get_sub_field( 'item_style' );
	$link_events_page = get_sub_field( 'link_to_events_page' );
	?>

	<section class="upcoming-events-sidebar tm-sidebar-module<?php echo esc_attr( $container_style ); ?>">

		<h2 class="tm-sidebar-module__title"><?php echo esc_html( $title_module ); ?></h2>

		<?php
		if ( function_exists( '\tamarind_events\display_upcoming_events_module' ) ) {
			echo \tamarind_events\display_upcoming_events_module( $number_of_items, $link_events_page );
		}
		?>
	</section>
	
	<?php
} else {
	echo '<p>' . esc_html__( 'The events module is not available.', 'tamarind-user-home' ) . '</p>';
}