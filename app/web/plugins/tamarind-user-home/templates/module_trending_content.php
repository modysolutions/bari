<?php
/**
 * Template for Trending Content layout.
 *
 * @package Tamarind_UserHome
 */

namespace tamarind_userhome;

defined( 'ABSPATH' ) || exit;


// Get the layout values.
$module_title    = get_sub_field( 'title' );
$number_of_items = get_sub_field( 'number_of_items' );
$container_style = ( get_sub_field( 'container_style' ) ) ? ' tm-module--' . get_sub_field( 'container_style' ) : '';
$item_style      = get_sub_field( 'item_style' );

// Get Trending Content posts.
$trending_contents = get_sub_field( 'trending_content_data' );
if ( empty( $trending_contents ) ) {
	echo '<p>You have no selected trending content.</p>';
	return;
}
$trending_query = \tamarind_base\get_posts_by_ids( $trending_contents, $number_of_items, 'post', 'post__in' );
?>

<section class="trending-content-module tm-module<?php echo esc_attr( $container_style ); ?>">

	<?php if ( $module_title ) { ?>
		<h2 class="new-module-label"><?php echo esc_html( $module_title ); ?></h2>
	<?php } ?>

	<div class="trending-content-module__inner">
		<?php \tamarind_base\print_grid( $trending_query, 'default', 'readmore', $item_style ); ?>
	</div>

</section>
