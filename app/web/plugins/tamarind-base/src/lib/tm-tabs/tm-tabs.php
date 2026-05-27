<?php
/**
 * Render Tamarind Tabs.
 *
 * @package Tamarind_Base
 */

namespace tamarind_base;

defined( 'ABSPATH' ) || exit;


/**
 * Render Tamarind Tabs.
 *
 * @param array  $tabs Array of tabs with title and content.
 * @param string $container_id Optional ID for the tabs container.
 *
 * @example
 * <code>
 * $tabs = array(
 *     array(
 *         'title' => 'Tab 1',
 *         'content' => 'Content for Tab 1'
 *     ),
 *     array(
 *         'title' => 'Tab 2',
 *         'content' => 'Content for Tab 2'
 *     ),
 * );
 * render_tm_tabs($tabs);
 * </code>
 *
 * @return string HTML output of the tabs.
 */
function render_tm_tabs( $tabs, $container_id = '' ) {
	ob_start();

	// Validate input.
	if ( ! is_array( $tabs ) || empty( $tabs ) ) { ?>
		<p><?php esc_html_e( 'No tabs found.', 'tamarind-base' ); ?></p>
		<?php
		return ob_get_clean();
	}

	// Generate unique IDs.
	$tab_ids = array_map(
		function ( $index ) {
			return 'tab-' . ( $index + 1 );
		},
		array_keys( $tabs )
	);

	// Generate panel IDs.
	$panel_ids = array_map(
		function ( $index ) {
			return 'panel-' . ( $index + 1 );
		},
		array_keys( $tabs )
	);

	// Use provided container ID.
	$container_id = ! empty( $container_id ) ? esc_attr( 'id="' . $container_id . '"' ) : '';
	?>

	<tm-tabs 
	<?php
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $container_id;
	?>
	role="tablist">
		<nav class="tabs-header">
			<?php
			foreach ( $tabs as $index => $tab ) :
				?>
				<?php
				if ( ! empty( $tab['title'] ) ) :
					?>
					<button
						id="<?php echo esc_attr( $tab_ids[ $index ] ); ?>"
						class="tab-title <?php echo 0 === $index ? 'active' : ''; ?>"
						role="tab"
						aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>"
						aria-controls="<?php echo esc_attr( $panel_ids[ $index ] ); ?>"
						tabindex="<?php echo 0 === $index ? '0' : '-1'; ?>"
						data-target="<?php echo esc_attr( $panel_ids[ $index ] ); ?>">
						<?php echo esc_html( $tab['title'] ); ?>
					</button>
				<?php endif; ?>
			<?php endforeach; ?>
		</nav>

		<div class="tabs-content">
			<?php
			foreach ( $tabs as $index => $tab ) :
				?>
				<?php
				if ( ! empty( $tab['content'] ) ) :
					?>
					<div
						id="<?php echo esc_attr( $panel_ids[ $index ] ); ?>"
						class="tab-content <?php echo 0 === $index ? 'active' : ''; ?>"
						role="tabpanel"
						aria-labelledby="<?php echo esc_attr( $tab_ids[ $index ] ); ?>"
						tabindex="0"
						<?php echo 0 !== $index ? 'hidden' : ''; ?>>
						<?php echo wp_kses_post( $tab['content'] ); ?>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</tm-tabs>

	<?php
	return ob_get_clean();
}
