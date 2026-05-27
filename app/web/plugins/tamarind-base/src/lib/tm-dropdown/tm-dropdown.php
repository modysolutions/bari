<?php
/**
 * Render Tamarind Dropdown.
 *
 * @package Tamarind_Base
 *
 * @return void
 */

namespace tamarind_base;

defined( 'ABSPATH' ) || exit;


/**
 * Render Tamarind Dropdown.
 * This function generates a dropdown component with a toggle button, header, content, and footer.
 *
 * @param string $id      Unique identifier for the dropdown element.
 * @param string $menu    Text/content for the dropdown toggle button.
 * @param string $header  Header text for the dropdown (optional).
 * @param string $content Main content of the dropdown.
 * @param string $footer  Footer content of the dropdown (optional).
 * @param array  $settings {
 *     Additional settings for the dropdown.
 *     @type string $size Size class for styling (e.g., 'small', 'large').
 * }
 * @return void
 */
function render_tm_dropdown( string $id, string $menu, string $header, string $content, string $footer = '', array $settings = array() ): void {
	$id_attr    = ( $id ) ? ' id="' . esc_attr( $id ) . '"' : '';
	$size_class = isset( $settings['size'] ) ? esc_attr( $settings['size'] ) : '';
	?>

	<tm-dropdown
	<?php
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo $id_attr;
	?>
	>
		<a href="#" class="toggle-button" title="<?php echo esc_attr( $header ); ?>">
			<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $menu;
			?>
		</a>
		<div class="dropdown 
		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $size_class;
		?>
		">

			<div class="dropdown-inner">
				<?php
				if ( $header ) :
					?>
					<div class="dropdown-header"><?php echo esc_html( $header ); ?></div>
					<?php
				endif;
				?>

				<div class="dropdown-content" id="content">
					<?php
					if ( $content ) :
						echo wp_kses_post( $content );
					endif;
					?>
				</div>

				<?php
				if ( $footer ) :
					?>
					<div class="dropdown-footer"><?php echo wp_kses_post( $footer ); ?></div>
				<?php endif; ?>
			</div>

		</div>
		</tm-dropdown>
	<?php
}
