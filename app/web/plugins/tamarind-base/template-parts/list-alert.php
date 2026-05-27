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

use function tamarind_notifications\tm_format_notification_date_archive;

defined( 'ABSPATH' ) || exit;


// Get the layout values.
if ( ! $content ) {
	return '';
}
$style = ( $args['item_style'] ) ? ' tm-list--' . $args['item_style'] : '';
?>
<ul class="widget-content-list tm-list tm-list--alert tm-list--index-disc<?php echo esc_attr( $style ); ?>">
	<?php
	if ( count( $content->posts ) > 0 ) {
		$counter = 1;
		foreach ( $content->posts as $item ) {
			?>
			<li class="tm-list__item tm-list__item--alert">
				<div id="tm-list__item--alert-<?php echo $counter; ?>" class="tm-list--alert-content">
					<?php echo apply_filters( 'the_content', ( $item->post_content ) ); ?>
				</div>
				<button class="tm-list--alert-button" aria-controls="tm-list__item--alert-<?php echo $counter; ?>" aria-expanded="false" title="Mostrar más">
					<i class='fa fa-plus'></i>
				</button>
                <div class="tm-list--alert-content" style="color: var(--color-text-lightgray);">
                    <?php $date = tm_format_notification_date_archive($item->post_date, false); ?>
                    <small><em><?php echo $date['full_date']; ?></em></small>
                </div>
			</li>
			<?php
			++$counter;
		}
	}
	wp_reset_postdata();
	?>
</ul>
