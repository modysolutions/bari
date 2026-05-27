<?php
/**
 * Template Part: Modules
 * @package ecig
 */

namespace tamarind_base\taxonomies;

$home_modules = array();
$queried_object = get_queried_object();

$cache_key_args = array(
	'subscription_plan' => \tamarind_subscriptions\subscription_plan\get_user_plan_id(),
	'queried_object' => $queried_object,
);

ksort($cache_key_args);
$cache_key = 'geography_posts_' . md5( serialize( $cache_key_args ) );
$cache_group = 'posts';
$cache_output = wp_cache_get( $cache_key, $cache_group );
if ( false !== $cache_output ) {
	$home_modules = $cache_output;
} else {
	if (have_rows('modules_homepage_blocks', 'options')) {
		while (have_rows('modules_homepage_blocks', 'options')) {
			the_row();
			$module_term    = get_sub_field('module_homepage_block');
			$home_modules[] = [
				'slug' => $module_term->slug,
				'posts' => get_sub_field('module_homepage_blocks_items_amount') ?: 1,
				'days' => get_sub_field('module_homepage_blocks_items_days_as_new_content') ?: 30,
			];
			wp_cache_set( $cache_key, $home_modules, $cache_group, 10 * MINUTE_IN_SECONDS );
		}
	}
}

$taxonomy = '';
if ( isset( $queried_object->taxonomy ) ) {
	$taxonomy = $queried_object->taxonomy;
} elseif ( is_singular() ) {
	// TODO: send $template_vars as args?
	$taxonomy = get_query_var('taxonomy');
}

$geography = '';
$topic = '';
if($taxonomy === 'geography') {
	// TODO: send $template_vars as args?
	$geography = get_query_var( 'geography' );
} elseif ($taxonomy === 'topics') {
	$topics_map = array(
		'eu-law-policy' => 'europe',
	);
	$topic = get_query_var('topics');
	if(in_array($topic, array_keys($topics_map))){
		$geography = $topics_map[$topic];
	}
} elseif(is_page()) {
	$geography = get_post_field('post_name');
}

echo print_modules( $home_modules, $geography, $topic );
