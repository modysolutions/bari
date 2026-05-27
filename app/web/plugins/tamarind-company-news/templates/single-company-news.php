<?php
/**
 * The template for displaying single "Company News" CPT.
 *
 * @package Tamarind_Company_News
 */

namespace tamarind_company_news;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

\tamarind_userarea\redirect_if_no_access_cpt();
?>
<div class="super_title">
	<div class="wrap">
		<a href="<?php echo esc_url( get_post_type_archive_link( 'company-news' ) ); ?>"><?php esc_html_e( 'Company News', 'tm-company-news' ); ?></a>
	</div>
</div>

<div id="tm-single-company-news" class="tm-layout-main tm-layout-wrapper tm-layout-main--sidebar tm-layout-main--sidebar-right">
	<main class="tm-layout-main__content">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<h1 class="entry-title"><?php the_title(); ?></h1>
				<div class="entry-date"><?php echo get_the_date( 'jS M Y', get_the_ID() ); ?></div>
				<div class="entry-content single-content">
					<?php the_content(); ?>
				</div>
			</article>
		<?php endwhile; ?>
	</main>
	<aside class="tm-layout-main__aside">
		<?php
		// Display Company News Sidebar.
		$title_other = __( 'Other company news', 'tm-company-news' );
		$limit       = 5;
		?>
		<section class="company-news-sidebar tm-sidebar-module">
			<h2 class="tm-sidebar-module__title"><?php echo esc_html( $title_other ); ?></h2>
			<?php
			$args = array(
				'post_type'      => 'company-news',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				'orderby'        => 'date',
				'order'          => 'DESC',
			);

			$company_news_query = new \WP_Query( $args );
			// Check if there are company news.
			if ( ! $company_news_query->have_posts() ) {
				echo '<div class="dropdown-item dropdown-info">No company news found.</div>';
				return ob_get_clean();
			}

			// Displays the list of company news.
			\tamarind_base\print_post_list( $company_news_query, 'thumbnail' );

			// Get the URL for the "View all" link.
			$my_company_news_url = get_post_type_archive_link( 'company_news' );
			echo '<p class="tm-sidebar-module__view-more"><a href="' . esc_url( $my_company_news_url ) . '">' . esc_html__( 'View all', 'tm-company-news' ) . '</a></p>';
			?>
		</section>
	</aside>
</div>
<?php get_footer(); ?>
