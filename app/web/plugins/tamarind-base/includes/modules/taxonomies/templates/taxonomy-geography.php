<?php
/**
 * The template for displaying Geography Taxonomy Archive.
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\taxonomies;

defined( 'ABSPATH' ) || exit;


get_header();

$id_tax         = get_queried_object()->term_id;
$geo_info       = get_queried_object();
$friendly_title = get_field( 'geo_friendly_title', $geo_info );
?>
<div id="geography-content">
	<div class="super_title">
		<div class="wrap">
			<h1>
				<?php echo ( esc_html( $friendly_title ) && ! empty( $friendly_title ) ) ? esc_html( $friendly_title ) : esc_html( single_term_title( '', false ) ); ?>
			</h1>
		</div>
	</div>
	<div id="content-area" class="tm-layout-main tm-layout-wrapper">
		<main class="tm-layout-main__content tm-archive-taxonomy">

			<?php
			$term_description = term_description();
			if ( ! empty( $term_description ) ) {
				?>
				<div class="term-description term-description-geographies mt-30">
					<?php echo wp_kses_post( $term_description ); ?>
				</div>
				<?php
			}

			require plugin_dir_path( __FILE__ ) . '../template-parts/modules.php';

			$bottom_term_text = get_field( 'bottom_description', $geo_info );
			if ( $bottom_term_text && ! empty( $bottom_term_text ) ) :
				?>
				<div class="term-bottom-description term-bottom-description-geography mt-30 mb-30">
					<?php echo wp_kses_post( $bottom_term_text ); ?>
				</div>
				<?php
			endif;

			if ( ! is_user_logged_in() ) :
				require plugin_dir_path( __FILE__ ) . '../template-parts/banner-cta-register.php';
			endif;
			?>
		</main>
	</div><!-- wrap -->
</div>

<?php get_footer(); ?>
