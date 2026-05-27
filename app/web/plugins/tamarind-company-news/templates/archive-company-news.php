<?php
/**
 * The template for displaying archive pages for the "Company News" CPT.
 *
 * @package Tamarind_Company_News
 */

namespace tamarind_company_news;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

\tamarind_userarea\redirect_if_no_access_cpt();

$current_page = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

$args = array(
	'post_type'        => 'company-news',
	'post_status'      => 'publish',
	'posts_per_page'   => 8,
	'paged'            => $current_page,
	'suppress_filters' => true, // Ignore filters from plugins like Groups.
);

$company_news_query = new \WP_Query( $args );
?>

<div id="main-content">
	<div class="super_title">
		<div class="wrap">
			<h1>
				<?php post_type_archive_title(); ?>
			</h1>
		</div>
	</div>

	<div id="tm-archive-company-news" class="tm-layout-main tm-layout-wrapper">

		<?php
		if ( $company_news_query->have_posts() ) :
			echo '<ul class="tm-layout-grid tm-layout-grid--fullwidth">';

			foreach ( $company_news_query->posts as $company_news ) {

				// Display the company news in a grid layout.
				\tamarind_base\print_post_card( $company_news, 'grid', 'horizontal' );
			}
			echo '</ul>';
			?>

		<?php else : ?>
			<p><?php esc_html_e( 'No company news found.', 'tm-company-news' ); ?></p>
		<?php endif; ?>

	</div>

	<div class="tm-layout-wrapper">
		<div class="tm-pagination">
			<?php
			// Pagination.
			echo paginate_links( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				array(
					'format'  => '?paged=%#%',
					'current' => max( 1, $current_page ),
					'total'   => $company_news_query->max_num_pages,
				)
			);
			?>
		</div>
	</div>
</div>

<?php
wp_reset_postdata();
get_footer(); ?>
