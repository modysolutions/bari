<?php
/**
 * Generates the HTML structure of a post Card with Read More & Date.
 *
 * @package TamarindBase
 * @param \WP_Post $content Post object.
 * @param string $style Custom style.
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

// Show "New" ribbon.
$days = isset( $args['ribbon_days'] ) ? (int) $args['ribbon_days'] : 0;
$new_ribbon_text = __( 'New', 'tamarind-base' );
$show_new_ribbon = should_show_new_ribbon( $content->post_date, $days );

// Get the content values.
$content_id    = $content->ID;
$content_title = $content->post_title;
$permalink     = tm_get_permalink( $content );

// Featured image.
$imagen = tm_get_thumbnail_url( $content, 'large' );
$alt    = ( $imagen ) ? $content_title : '';

// Data attributes for taxonomy terms.
$data_attributes = tm_get_data_attributes( $content_id );


$lock_icon = current_user_can_read_post() ? '<i class="fa fa-unlock" aria-hidden="true"></i>' : '<i class="fa fa-lock" aria-hidden="true"></i>';
?>

<li class="tm-post-card tm-card--date-restricted<?php echo esc_attr( $class_style . $class_slider ); ?>" 
<?php
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $data_attributes;
?>
>
	<article class="tm-post-card__inner">
		<div class="tm-post-card__thumbnail">
			<a href="<?php echo esc_url( $permalink ); ?>">
				<figure class="tm-post-card__image">
					<img src="<?php echo esc_url( $imagen ); ?>" alt="<?php echo esc_attr( $alt ); ?>">
				</figure>
			</a>
			<?php
			if ( $show_new_ribbon ) :
				?>
				<div class="ribbon">
					<?php echo esc_html( $new_ribbon_text ); ?>
				</div>
				<?php
			endif;
			do_action( 'tm_card_after_thumbnail', $content_id );
			?>
		</div>
		<div class="tm-post-card__content">
			<div class="tm-post-card__date"><?php echo get_the_date(); ?></div>
			<div class="tm-post-card__title-wrapper">
				<p class="tm-post-card__lock-icon">
					<?php echo wp_kses_post( $lock_icon ); ?>
				</p>
				<h3 class="tm-post-card__title">
					<a href="<?php echo esc_url( $permalink ); ?>">
						<?php echo esc_html( $content_title ); ?>
					</a>
				</h3>
			</div>
		</div>
	</article>
</li>
