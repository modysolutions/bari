<?php
/**
 * Tamarind Accordion Component
 *
 * @package Tamarind_Base
 */

namespace tamarind_base;

defined( 'ABSPATH' ) || exit;

/**
 * Render Tamarind Accordion.
 *
 * @param mixed  $data  The data: can be a WP_Query object or an array of items.
 * @param string $container_id Optional ID for the accordion container.
 *
 * @example
 * <code>
 * $data = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 5 ) );
 * render_tm_accordion( $data );
 *
 * $data = array(
 *     array(
 *         'title' => 'Item 1',
 *         'content' => 'Content for item 1'
 *     ),
 *     array(
 *         'title' => 'Item 2',
 *         'content' => 'Content for item 2'
 *     )
 * );
 * render_tm_accordion( $data );
 * </code>
 *
 * @return string HTML output of the accordion.
 */
function render_tm_accordion( $data, $container_id = '' ) {
	ob_start();

	$items = array();

	if ( $data instanceof \WP_Query ) {
		if ( $data->have_posts() ) {
			while ( $data->have_posts() ) {
				$data->the_post();
				$items[] = array(
					'title'   => get_the_title(),
					'content' => get_the_content(),
				);
			}
			wp_reset_postdata();
		}
	} elseif ( is_array( $data ) ) {
		foreach ( $data as $item ) {
			if ( isset( $item['title'] ) && isset( $item['content'] ) ) {
				$items[] = array(
					'title'   => $item['title'],
					'content' => $item['content'],
				);
			}
		}
	}

	if ( ! empty( $items ) ) :
		// Use provided container ID.
		$container_id = ! empty( $container_id ) ? esc_attr( 'id="' . $container_id . '"' ) : ''; ?>

		<tm-accordion 
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $container_id . ' ';
		?>
		role="region" aria-label="<?php esc_attr_e( 'Accordion', 'tamarind-base' ); ?>">
			<ul>
				<?php
				foreach ( $items as $index => $item ) :
					$item_id = 'accordion-item-' . $index;
					?>
					<li class="accordion-item">
						<button 
							id="<?php echo esc_attr( $item_id ); ?>-button" 
							class="menu-title" 
							aria-expanded="false" 
							aria-controls="<?php echo esc_attr( $item_id ); ?>-panel"
							type="button"
						>
							<?php echo esc_html( $item['title'] ); ?>
						</button>
						<div 
							id="<?php echo esc_attr( $item_id ); ?>-panel" 
							class="menu-options" 
							role="region"
							aria-labelledby="<?php echo esc_attr( $item_id ); ?>-button"
							hidden
						>
							<?php echo wp_kses_post( $item['content'] ); ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		</tm-accordion>
	<?php else : ?>
		<p><?php esc_html_e( 'No items found.', 'tamarind-base' ); ?></p>
		<?php
	endif;

	return ob_get_clean();
}
