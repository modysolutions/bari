<?php
/**
 * Generates the HTML structure of a post Card Horizontal with Thumbnail & excerpt.
 *
 * @package Tamarind_Base
 */

namespace tamarind_base;

defined( 'ABSPATH' ) || exit;

use function tamarind_subscriptions\access\{current_user_can_read_post};

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
$imagen = tm_get_thumbnail_url( $content, 'medium' );
$alt    = ( $imagen ) ? $content_title : '';

// Data attributes for taxonomy terms.
$data_attributes = tm_get_data_attributes( $content_id );
$taxonomy_links = generate_formatted_taxonomy_links();

$can_read = current_user_can_read_post();
if ( $can_read ) {
	$locked = get_svg_icon( 'search-user-access-icon', 'icon-candado icon-padlock', __( 'Content included in subscription', 'tamarind-base' ) );
} else {
	$locked = get_svg_icon( 'search-user-access-icon-locked', 'icon-candado icon-padlock-locked', __( 'Content not included in subscription', 'tamarind-base' ) );
}
?>

<li class="tm-post-card tm-post-card--horizontal<?php echo esc_attr( $class_style . $class_slider ); ?>" 
<?php
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $data_attributes;
?>
>
	<article id="post-<?php echo esc_attr( $content_id ); ?>" class="tm-post-card__inner">

		<div class="tm-post-card__left">
			<a href="<?php echo esc_url( $permalink ); ?>">
				<figure class="tm-post-card__image favourite-icon-inside">
					<img src="<?php echo esc_url( $imagen ); ?>" alt="<?php echo esc_attr( $alt ); ?>">
					<?php do_action( 'tm_card_after_thumbnail', $content_id ); ?>
				</figure>
			</a>
		</div>

		<div class="tm-post-card__right">
			<div class="tm-post-card__header tm-post-card__header--with-icon">
				<div class="new-search-card-icon" title="<?php echo ( $can_read ) ? esc_attr__( 'Content included in subscription', 'tamarind-base' ) : esc_attr__( 'Content not included in subscription', 'tamarind-base' ); ?>">
					<?php echo $locked; // ignore:phpcs ?>
				</div>
				<div class="tm-post-card__text">
					<h2 class="tm-post-card__title">
						<a href="<?php echo esc_url( $permalink ); ?>">
							<?php echo esc_html( $content_title ); ?>
						</a>
					</h2>
					<h4 class="tm-post-card__meta">
						<?php
						echo esc_html__( 'Written by', 'tamarind-base' ) . ' ' . get_the_author() . ' | ' . get_the_date( '', $content_id );
						if ( $taxonomy_links ) {
							echo ' | ' . wp_kses_post( $taxonomy_links );
						}
						?>
					</h4>
				</div>			
			</div>
			<div class="tm-post-card__excerpt">
				<?php the_excerpt(); ?>
			</div>
		</div>

	</article>
</li>
