<?php

namespace tamarind_base;

defined( 'ABSPATH' ) || exit;

use function tamarind_subscriptions\subscription_plan\get_user_plan_id;

add_action( 'parse_query', __NAMESPACE__ . '\cache_main_query', 1 );
function cache_main_query(): void {
	$user_id = get_current_user_id();
	$cache_group = 'main_query_anonymous';
	if ( $user_id ) {
		$subscription_plan = get_user_plan_id();
		$cache_group = 'main_query_' . md5($subscription_plan . $user_id);
	}
	$cache_key = 'main_query_custom_' . md5(serialize($GLOBALS['wp_query']->query_vars));
	$cached_data = wp_cache_get( $cache_key, $cache_group );

	if ( false !== $cached_data ) {
		$GLOBALS['wp_query'] = unserialize($cached_data);
		return;
	}

	add_action( 'wp_footer', function() use ( $cache_key, $cache_group ) {
		global $wp_query;
		wp_cache_set( $cache_key, $cache_group, $wp_query, HOUR_IN_SECONDS );
	});
}
