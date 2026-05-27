<?php
/**
 * Generates the HTML structure of a post Slider Layout2: Swiper Slider.
 *
 * @package TamarindBase
 * @param \WP_Query $content Query with the posts to display.
 * @param string $item_layout Card layout style.
 * @param string $item_style Custom card style.
 * @return string HTML of the Slider.
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

$unique_id = 'slider-' . uniqid();
?>

<div class="swiper-container">
	<div class="swiper" id="<?php echo esc_attr( $unique_id ); ?>">
		<ul class="swiper-wrapper">
			<?php
			while ( $content->have_posts() ) :
				$content->the_post();
				print_post_card( get_post(), $template_type_parent, $item_layout, $item_style );
			endwhile;
			wp_reset_postdata();
			?>
		</ul>
	</div>
	<div class="swiper-btn-next" data-swiper-target="<?php echo esc_attr( $unique_id ); ?>">
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo get_svg_icon( 'nav-right', 'icon-slider-nav', 'nav right' );
		?>
	</div>
	<div class="swiper-btn-prev" data-swiper-target="<?php echo esc_attr( $unique_id ); ?>">
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo get_svg_icon( 'nav-left', 'icon-slider-nav', 'nav left' ); ?>
	</div>
	<div class="swiper-pagination" data-swiper-target="<?php echo esc_attr( $unique_id ); ?>">
	</div>
</div>

<?php
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo call_swiper_slider( $unique_id, 3, true, false );
