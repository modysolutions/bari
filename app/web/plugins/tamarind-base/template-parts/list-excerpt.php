<?php
/**
 * Generates the HTML structure of a post List Layout with excerpt.
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

echo '<ul class="tm-list tm-list--excerpt' . esc_attr( $style ) . '">';

foreach ( $content->posts as $content ) {
	// Get the content values.
	$content_title = $content->post_title;
	$permalink     = tm_get_permalink( $content );
	$date          = \tamarind_notifications\tm_format_notification_date( $content->ID );
	$excerpt       = get_the_excerpt( $content->ID );

	echo '<li class="tm-list__item">
		<div class="tm-list__header">
			<a href="' . esc_url( $permalink ) . '" class="tm-list__title">' . esc_html( $content_title ) . '</a>
			<span class="tm-list__date">' . esc_html( $date ) . '</span>
		</div>
		<div class="tm-list__excerpt">' . esc_html( $excerpt ) . '</div>
	</li>';
}

echo '</ul>';

wp_reset_postdata();
