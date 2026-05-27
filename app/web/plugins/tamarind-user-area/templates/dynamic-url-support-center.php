<?php
/**
 * Dynamic URL: Support Centre.
 *
 * @package Tamarind_UserArea
 */

namespace tamarind_userarea;

defined( 'ABSPATH' ) || exit;
?>

<div class="tm-support-centre">
	<header>
		<h2>Support Centre</h2>
	</header>

	<?php if ( have_rows( 'support_center_links', 'option' ) ) : ?>
		<ul class="support-center-links">
			<?php
			while ( have_rows( 'support_center_links', 'option' ) ) :
				the_row();
				$support_link = get_sub_field( 'support_link' );
				?>
				<?php if ( ! empty( $support_link['url'] ) && ! empty( $support_link['title'] ) ) : ?>
					<li>
						<a href="<?php echo esc_url( $support_link['url'] ); ?>" 
						<?php
						if ( ! empty( $support_link['target'] ) ) {
							echo 'target="' . esc_attr( $support_link['target'] ) . '" rel="noopener noreferrer"';
						}
						?>
						class="tm-btn btn-default">
							<?php echo esc_html( $support_link['title'] ); ?>
						</a>
					</li>
				<?php endif; ?>
			<?php endwhile; ?>
		</ul>
	<?php endif; ?>

	<div class="tm-layout-grid tm-layout-grid--large">

		<div class="contact-form">
			<?php
			if ( is_plugin_active( 'tamarind-forms/tamarind-forms.php' ) ) {
				\tamarind_forms\display_form\display_form( 'contact_userarea', true, get_the_id() );
			}
			?>
		</div>
		<div class="contact-information">
			<h3><?php esc_html_e( 'Contact', 'tamarind-user-area' ); ?></h3>
			<?php echo get_field( 'contact_information', 'option' ); ?>
		</div>

	</div>
</div>