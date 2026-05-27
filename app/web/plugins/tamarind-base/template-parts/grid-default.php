<?php
/**
 * Generates the HTML structure of a post Slider Layout1.
 *
 * @param \WP_Query $content Query with the posts to display.
 * @param string $item_layout Card layout style.
 * @param string $item_style Custom card style.
 * @return string HTML of the Slider.
 *
 * @package Tamarind_Base
 */

namespace tamarind_base;

defined( 'ABSPATH' ) || exit;

// Get the layout values.
if ( ! $content ) {
	return '';
}
$item_layout          = $args['item_layout'];
$item_style           = $args['item_style'];
$template_type_parent = $template_type;
?>

<ul class="tm-layout-grid">            
	<?php
	while ( $content->have_posts() ) :
		$content->the_post();
		print_post_card( get_post(), $template_type_parent, $item_layout, $item_style );
	endwhile;
	wp_reset_postdata();
	?>
</ul>
