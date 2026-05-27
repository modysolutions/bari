<?php
/**
 * The template for displaying Topics Taxonomy Archive.
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\taxonomies;

defined( 'ABSPATH' ) || exit;


$el_term = get_queried_object();

$term_id           = $el_term->term_id;
$term_slug         = $el_term->slug;
$is_landing_module = get_field( 'topic_module_landing', 'topics_' . $term_id );

$current_page = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
?>

<?php get_header(); ?>
<div id="main-content">

	<div class="super_title">
		<div class="wrap">
			<h1>
			<?php single_cat_title(); ?>
			</h1>
		</div>
	</div>

	<div id="topics-content">
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

				if ( $is_landing_module ) {
					// landing of Topic format Geography by modules.
					include plugin_dir_path( __FILE__ ) . '../template-parts/modules.php';
				} else {
					// List Topics.
					if ( have_posts() ) :
						?>
						<ul class="tm-layout-grid tm-layout-grid--fullwidth">
							<?php
							while ( have_posts() ) :
								the_post();

								$is_alert = \tamarind_base\is_alert();

								if ( $is_alert ) {
									// Use alert card for alert content type.
									\tamarind_base\print_post_card( get_post(), 'grid', 'horizontal-alert', 'taxonomy' );
								} else {
									// Use regular card for other content types.
									\tamarind_base\print_post_card( get_post(), 'grid', 'horizontal', 'taxonomy' );
								}
							endwhile;
							?>
						</ul>
						<?php
						$bottom_term_text = get_field( 'bottom_description', $el_term );

						if ( $bottom_term_text && ! empty( $bottom_term_text ) ) {
							?>
							<div class="term-bottom-description term-bottom-description-geography mt-30 mb-30">
								<?php echo wp_kses_post( $bottom_term_text ); ?>
							</div>
							<?php
						}

						?>
						<div class="tm-pagination">
							<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo paginate_links(
								array(
									'format'   => '?paged=%#%',
									'current'  => max( 1, $current_page ),
									'mid_size' => 5,
								)
							);
							?>
						</div>
						<?php
					endif;
				}
				?>
			</main> 
		</div> 
	</div> 
</div> 

<?php get_footer(); ?>
