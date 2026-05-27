<?php
/**
 * Template for My Favourites layout.
 *
 * @package Tamarind_UserHome
 */

namespace tamarind_userhome;

defined( 'ABSPATH' ) || exit;


// Get the layout values.
$title_module          = get_sub_field( 'title' );
$number_of_items       = get_sub_field( 'number_of_items' );
$container_style       = ( get_sub_field( 'container_style' ) ) ? ' tm-module--' . get_sub_field( 'container_style' ) : '';
$item_style            = get_sub_field( 'item_style' );
$no_favourites_message = ( get_sub_field( 'no_saved_favourites_message' ) ) ? get_sub_field( 'no_saved_favourites_message' ) : '<p>' . __( 'You have no saved favourites.', 'tamarind-user-home' ) . '</p>';
?>

<section class="my-favourites-module tm-module<?php echo esc_attr( $container_style ); ?>">

	<?php
	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		echo '<p>' . esc_html__( 'You are not authenticated.', 'tamarind-user-home' ) . '</p></section>';
		return;
	}

	if ( $title_module ) { ?>
		<h2 class="new-module-label"><?php echo esc_html( $title_module ); ?></h2>
		<?php
	}

	$favourites = get_field( 'user_favourite_posts', 'user_' . $user_id );
	if ( empty( $favourites ) ) {
		echo '<div class="tm-module__no-favourites box-info">' . wp_kses_post( $no_favourites_message ) . '</div></section>';
		return;
	}

	$favourites_posts = \tamarind_base\get_posts_by_ids( $favourites, $number_of_items, array( 'post', 'regulatory_alert' ), 'post__in' );

	\tamarind_base\print_slider( $favourites_posts, 'default', 'default', $item_style );

	$my_favourites_url = \tamarind_favourites\get_menu_link_by_key( 'my_favourites' );
	echo '<p class="tm-module__view-more"><a class="tm-btn btn-transparent" href="' . esc_url( $my_favourites_url ) . '">' . esc_html__( 'View all', 'tamarind-user-home' ) . '</a></p>';
	?>

</section>