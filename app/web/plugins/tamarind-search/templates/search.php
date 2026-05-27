<?php

/**
 * The template for displaying Search results
 */

get_header();
$search_string = get_search_query();
if ($search_string == '') {
	$array_get = $_GET;
	foreach ($array_get['taxo'] as $item) {
		if ($item['name'] == 'content_types' || $item['name'] == 'geography') {
			if ($item['term'] != 'uwpqsftaxoall') {
				$search_string .= $item['term'] . ',';
			}
		}
	}
	$search_string = rtrim($search_string, ", ");
}
?>
<div class="super_title">
	<div class="wrap">
		<h1><?php printf(__('Search Results: %s', 'shape'), '<span>' . $search_string . '</span>'); ?></h1>
	</div>
</div>
<div class="new-search wrap">
	<div class="new-search-filter">
		<h2 class="new-search-filter-title new-sidebar-filter-title-search-mobile">
            <?php _e( 'Filter by', 'tm-search' ); ?>
			<span class="fa fa-chevron-down icono-filtro-title"></span>
		</h2>
		<?php get_sidebar(); ?>
		<span class="fa fa-times new-sidebar-close-filter-search-mobile"></span>
		<a class="new-sidebar-continue-filter-search-mobile">
            <?php _e( 'Continue', 'tm-search' ); ?>
        </a>

	</div>
	<div class="new-search-results">

		<?php
		// new featured search results by custom field
		$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
		if ($paged == 1) {
			include(plugin_dir_path(__FILE__) . 'search-featured-results.php');
		}

		if (have_posts()) : ?>
			<div class="common-search-results">
			<?php
			while (have_posts()) : the_post();
				$post_id = get_the_ID();
				include(plugin_dir_path(__FILE__) . 'classic-search-result.php');
			endwhile;

			echo '<div class="infinite-loader-oberserver"></div>';
			?>
			<div class="tm-pagination">
				<?php
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo paginate_links(
					array(
						'format'   => '?paged=%#%',
						'current'  => max( 1, $paged ),
						'mid_size' => 5,
					)
				);
				?>
			</div>
			<?php
		else :
			get_template_part('includes/no-results', 'index');
		endif;
			?>
			</div>

	</div>

</div>

<?php get_footer(); ?>