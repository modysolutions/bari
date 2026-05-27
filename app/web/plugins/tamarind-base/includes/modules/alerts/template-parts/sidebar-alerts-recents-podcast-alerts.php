<?php
/**
 * Template for Alerts Podcasts module sidebar.
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\alerts;

defined( 'ABSPATH' ) || exit;

$title_module    = __( 'Alerts Podcasts', 'tamarind-base' );
$number_of_items = 5;
$container_style = 'tm-sidebar-module--light';
$item_style      = 'dark';

$args = array(
	'post_type'      => 'post',
	'posts_per_page' => $number_of_items,
	'tax_query'      => array( // phpcs:ignore
		array(
			'taxonomy' => 'content_types',
			'field'    => 'slug',
			'terms'    => 'regulatory-alerts-podcasts',
		),
	),
);

$cache_args = array(
	'subscription_plan' => \tamarind_subscriptions\subscription_plan\get_user_plan_id(),
	'args'    => $args,
);
ksort( $cache_args );
$cache_key = 'sidebar_alerts_recents_podcasts_' . md5( serialize( $cache_args ) );
$cache_group = 'alerts';
$alerts_contents = wp_cache_get( $cache_key, $cache_group );
if(!$alerts_contents){
	$alerts_contents = new \WP_Query( $args );
	wp_cache_set( $cache_key, $alerts_contents, $cache_group, 10 * MINUTE_IN_SECONDS );
}
?>

<section class="alerts-podcasts-sidebar tm-sidebar-module <?php echo esc_attr( $container_style ); ?>">

	<h2 class="tm-sidebar-module__title"><?php echo esc_html( $title_module ); ?></h2>

	<?php \tamarind_base\print_post_list( $alerts_contents, 'default', $item_style ); ?>

	<p style="margin-top:20px">
		<a href="<?php echo esc_url( get_term_link( 'regulatory-alerts-podcasts', 'content_types' ) ); ?>">
			<?php esc_html_e( 'Browse archives', 'tamarind-base' ); ?>
		</a>
	</p>

</section>
