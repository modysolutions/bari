<?php
/**
 * Custom Posts Types for Tamarind Subscription.
 *
 * @package tamarind_subscriptions
 */

namespace tamarind_subscriptions\subscription_plan;

defined( 'ABSPATH' ) || exit;

add_action( 'init', __NAMESPACE__ . '\register_subscription_plan_cpt' );

/**
 * Register the Subscription Plan Custom Post Type.
 *
 * @return void
 */
function register_subscription_plan_cpt() : void {
	$labels = array(
		'name'                  => __( 'Subscription Plans', 'tamarind-subscriptions' ),
		'singular_name'         => __( 'Subscription Plan', 'tamarind-subscriptions' ),
		'menu_name'             => __( 'Subscription Plans', 'tamarind-subscriptions' ),
		'name_admin_bar'        => __( 'Subscription Plan', 'tamarind-subscriptions' ),
		'add_new'               => __( 'Add New', 'tamarind-subscriptions' ),
		'add_new_item'          => __( 'Add New Subscription Plan', 'tamarind-subscriptions' ),
		'new_item'              => __( 'New Subscription Plan', 'tamarind-subscriptions' ),
		'edit_item'             => __( 'Edit Subscription Plan', 'tamarind-subscriptions' ),
		'view_item'             => __( 'View Subscription Plan', 'tamarind-subscriptions' ),
		'all_items'             => __( 'All Subscription Plans', 'tamarind-subscriptions' ),
		'search_items'          => __( 'Search Subscription Plans', 'tamarind-subscriptions' ),
		'parent_item_colon'     => __( 'Parent Subscription Plan:', 'tamarind-subscriptions' ),
		'not_found'             => __( 'No subscription plans found.', 'tamarind-subscriptions' ),
		'not_found_in_trash'    => __( 'No subscription plans found in Trash.', 'tamarind-subscriptions' ),
		'featured_image'        => _x( 'Subscription Plan Cover Image', 'Overrides the "Featured Image" phrase for this post type.', 'tamarind-subscriptions' ),
		'set_featured_image'    => _x( 'Set cover image', 'Overrides the "Set featured image" phrase for this post type.', 'tamarind-subscriptions' ),
		'remove_featured_image' => _x( 'Remove cover image', 'Overrides the "Remove featured image" phrase for this post type.', 'tamarind-subscriptions' ),
		'use_featured_image'    => _x( 'Use as cover image', 'Overrides the "Use as featured image" phrase for this post type.', 'tamarind-subscriptions' ),
		'archives'              => _x( 'Subscription Plans archives', 'The post type archive label used in nav menus.', 'tamarind-subscriptions' ),
		'insert_into_item'      => _x( 'Insert into subscription plan', 'Overrides the "Insert into post"/"Insert into page" phrase (used when inserting media into a post).', 'tamarind-subscriptions' ),
		'uploaded_to_this_item' => _x( 'Uploaded to this subscription plan', 'Overrides the "Uploaded to this post"/"Uploaded to this page" phrase (used when viewing media attached to a post).', 'tamarind-subscriptions' ),
		'filter_items_list'     => _x( 'Filter subscription plans list', 'Screen reader text for the filter links heading on the post type listing screen.', 'tamarind-subscriptions' ),
		'items_list_navigation' => _x( 'Subscription Plans list navigation', 'Screen reader text for the pagination heading on the post type listing screen.', 'tamarind-subscriptions' ),
		'items_list'            => _x( 'Subscription Plans list', 'Screen reader text for the items list heading on the post type listing screen.', 'tamarind-subscriptions' ),
	);

	$args = array(
		'labels'              => $labels,
		'public'              => false,
		'publicly_queryable'  => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_rest'        => true,
		'has_archive'         => false,
		'rewrite'             => false,
		'query_var'           => false,
		'supports'            => array( 'title', 'thumbnail', 'custom-fields' ),
		'exclude_from_search' => true,
		'menu_icon'           => 'dashicons-groups',

		'capability_type'     => 'subscription_plan',
		'map_meta_cap'        => true,
		'capabilities'        => array(
			'edit_post'              => 'edit_subscription_plan',
			'read_post'              => 'read_subscription_plan',
			'delete_post'            => 'delete_subscription_plan',

			'edit_posts'             => 'edit_subscription_plans',
			'edit_others_posts'      => 'edit_other_subscription_plans',
			'delete_posts'           => 'delete_subscription_plans',
			'delete_private_posts'   => 'delete_private_subscription_plans',
			'delete_others_posts'    => 'delete_other_subscription_plans',
			'delete_published_posts' => 'delete_published_subscription_plans',
			'publish_posts'          => 'publish_subscription_plans',
			'read_posts'             => 'read_subscription_plans',
			'read_private_posts'     => 'read_private_subscription_plans',
			'create_posts'           => 'create_subscription_plans',
		),
	);

	register_post_type( 'subscription-plan', $args );
}
