<?php
/**
 * Custom Posts Types for Tamarind Subscription.
 *
 * @package tamarind_subscriptions
 */

namespace tamarind_subscriptions\subscription_plan;

defined( 'ABSPATH' ) || exit;

add_action( 'init', __NAMESPACE__ . '\register_client_cpt' );

/**
 * Register the Client Custom Post Type.
 *
 * @return void
 */
function register_client_cpt() : void {
	$labels = array(
		'name'                  => __( 'Clients', 'tamarind-subscriptions' ),
		'singular_name'         => __( 'Client', 'tamarind-subscriptions' ),
		'menu_name'             => __( 'Clients', 'tamarind-subscriptions' ),
		'name_admin_bar'        => __( 'Client', 'tamarind-subscriptions' ),
		'add_new'               => __( 'Add New', 'tamarind-subscriptions' ),
		'add_new_item'          => __( 'Add New Client', 'tamarind-subscriptions' ),
		'new_item'              => __( 'New Client', 'tamarind-subscriptions' ),
		'edit_item'             => __( 'Edit Client', 'tamarind-subscriptions' ),
		'view_item'             => __( 'View Client', 'tamarind-subscriptions' ),
		'all_items'             => __( 'All Clients', 'tamarind-subscriptions' ),
		'search_items'          => __( 'Search Clients', 'tamarind-subscriptions' ),
		'parent_item_colon'     => __( 'Parent Client:', 'tamarind-subscriptions' ),
		'not_found'             => __( 'No clients found.', 'tamarind-subscriptions' ),
		'not_found_in_trash'    => __( 'No clients found in Trash.', 'tamarind-subscriptions' ),
		'featured_image'        => _x( 'Client Cover Image', 'Overrides the "Featured Image" phrase for this post type.', 'tamarind-subscriptions' ),
		'set_featured_image'    => _x( 'Set cover image', 'Overrides the "Set featured image" phrase for this post type.', 'tamarind-subscriptions' ),
		'remove_featured_image' => _x( 'Remove cover image', 'Overrides the "Remove featured image" phrase for this post type.', 'tamarind-subscriptions' ),
		'use_featured_image'    => _x( 'Use as cover image', 'Overrides the "Use as featured image" phrase for this post type.', 'tamarind-subscriptions' ),
		'archives'              => _x( 'Clients archives', 'The post type archive label used in nav menus.', 'tamarind-subscriptions' ),
		'insert_into_item'      => _x( 'Insert into client', 'Overrides the "Insert into post"/"Insert into page" phrase (used when inserting media into a post).', 'tamarind-subscriptions' ),
		'uploaded_to_this_item' => _x( 'Uploaded to this client', 'Overrides the "Uploaded to this post"/"Uploaded to this page" phrase (used when viewing media attached to a post).', 'tamarind-subscriptions' ),
		'filter_items_list'     => _x( 'Filter clients list', 'Screen reader text for the filter links heading on the post type listing screen.', 'tamarind-subscriptions' ),
		'items_list_navigation' => _x( 'Clients list navigation', 'Screen reader text for the pagination heading on the post type listing screen.', 'tamarind-subscriptions' ),
		'items_list'            => _x( 'Clients list', 'Screen reader text for the items list heading on the post type listing screen.', 'tamarind-subscriptions' ),
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
		'menu_icon'           => 'dashicons-id-alt',

		'capability_type'     => 'client',
		'map_meta_cap'        => true,
		'capabilities'        => array(
			'edit_post'              => 'edit_client',
			'read_post'              => 'read_client',
			'delete_post'            => 'delete_client',

			'edit_posts'             => 'edit_clients',
			'edit_others_posts'      => 'edit_other_clients',
			'delete_posts'           => 'delete_clients',
			'delete_private_posts'   => 'delete_private_clients',
			'delete_others_posts'    => 'delete_other_clients',
			'delete_published_posts' => 'delete_published_clients',
			'publish_posts'          => 'publish_clients',
			'read_posts'             => 'read_clients',
			'read_private_posts'     => 'read_private_clients',
			'create_posts'           => 'create_clients',
		),
	);

	register_post_type( 'client', $args );
}
