<?php
/**
 * Generates the HTML structure of a post Card Default with user access icon.
 *
 * @package TamarindBase
 *
 * @param \WP_Post $content Post object.
 * @param string $item_style Custom style.
 * @return string HTML of the card.
 */

namespace tamarind_base;

use function tamarind_subscriptions\access\{current_user_can_read_post};

defined( 'ABSPATH' ) || exit;

// Get the layout values.
$class_slider = ( 'slider' === $args['template_type_parent'] ) ? ' swiper-slide' : '';
$class_style  = ( $args['item_style'] ) ? ' tm-post-card--' . $args['item_style'] : '';
if ( ! is_a( $content, 'WP_Post' ) ) {
	return '';
}

// Get the content values.
$content_id    = $content->ID;
$content_title = $content->post_title;
$permalink     = tm_get_permalink( $content );

// Featured image.
$imagen = tm_get_thumbnail_url( $content, 'large' );
$alt    = ( $imagen ) ? $content_title : '';

// Data attributes for taxonomy terms.
$data_attributes = tm_get_data_attributes( $content_id );
?>

<li class="tm-post-card tm-card--user-access<?php echo esc_attr( $class_style . $class_slider ); ?>" 
<?php
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $data_attributes;
?>
>
	<article class="tm-post-card__inner">
		<div class="tm-post-card__thumbnail">
			<a href="<?php echo esc_url( $permalink ); ?>">
				<figure class="tm-post-card__image">
					<img src="<?php echo esc_url( $imagen ); ?>" loading="lazy" alt="<?php echo esc_attr( $alt ); ?>">
				</figure>
			</a>
			<?php do_action( 'tm_card_after_thumbnail', $content_id ); ?>
		</div>
		<div class="tm-post-card__content">
			<a href="<?php echo esc_url( $permalink ); ?>">
				<h3 class="tm-post-card__title"><?php echo esc_html( $content_title ); ?></h3>
			</a>
			<div class="tm-post-card__footer">
				<div class="tm-post-card__footer-left">
					<div class="tm-post-card__footer-left-date"><?php echo get_the_date( 'jS M Y', $content_id ); ?></div>
					<div class="tm-post-card__footer-left-content-type">
						<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo getParentContentTypesById( $content_id );
						?>
					</div>
				</div>
				<div class="tm-post-card__footer-right">
					<?php
					if ( current_user_can_read_post() ) {
						echo get_svg_icon( 'user-access-icon-2', 'icon-candado', 'open' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					} else {
						echo get_svg_icon( 'user-access-icon-locked-2', 'icon-candado', 'locked' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					?>
				</div>
			</div>
		</div>
	</article>
</li>
