<?php
/**
 * Generates the HTML structure of a post List Layout with Thumbnail.
 * List with only title.
 *
 * @package TamarindBase
 *
 * @param \WP_Query $content Query with the posts to display.
 * @param string $style Custom list style.
 *
 * @return string HTML of the list.
 */

namespace tamarind_base;

defined( 'ABSPATH' ) || exit;


// Get the layout values.
if ( ! $content ) {
	return '';
}
$style = ( $args['item_style'] ) ? ' tm-list--' . $args['item_style'] : '';

echo '<ul class="tm-list tm-list--thumb' . esc_attr( $style ) . '">';

foreach ( $content->posts as $content ) {
	// Get the content values.
	$content_title = $content->post_title;
	$permalink     = tm_get_permalink( $content );

	// Featured image.
	$image_url = tm_get_thumbnail_url( $content, 'large' );
	$alt       = ( $image_url ) ? $content_title : '';

	echo '<div class="tm-list__item">
			<img class="tm-list__image" src="' . esc_url( $image_url ) . '" alt="' . esc_attr( $alt ) . '">
			<div class="tm-list__content">
				<a class="tm-list__link" href="' . esc_url( $permalink ) . '">' . esc_html( $content_title ) . '</a><br/>
				<span class="tm-list__date">' . get_the_date( 'jS M Y', $content->ID ) . '</span>
			</div>
		</div>';
}

echo '</ul>';

wp_reset_postdata();
