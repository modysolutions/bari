<?php
/**
 * Template name: Content type archives
 *
 * @package tamarind_templates_custom_lists
 */

get_header();

$current_page = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

$included_terms    = get_field( 'tm_templates_content_types_includes', 'option' );
$tax_query         = $included_terms ? array(
	array(
		'taxonomy' => 'content_types',
		'field'    => 'term_id',
		'terms'    => $included_terms,
		'operator' => 'IN',
	),
) : null;
$args              = array(
	'post_type' => 'post',
	'tax_query' => $tax_query,
);
if ( get_query_var( 'paged' ) ) {
	$args['paged'] = get_query_var( 'paged' );
}
if ( get_query_var( 's' ) ) {
	$args['s'] = get_query_var( 's' );
}
if ( get_query_var( 'content_types' ) ) {
	$args['content_types'] = get_query_var( 'content_types' );
}
query_posts( $args );
?>
	<div class="super_title">
		<div class="wrap"><?php the_title(); ?></div>
	</div>

	<div id="content-area" class="tm-layout-main tm-layout-wrapper">
		<main class="tm-layout-main__content tm-archive-taxonomy">	

			<div class="new-search-results new-taxonomy-results">
				<?php
				if ( have_posts() ) :
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
				endif;
				?>
			</div>

		</main>
	</div>
<?php
get_footer();
