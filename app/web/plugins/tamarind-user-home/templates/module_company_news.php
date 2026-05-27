<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase
/**
 * Template for Company News layout.
 *
 * @package Tamarind_UserHome
 */

namespace tamarind_userhome;

defined( 'ABSPATH' ) || exit;

if ( false === \tamarind_userarea\check_cpt_access( 'company-news' ) ) {
	return;
}

// Get the layout values.
$title_module    = get_sub_field( 'title' );
$number_of_items = get_sub_field( 'number_of_items' );
$container_style = ( get_sub_field( 'container_style' ) ) ? ' tm-module--' . get_sub_field( 'container_style' ) : '';
$item_style      = get_sub_field( 'item_style' );

// Get the Company News.
$company_news = get_query_company_news( $number_of_items ); ?>

<section class="company-news-module tm-module<?php echo esc_attr( $container_style ); ?>">
	<?php if ( $title_module ) { ?>
		<h2 class="new-module-label"><?php echo esc_html( $title_module ); ?></h2>
	<?php } ?>
	<div class="company-news-module__inner">
		<?php \tamarind_base\print_grid( $company_news, 'default', 'date', $item_style ); ?>
	</div>
</section>
