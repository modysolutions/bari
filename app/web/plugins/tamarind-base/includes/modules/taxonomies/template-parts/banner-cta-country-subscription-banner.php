<?php
/**
 * Template Part: Country Subscriptions Banner
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\taxonomies;

defined( 'ABSPATH' ) || exit;


$banner_bg_color = get_field( 'csb_bgcolor', 'options' );
$banner_text = get_field( 'csb_banner_text', 'options' );
$banner_cta_link = get_field( 'csb_banner_button_link', 'options' );

if ( ! empty( $banner_text ) && ! empty( $banner_cta_link['url'] ) ) :?>
	<section class="block-country-subscriptions-banner text-center"
			style="
				background-color: <?php echo esc_attr( $banner_bg_color ); ?>;
				padding:30px 0;
				width: 100%;
				margin: 0 0 30px 0;
				">
		<div class="container">
			<h3 class="text-white">
				<?php echo esc_html( $banner_text ); ?>
			</h3>
			<div class="mt-30">
				<a href="<?php echo esc_url( $banner_cta_link['url'] ); ?>" class="cta-btn cta-btn-medium cta-btn-gold">
					<?php echo esc_html( $banner_cta_link['title'] ); ?>
				</a>
			</div>
		</div>
	</section>
	<?php
endif;
