<?php
/**
 * Generates the HTML structure of a post List Layout Default.
 * List with thumbnail, title and date.
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
?>

<ul class="tm-list tm-list--index-disc<?php echo esc_attr( $style ); ?>">
	<?php
	foreach ( $content->posts as $item ) {
		// Get the content values.
		$post_title = $item->post_title;
		$permalink  = tm_get_permalink( $item );
		?>

		<li class="tm-list__item">
			<a href="<?php echo esc_url( $permalink ); ?>" class="tm-list__link">
				<?php echo esc_html( $post_title ); ?>
			</a>
		</li>
		<?php
	}
	wp_reset_postdata();
	?>
</ul>
