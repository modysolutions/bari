<?php
/**
 * Template for My Favourites layout.
 *
 * @package Tamarind_UserHome
 */

namespace tamarind_userhome;

defined( 'ABSPATH' ) || exit;


// Get the layout values.
$title_module    = get_sub_field( 'title' );
$number_of_items = get_sub_field( 'number_of_items' );
$container_style = ( get_sub_field( 'container_style' ) ) ? ' tm-sidebar-module--' . get_sub_field( 'container_style' ) : '';
$item_style      = get_sub_field( 'item_style' );

// Get the user favourites.
$user_id    = get_current_user_id();
$favourites = get_field( 'user_favourite_posts', 'user_' . $user_id );
?>

<section class="my-favourites-sidebar tm-sidebar-module<?php echo esc_attr( $container_style ); ?>">

		<h2 class="tm-sidebar-module__title"><?php echo esc_html( $title ); ?></h2>
		
		<?php
		if ( ! empty( $favourites ) ) {

			$favourites_query = \tamarind_base\get_posts_by_ids( $favourites, $number_of_items, array( 'post', 'regulatory_alert' ), 'post__in' );

			\tamarind_base\print_post_list( $favourites_query, 'thumbnail', $item_style );

		} else {
			echo '<div class="tm-sidebar-module__not-found">' . esc_html__( 'No favourites saved.', 'tamarind-user-home' ) . '</div>';
		}

		$my_favourites_url = \tamarind_favourites\get_menu_link_by_key( 'my_favourites' );
		echo '<p class="tm-sidebar-module__view-more"><a href="' . esc_url( $my_favourites_url ) . '">' . esc_html__( 'View all', 'tamarind-user-home' ) . '</a></p>';
		?>

</section>