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
	$title_module    = get_sub_field( 'title' );
	$number_of_items = get_sub_field( 'number_of_items' );
	$container_style = ( get_sub_field( 'container_style' ) ) ? ' tm-module--' . get_sub_field( 'container_style' ) : '';
	$item_style      = get_sub_field( 'item_style' );

	// Get Upcoming Events posts.
	$upcoming_events_contents = \tamarind_events\get_upcoming_events_query();
	?>

	<section class="upcoming-events-module tm-module<?php echo esc_attr( $container_style ); ?>">

		<?php if ( $title_module ) { ?>
			<h2 class="new-module-label"><?php echo esc_html( $title_module ); ?></h2>
		<?php } ?>

		<div class="upcoming-events-module__inner">
			<?php
			\tamarind_base\print_grid( $upcoming_events_contents, 'default', 'event', $item_style );
			?>
		</div>

	</section>

	<?php
} else {
	echo '<p>' . esc_html__( 'The events module is not available.', 'tamarind-user-home' ) . '</p>';
}
