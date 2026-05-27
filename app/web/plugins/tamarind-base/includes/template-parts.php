<?php
/**
 * Template Parts functions.
 *
 * @package TamarindBase
 */

namespace tamarind_base;

defined( 'ABSPATH' ) || exit;


/**
 * Get the posts by IDs.
 *
 * @param array        $ids Array of post IDs.
 * @param int          $limit Number of posts to display.
 * @param array|string $post_type Array of post types.
 * @param string       $order_by Order by.
 *
 * @return object
 */
function get_posts_by_ids( array $ids, int $limit = -1, array|string $post_type = array( 'post', 'regulatory_alert' ), string $order_by = 'date' ) : object {
	$args = array(
		'post__in'       => $ids,
		'post_type'      => $post_type,
		'posts_per_page' => $limit,
		'orderby'        => $order_by,
		'post_status'    => 'publish',
	);

	$cache_key   = 'posts_by_ids_' . md5( serialize( $args ) );
	$cache_group = 'posts';
	$posts       = wp_cache_get( $cache_key, $cache_group );
	if ( $posts ) {
		return $posts;
	}
	$posts = new \WP_Query( $args );
	wp_cache_set( $cache_key, $posts, $cache_group, 10 * MINUTE_IN_SECONDS );
	return $posts;
}

/**
 * Render a template part.
 *
 * @param string $template_type Type of template (card, list, slider).
 * @param mixed  $content_data Main content to display (WP_Post or WP_Query).
 * @param array  $args {
 *      Optional. Additional arguments.
 *
 *     @type string $layout      Layout variant. Default 'layout1'.
 *     @type string $item_layout For sliders, layout of individual items.
 *     @type string $style       CSS style classes.
 * }
 */
function render_template_part( string $template_type, mixed $content_data, array $args = array() ): void {
	$template_path = plugin_dir_path( __DIR__ )
		. 'template-parts/' . $template_type . '-' . $args['layout'] . '.php';

	if ( ! file_exists( $template_path ) ) {
		echo '<p>Template not found: ' . esc_html( basename( $template_path ) ) . '</p>';
		return;
	}

	$content = $content_data;
	include $template_path;
}


/**
 * Print Post Card. This function is used to print a post card with a specific layout and style.
 *
 * @param \WP_Post $post Post object.
 * @param string $template_type_parent Parent template type.
 * @param string $layout Custom style.
 * @param string $style Custom card style.
 * @param int $ribbon_days Number of days to show "New" ribbon. 0 to disable.
 *
 * @return void
 * @uses render_template_part() Renders the template part for the post card.
 *
 */
function print_post_card( \WP_Post $post, string $template_type_parent = '', string $layout = 'default', string $style = '', int $ribbon_days = 0 ): void { // phpcs:ignore Squiz.Commenting.FunctionComment.IncorrectTypeHint
	render_template_part(
		'card',
		$post,
		array(
			'template_type_parent' => $template_type_parent,
			'layout'               => $layout,
			'item_style'           => $style,
			'ribbon_days'          => $ribbon_days,
		)
	);
}

/**
 * Print in List format the posts.
 *
 * This function is used to print a list of posts with a specific layout and style.
 *
 * @param \WP_Query $query Query with the posts to display.
 * @param string $layout List layout.
 * @param string $style Custom list style.
 *
 * @return void
 * @uses render_template_part() Renders the template part for the post list.
 *
 */
function print_post_list( \WP_Query $query, string $layout = 'default', string $style = '' ): void { // phpcs:ignore Squiz.Commenting.FunctionComment.IncorrectTypeHint
	render_template_part(
		'list',
		$query,
		array(
			'layout'     => $layout,
			'item_style' => $style,
		)
	);
}


/**
 * Print in Slider format the posts.
 *
 * This function is used to print a slider of posts with a specific layout and style.
 *
 * @param \WP_Query $query Query with the posts to display.
 * @param string $layout Slider layout.
 * @param string $item_layout Card layout.
 * @param string $item_style Custom card style.
 *
 * @return void
 * @uses render_template_part() Renders the template part for the post slider.
 *
 */
function print_slider( \WP_Query $query, string $layout = 'default', string $item_layout = 'default', string $item_style = '' ): void { // phpcs:ignore Squiz.Commenting.FunctionComment.IncorrectTypeHint
	render_template_part(
		'slider',
		$query,
		array(
			'layout'      => $layout,
			'item_layout' => $item_layout,
			'item_style'  => $item_style,
		)
	);
}

/**
 * Print in Grid format the posts.
 *
 * This function is used to print a grid of posts with a specific layout and style.
 *
 * @param \WP_Query $query Query with the posts to display.
 * @param string $layout Grid layout.
 * @param string $item_layout Card layout.
 * @param string $item_style Custom card style.
 *
 * @return void
 * @uses render_template_part() Renders the template part for the post grid.
 *
 */
function print_grid( \WP_Query $query, string $layout = 'default', string $item_layout = 'default', string $item_style = '' ): void { // phpcs:ignore Squiz.Commenting.FunctionComment.IncorrectTypeHint
	render_template_part(
		'grid',
		$query,
		array(
			'layout'      => $layout,
			'item_layout' => $item_layout,
			'item_style'  => $item_style,
		)
	);
}

/**
 * Call Swiper Slider.
 *
 * This function generates the JavaScript code to initialize a Swiper slider.
 *
 * @param string    $unique_id Unique ID for the slider.
 * @param array|int $slider_per_view Number of slides per view or breakpoints.
 * @param bool      $navigation Enable navigation buttons.
 * @param bool      $pagination Enable pagination.
 * @param array     $args Additional arguments for Swiper configuration.
 *
 * @return string HTML and JavaScript code for the Swiper slider.
 *
 * Example usage:
 *
 * ```php
 * echo call_swiper_slider('my-slider', [320 => 1, 768 => 2, 1024 => 4], true, true, [
 *     'autoplay' => true,
 *     'loop' => false,
 *     'speed' => 1000,
 *    'customParam' => 'someValue'
 * ]);
 *
 * echo call_swiper_slider('my-slider', 4, true, true);
 * ```
 */
function call_swiper_slider( string $unique_id, array|int $slider_per_view = 3, bool $navigation = true, bool $pagination = false, array $args = array() ): string {
	ob_start();

	// Default value.
	$main_slides_per_view = 3;

	// Process $slider_per_view.
	if ( is_numeric( $slider_per_view ) ) {
		$main_slides_per_view = $slider_per_view;
	} elseif ( is_array( $slider_per_view ) ) {
		if ( ! empty( $slider_per_view ) ) {
			// Get the largest breakpoint (direct numeric value).
			krsort( $slider_per_view );
			$main_slides_per_view = reset( $slider_per_view );
		}
	}

	// Convert breakpoints to Swiper format (with arrays if necessary).
	$prepare_breakpoints = function ( $breakpoints ) {
		$result = array();
		foreach ( $breakpoints as $width => $slides ) {
			$result[ $width ] = is_array( $slides ) ? $slides : array( 'slidesPerView' => $slides );
		}
		return $result;
	};

	// Default breakpoints (simplified).
	$default_breakpoints = array(
		320  => 1,
		640  => 2,
		768  => 3,
		1024 => $main_slides_per_view,
	);

	// Prepare final breakpoints.
	$breakpoints = is_array( $slider_per_view ) ? $prepare_breakpoints( $slider_per_view ) : $prepare_breakpoints( $default_breakpoints );
	?>

	<script>
		var swiper = new Swiper("#<?php echo esc_js( $unique_id ); ?>", {
			slidesPerView: <?php echo esc_js( $main_slides_per_view ); ?>,
			spaceBetween: 15,
			<?php if ( $navigation ) : ?>
				navigation: {
					nextEl: `.swiper-btn-next[data-swiper-target="<?php echo esc_js( $unique_id ); ?>"]`,
					prevEl: `.swiper-btn-prev[data-swiper-target="<?php echo esc_js( $unique_id ); ?>"]`,
				},
			<?php endif; ?>
			<?php if ( $pagination ) : ?>
				pagination: {
					el: `.swiper-pagination[data-swiper-target="<?php echo esc_js( $unique_id ); ?>"]`,
					clickable: true,
				},
			<?php endif; ?>
			<?php
			// Additional arguments with precise control of quotes.
			if ( ! empty( $args ) ) {
				foreach ( $args as $key => $value ) {
					if ( is_bool( $value ) ) {
						echo esc_js( $key ) . ': ' . ( $value ? 'true' : 'false' ) . ",\n";
					} elseif ( is_numeric( $value ) ) {
						echo esc_js( $key ) . ': ' . esc_js( $value ) . ",\n";
					} else {
						echo esc_js( $key ) . ': ' . wp_json_encode( $value, JSON_HEX_APOS | JSON_HEX_QUOT ) . ",\n";
					}
				}
			}
			?>
			breakpoints: <?php echo wp_json_encode( $breakpoints, JSON_PRETTY_PRINT ); ?>,
		});
	</script>

	<?php
	return ob_get_clean();
}


/**
 * Get the post Thumbnail URL or a default image.
 *
 * @param \WP_Post $post The post object.
 * @param string   $size The size of the thumbnail. Default is 'large'.
 *
 * @return string|false The URL of the thumbnail or false if not available.
 */
function tm_get_thumbnail_url( \WP_Post $post, string $size = 'large' ): bool|string {
	$post_id   = $post->ID;
	$post_type = $post->post_type;

	// Fallback for default image.
	if ( 'regulatory_alert' === $post_type ) {
		$image_alerts = get_field( 'alerts_thumb', 'options' );
		return $image_alerts['sizes']['medium'] ?? false;
	}
	if ( 'notifications' === $post_type ) {
		return plugins_url( 'assets/icons/alert.svg', __DIR__ );
	}

	// Featured image.
	$thumbnail_id = get_post_thumbnail_id( $post_id );
	if ( $thumbnail_id ) {
		$image = wp_get_attachment_image_src( $thumbnail_id, $size );
		return $image[0];
	}
	return false;
}

/**
 * Get data attributes for a post based on taxonomy terms.
 *
 * @param int   $post_id The ID of the post.
 * @param array $taxonomy Array of taxonomy names to retrieve terms from.
 *
 * @return string Data attributes string for the specified taxonomies.
 */
function tm_get_data_attributes( int $post_id, array $taxonomy = array( 'topics', 'geography', 'content_types' ) ): string {
	$data_attributes = '';

	foreach ( (array) $taxonomy as $tax ) {
		$terms = get_the_terms( $post_id, $tax );

		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			$attribute_name   = str_replace( '_', '-', $tax ); // Convert content_types to content-types.
			$slugs            = implode( ' ', wp_list_pluck( $terms, 'slug' ) );
			$data_attributes .= ' data-' . esc_attr( $attribute_name ) . '="' . esc_attr( $slugs ) . '"';
		}
	}

	return $data_attributes;
}

/**
 * Get the permalink for a post.
 *
 * If the post type is 'company-news', it retrieves a custom field URL.
 * Otherwise, it returns the default permalink.
 *
 * @param \WP_Post $post The post object.
 *
 * @return string The permalink or custom URL for the post.
 */
function tm_get_permalink( \WP_Post $post ): string {
	if ( 'company-news' === $post->post_type ) {
		return get_field( 'company_news_url', $post->ID );
	}
	return get_permalink( $post );
}


/**
 * Checks if the current post is a regulatory alert
 *
 * @param int|null $post_id The ID of the post to check. Defaults to current post ID.
 *
 * @return bool True if the post is a regulatory alert, false otherwise
 */
function is_alert( ?int $post_id = null ): bool {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	if ( 'regulatory_alert' !== get_post_type( $post_id ) ) {
		return false;
	}

	$terms = get_the_terms( $post_id, 'content_types' );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return false;
	}

	foreach ( $terms as $term ) {
		if ( 'alerts' === $term->slug ) {
			return true;
		}
	}

	return false;
}

/**
 * Generates formatted taxonomy terms links for a post with special handling for alerts
 *
 * This function retrieves Content Types terms and Geography terms (for alerts),
 * formats them as HTML links, and joins them with appropriate separators.
 *
 * @param int|null $post_id The ID of the post to process. Defaults to current post ID.
 *
 * @return string HTML string containing formatted taxonomy term links
 */
function generate_formatted_taxonomy_links( ?int $post_id = null ): string {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$taxonomy_meta = 'content_types';
	$terms = get_the_terms( $post_id, $taxonomy_meta );

	$formatted_links = '';
	$is_alert_post = is_alert( $post_id );

	if ( $terms && ! is_wp_error( $terms ) ) {
		$term_links = array();

		foreach ( $terms as $term ) {
			if ( $is_alert_post && 'alerts' === $term->slug ) {
				$icon = '';
			} else {
				$icon = get_field( $term->slug . '_icon', 'option' );
			}
			$term_links[] = '<a href="' . esc_url( get_term_link( $term ) ) . '">' . esc_html( $term->name ) . '</a>';
		}
		$formatted_links = join( ' , ', $term_links );
	}

	if ( $is_alert_post ) {
		$alerts_archive_link = get_post_type_archive_link( 'regulatory_alert' );
		$geography_terms = wp_get_post_terms( $post_id, 'geography', array( 'orderby' => 'name' ) );

		$geography_links = '';
		$is_first_continent = true;

		foreach ( $geography_terms as $geography_term ) {
			if ( ! $is_first_continent ) {
				$geography_links .= ', ';
			} else {
				$is_first_continent = false;
			}
			$geography_links .= '<a href="' . $alerts_archive_link . '?geoalerts=' . $geography_term->slug . '"> ' . $geography_term->name . '</a>';
		}
		$formatted_links .= ' | ' . $geography_links;
	}

	return $formatted_links;
}


/**
 * Determine if a post should display a "New" ribbon based on its publication date.
 *
 * @param string $post_date The publication date of the post in a format recognized by strtotime().
 * @param int    $max_days  The maximum number of days since publication to consider the post as "New". Default is 30 days.
 * @return bool True if the post is "New", false otherwise.
 */
function should_show_new_ribbon( string $post_date, int $max_days = 30 ) : bool {
	if ( ! $post_date ) {
		return false;
	}

	$published_timestamp = strtotime( $post_date );
	$now = time();
	$days_since_published = floor( ( $now - $published_timestamp ) / DAY_IN_SECONDS );

	return $days_since_published <= (int) $max_days;
}

/**
 * Adds ellipsis to the end of the excerpt,
 * whether it is automatically generated or manually written.
 * Prevents duplicate ellipsis if they already exist.
 *
 * @param string $excerpt The excerpt text.
 *
 * @return string
 */
function tm_add_ellipsis_to_excerpt( string $excerpt ): string {
	$excerpt = trim( $excerpt );

	if ( ! str_ends_with( $excerpt, '...' ) ) {
		$excerpt .= '...';
	}

	return $excerpt;
}

add_filter( 'get_the_excerpt', __NAMESPACE__ . '\tm_add_ellipsis_to_excerpt' );
