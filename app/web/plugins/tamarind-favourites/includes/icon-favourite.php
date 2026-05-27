<?php
/**
 * Print the favourite icon for a post.
 *
 * @package Tamarind_Favourites
 */

namespace tamarind_favourites;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Add the favourite icon to the card and before the post title.
add_action( 'tm_card_after_thumbnail', __NAMESPACE__ . '\add_favourite_icon' );
add_action( 'tm_before_post_title', __NAMESPACE__ . '\add_favourite_icon' );

// Toggle favourite post for the current user.
add_action( 'wp_ajax_toggle_favourite', __NAMESPACE__ . '\tm_handle_toggle_favourite' );


/**
 * Render favorite icon for a post.
 *
 * @param int|null $post_id Post ID (optional, defaults to the current post ID).
 */
function render_favourite_icon( $post_id = null ) {
	if ( ! is_user_logged_in() ) {
		return; // Only for logged-in users.
	}

	$user_id = get_current_user_id();
	$post_id = $post_id ? $post_id : get_the_ID();

	if ( ! $post_id ) {
		return;
	}

	// Get the list of user favourites.
	$user_favourites = get_field( 'user_favourite_posts', 'user_' . $user_id ) ? get_field( 'user_favourite_posts', 'user_' . $user_id ) : array();
	// TODO: save in cookie o transient to avoid database queries.
	$is_favourite = in_array( $post_id, $user_favourites, true );

	ob_start();

	// Heart icon (filled or outline). ?>
	<div class="tm-favourite-icon-wrapper">
		<div class="tm-favourite-icon" data-post-id="<?php echo esc_attr( $post_id ); ?>">
			<?php
			if ( $is_favourite ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo \tamarind_base\get_svg_icon( 'favourite', 'tm-heart tm-heart-filled', 'favourite post' );
			} else {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo \tamarind_base\get_svg_icon( 'favourite-outline', 'tm-heart tm-heart-outline', 'favourite post' );
			}
			?>
		</div>
		<?php
		// Tooltip only if it is a favourite.
		if ( $is_favourite ) {
			$my_favourites_url = get_menu_link_by_key( 'my_favourites' );
			echo '<a href="' . esc_url( $my_favourites_url ) . '" class="tm-favourite-tooltip">' . esc_html__( 'Go to My Favourites', 'tm-favourites' ) . '</a>';
		}
		?>
	</div>

	<?php
	return ob_get_clean();
}


/**
 * Add the favourite icon to a card.
 *
 * @param int $post_id Post ID.
 */
function add_favourite_icon( $post_id ) {

	// Don't show the icon in the favourites menu.
	if ( is_favourites_menu() ) {
		return;
	}

	// Don't show the icon for some CPTs.
	if ( ! in_array( get_post_type( $post_id ), array( 'post', 'regulatory_alert' ), true ) ) {
		return;
	}

	echo render_favourite_icon( $post_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}


/**
 * Handle AJAX request to toggle favourite.
 */
function tm_handle_toggle_favourite() {
	// Verify nonce.
	check_ajax_referer( 'tm_favourites_nonce', 'nonce' );

	// Get the user ID and post ID.
	$user_id = get_current_user_id();
	$post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;

	if ( ! $user_id || ! $post_id ) {
		wp_send_json_error( array( 'message' => 'Invalid user or post ID.' ) );
	}

	// Get the current user's favourites.
	$user_favourites = get_field( 'user_favourite_posts', 'user_' . $user_id ) ? get_field( 'user_favourite_posts', 'user_' . $user_id ) : array();

	if ( in_array( $post_id, $user_favourites, true ) ) {
		// Remove the post from favourites.
		$user_favourites = array_diff( $user_favourites, array( $post_id ) );
		$is_favourite    = false;
		$action_type     = 'removed';
	} else {
		// Add the post to favourites.
		array_unshift( $user_favourites, $post_id );
		$is_favourite = true;
		$action_type  = 'added';
	}

	// Update the ACF field of the user.
	update_field( 'user_favourite_posts', $user_favourites, 'user_' . $user_id );

	// Log the action to history table.
	$post_type = get_post_type( $post_id );
	log_favourite_action( $user_id, $post_id, $action_type, $post_type );

	wp_send_json_success( array( 'is_favourite' => $is_favourite ) );
}


/**
 * Displays the user's favorite posts list in Header icon.
 *
 * @param WP_Query $query Query that contains the favorite posts.
 * @return string HTML content.
 */
function tamarind_favourites_list( \WP_Query $query ) { // phpcs:ignore Squiz.Commenting.FunctionComment.IncorrectTypeHint

	// Fallback image.
	$image_fallback = get_field( 'alerts_thumb', 'options' )['sizes']['thumbnail'] ?? '';

	ob_start();
	echo '<ul class="tm-list-layout2">';

	foreach ( $query->posts as $post ) {
		$title     = $post->post_title;
		$permalink = get_permalink( $post );
		$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'large' );
		$image_url = $thumbnail ? $thumbnail[0] : $image_fallback;

		echo '<div class="tm-list-layout2__item">
			<img src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $title ) . '">
			<div>
				<a href="' . esc_url( $permalink ) . '"><strong>' . esc_html( $title ) . '</strong></a><br/>
				<small>' . get_the_date( 'jS M Y', $post->ID ) . '</small>
			</div>
		</div>';
	}

	echo '</ul>';

	wp_reset_postdata();

	return ob_get_clean();
}
