<?php
/**
 * My Favourites filter content.
 *
 * @package Tamarind_Favourites
 */

namespace tamarind_favourites;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Displays the user's favorite posts.
 *
 * @return void
 */
function tamarind_favourites_display() {
	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		echo '<p>You are not authenticated.</p>';
		return;
	}

	$flexible_field_name   = 'tm_userhome_flexible_content_modules';
	$no_favourites_message = '<p>' . __( 'You have no saved favourites.', 'tamarind-user-home' ) . '</p>';

	// Search for the custom message in the Favourites Module of the User Home.
	if ( have_rows( $flexible_field_name, 'option' ) ) {
		while ( have_rows( $flexible_field_name, 'option' ) ) {
			the_row();

			if ( get_row_layout() === 'my_favourites' ) { 
				$message = get_sub_field( 'no_saved_favourites_message' );
				if ( ! empty( $message ) ) {
					$no_favourites_message = $message;
				}
			}
		}
	}

	$favourites = get_field( 'user_favourite_posts', 'user_' . $user_id );

	if ( empty( $favourites ) ) {
		echo '<div class="tm-module__no-favourites box-info">' . wp_kses_post( $no_favourites_message ) . '</div>';
		return;
	}

	// Query to retrieve all favourites.
	$favourites_query = \tamarind_base\get_posts_by_ids( $favourites, -1, array( 'post', 'regulatory_alert' ), 'post__in' );

	echo '<ul class="tm-layout-grid">';
	foreach ( $favourites_query->posts as $post ) {
		\tamarind_base\print_post_card( $post, 'grid', 'default' );
	}
	echo '</ul>';

	wp_reset_postdata();
}

/**
 * Adds a button to remove the post from favourites.
 *
 * @param int $post_id The ID of the post.
 * @param WP_Post $post The post object.
 *
 * @return void
 */
add_action( 'tm_card_after_thumbnail', __NAMESPACE__ . '\add_remove_button_to_card', 10, 2 );

/**
 * Adds a remove button to the card in the favourites menu.
 *
 * @param int $post_id The ID of the post.
 * @return void
 */
function add_remove_button_to_card( $post_id ) {
	if ( is_favourites_menu() ) {
		echo '<span class="tm-remove-favourite" data-post-id="' . esc_attr( $post_id ) . '">';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo \tamarind_base\get_svg_icon( 'remove', '', 'remove' );
		echo '</span>';
	}
}


/**
 * Generates the filter UI for favorite posts.
 *
 * @return string HTML of the filter.
 */
function tamarind_favourites_filter() {
	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		return '';
	}

	$favourites = get_field( 'user_favourite_posts', 'user_' . $user_id );
	if ( empty( $favourites ) ) {
		return;
	}

	$taxonomies = array( 'topics', 'geography', 'content_types' );
	$terms      = array();

	foreach ( $favourites as $post_id ) {
		foreach ( $taxonomies as $taxonomy ) {
			$post_terms = wp_get_post_terms( $post_id, $taxonomy );
			if ( ! empty( $post_terms ) && ! is_wp_error( $post_terms ) ) {
				foreach ( $post_terms as $term ) {
					$terms[ $taxonomy ][ $term->term_id ] = $term;
				}
			}
		}
	}

	ob_start(); ?>
	
	<tm-accordion>
		<ul>
			<?php foreach ( $taxonomies as $taxonomy ) : ?>
				<?php if ( ! empty( $terms[$taxonomy] ) ) : ?>
					<li class="accordion-item">
						<button class="menu-title"><?php echo esc_html( ucfirst( str_replace( '_', ' ', $taxonomy ) ) ); ?></button>
						<ul class="menu-options">
							<?php
							$parent_terms = array();
							foreach ( $terms[ $taxonomy ] as $term ) {
								if ( 0 === $term->parent ) {
									$parent_terms[ $term->term_id ] = array(
										'term'     => $term,
										'children' => array(),
									);
								} else {
									// If the parent is not yet registered, we initialize it.
									if ( ! isset( $parent_terms[ $term->parent ] ) ) {
										$parent_terms[ $term->parent ] = array(
											'term'     => get_term( $term->parent ), // Get the real parent.
											'children' => array(),
										);
									}
									$parent_terms[ $term->parent ]['children'][] = $term;
								}
							}

							// Sort terms alphabetically.
							uasort(
								$parent_terms,
								function (
									$a,
									$b
								) {
									return strcmp( $a['term']->name, $b['term']->name );
								}
							);
							foreach ( $parent_terms as $parent ) {
								usort(
									$parent['children'],
									function ( $a, $b ) {
										return strcmp( $a->name, $b->name );
									}
								);
							}

							foreach ( $parent_terms as $parent ) :
								?>
								<li>
									<label>
										<input type="checkbox" class="filter-checkbox" data-taxonomy="<?php echo esc_attr( $taxonomy ); ?>" value="<?php echo esc_attr( $parent['term']->slug ); ?>">
										<?php echo esc_html( $parent['term']->name ); ?>
									</label>
									<?php if ( ! empty( $parent['children'] ) ) : ?>
										<ul>
											<?php 
											foreach ( $parent['children'] as $child ) :
												?>
												<li>
													<label>
														<input type="checkbox" class="filter-checkbox" data-taxonomy="<?php echo esc_attr( $taxonomy ); ?>" value="<?php echo esc_attr( $child->slug ); ?>">
														<?php echo esc_html( $child->name ); ?>
													</label>
												</li>
											<?php endforeach; ?>
										</ul>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					</li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>
	</tm-accordion>
	<?php
	return ob_get_clean();
}


/**
 * Handles the AJAX request to remove a favourite.
 */
function tamarind_remove_favourite() {
	check_ajax_referer( 'tm_favourites_nonce', 'nonce' );

	$post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;
	$user_id = get_current_user_id();

	if ( ! $user_id || ! $post_id ) {
		wp_send_json_error( 'Invalid request' );
	}

	$favourites = get_field( 'user_favourite_posts', 'user_' . $user_id );
	$key        = array_search( $post_id, $favourites, true );

	if ( false !== $key ) {
		unset( $favourites[ $key ] );
		update_field( 'user_favourite_posts', array_values( $favourites ), 'user_' . $user_id );
		
		// Log the removal to history table.
		$post_type = get_post_type( $post_id );
		log_favourite_action( $user_id, $post_id, 'removed', $post_type );
	}

	wp_send_json_success();
}
add_action( 'wp_ajax_tamarind_remove_favourite', __NAMESPACE__ . '\tamarind_remove_favourite' );
