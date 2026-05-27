<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase
/**
 * Template for displaying taxonomy content types podcasts.
 * Taxonomy archive: unified podcasts list (N taxonomies via ACF).
 *
 * @package tamarind_templates_custom_lists
 */

get_header();


$current_page = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

$podcast_term     = get_term_by( 'slug', get_query_var( 'term' ), get_query_var( 'taxonomy' ) );
$term_name        = $podcast_term ? $podcast_term->name : 'Podcasts';
$term_description = term_description();
$podcast_title    = get_field( 'podcasts_archive_title', 'option' ) ? get_field( 'podcasts_archive_title', 'option' ) : '';
?>
<div class="super_title">
	<div class="wrap"><?php echo esc_html( $term_name ); ?></div>
</div>
<div class="wrap">
	<div id="content-area" class="tm-layout-main tm-layout-wrapper">
		<main class="tm-layout-main__content tm-archive-taxonomy">

			<?php
			$term_description = term_description();
			if ( ! empty( $term_description ) ) {
				?>
				<div class="taxonomy-description">
					<?php echo wp_kses_post( $term_description ); ?>
				</div>
				<?php
			}

			if ( $podcast_title ) {
				?>
				<h2 class="new-search-results__title"><?php echo esc_html( $podcast_title ); ?></h2>
				<?php
			}
			?>
			<div class="new-search-results new-taxonomy-results">
			<?php
			if ( have_posts() ) {
				while ( have_posts() ) :
					the_post();
					// TODO! Change with card into plugin tamarind-base when merge with releae/subscriptions.
					include trailingslashit( tamarind_templates_custom_lists_PATH ) . 'template-parts/podcast-item.php';
				endwhile;
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
			} else {
				?>
				<p><?php esc_html_e( 'No Podcasts found.', 'tamarind-templates-custom-lists' ); ?></p>
				<?php
			}
			?>
			</div>
		</main>		
	</div>
</div>
<?php get_footer(); ?>
