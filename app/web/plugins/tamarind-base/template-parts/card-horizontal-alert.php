<?php
/**
 * Generates the HTML structure of a post Card Horizontal with Thumbnail & excerpt for Alerts.
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

// Featured image.
$imagen = tm_get_thumbnail_url( $content, 'medium' );
$alt    = ( $imagen ) ? $content_title : '';

// Data attributes for taxonomy terms.
$data_attributes = tm_get_data_attributes( $content_id );
$taxonomy_links = generate_formatted_taxonomy_links();

$can_read = current_user_can_read_post();
if ( $can_read ) {
	$locked = '<i class="fa fa-unlock" aria-hidden="true"></i>';
} else {
	$locked = '<i class="fa fa-lock" aria-hidden="true"></i>';
}
?>

<li class="tm-post-card tm-post-card--horizontal<?php echo esc_attr( $class_style . $class_slider ); ?>" 
<?php
// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo $data_attributes;
?>
>
	<article class="tm-post-card__inner tm-post-card--alert">

		<div class="tm-post-card__left">
			<figure class="tm-post-card__image favourite-icon-inside">
				<img src="<?php echo esc_url( $imagen ); ?>" loading="lazy" alt="<?php echo esc_attr( $alt ); ?>">
				<?php do_action( 'tm_card_after_thumbnail', $content_id ); ?>
			</figure>
		</div>

		<div class="tm-post-card__right">
			<div class="tm-post-card__excerpt">
				<?php
				if ( $can_read ) {
					the_content();
				} else {
					?>
					<h2 class="tm-post-card__title"><?php echo esc_html( $content_title ); ?></h2>
					<?php
				}
				?>

				<div class="tm-post-card__meta">
					<?php echo ( '<h4>' . get_the_date() . ' - ' . wp_kses_post( $taxonomy_links ) . ' | ' . wp_kses_post( $locked ) . '</h4>' ); ?>
				</div>
			</div>
		</div>

	</article>
</li>
