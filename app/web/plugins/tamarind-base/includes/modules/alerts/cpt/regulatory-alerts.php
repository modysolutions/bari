<?php
/**
 * Register Regulatory Alerts CPT.
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\alerts;

defined( 'ABSPATH' ) || exit;

add_action( 'init', __NAMESPACE__ . '\custom_post_type_regulatory_alert' );

/**
 * Register Custom Post Type
 */
function custom_post_type_regulatory_alert(): void {
	$labels = array(
		'name'                  => _x( 'ALERTS', 'Post Type General Name', TM_LANGUAGE_DOMAIN ),
		'singular_name'         => _x( 'ALERT', 'Post Type Singular Name', TM_LANGUAGE_DOMAIN ),
		'menu_name'             => __( 'ALERTS', TM_LANGUAGE_DOMAIN ),
		'name_admin_bar'        => __( 'ALERTS', TM_LANGUAGE_DOMAIN ),
		'archives'              => __( 'Alerts Archives', TM_LANGUAGE_DOMAIN ),
		'attributes'            => __( 'Alerts Attributes', TM_LANGUAGE_DOMAIN ),
		'parent_item_colon'     => __( 'Parent Item:', TM_LANGUAGE_DOMAIN ),
		'all_items'             => __( 'All alerts', TM_LANGUAGE_DOMAIN ),
		'add_new_item'          => __( 'Add New Alert', TM_LANGUAGE_DOMAIN ),
		'add_new'               => __( 'Add New', TM_LANGUAGE_DOMAIN ),
		'new_item'              => __( 'New Alert', TM_LANGUAGE_DOMAIN ),
		'edit_item'             => __( 'Edit Alert', TM_LANGUAGE_DOMAIN ),
		'update_item'           => __( 'Update Alert', TM_LANGUAGE_DOMAIN ),
		'view_item'             => __( 'View Alert', TM_LANGUAGE_DOMAIN ),
		'view_items'            => __( 'View Alerts', TM_LANGUAGE_DOMAIN ),
		'search_items'          => __( 'Search Alerts', TM_LANGUAGE_DOMAIN ),
		'not_found'             => __( 'Not found', TM_LANGUAGE_DOMAIN ),
		'not_found_in_trash'    => __( 'Not found in Trash', TM_LANGUAGE_DOMAIN ),
		'featured_image'        => __( 'Featured Image', TM_LANGUAGE_DOMAIN ),
		'set_featured_image'    => __( 'Set featured image', TM_LANGUAGE_DOMAIN ),
		'remove_featured_image' => __( 'Remove featured image', TM_LANGUAGE_DOMAIN ),
		'use_featured_image'    => __( 'Use as featured image', TM_LANGUAGE_DOMAIN ),
		'insert_into_item'      => __( 'Insert into item', TM_LANGUAGE_DOMAIN ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', TM_LANGUAGE_DOMAIN ),
		'items_list'            => __( 'Items list', 'tamarind-base' ),
		'items_list_navigation' => __( 'Items list navigation', 'tamarind-base' ),
		'filter_items_list'     => __( 'Filter items list', 'tamarind-base' ),
	);

	$args = array(
		'label'               => __( 'Alert', 'tamarind-base' ),
		'description'         => __( 'Regulatory Alerts', 'tamarind-base' ),
		'labels'              => $labels,
		'supports'            => array(
			'title',
			'thumbnail',
			'editor',
		),
		'hierarchical'        => false,
		'rewrite'             => array(
			'slug'       => 'regulatory-alerts',
			'with_front' => false,
		),
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 11,
		'show_in_admin_bar'   => true,
		'show_in_nav_menus'   => true,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'show_in_rest'        => true,

		'capability_type'     => 'alert',
		'map_meta_cap'        => true,
		'capabilities'        => array(
			'edit_post'              => 'edit_alert',
			'read_post'              => 'read_alert',
			'delete_post'            => 'delete_alert',

			'edit_posts'             => 'edit_alerts',
			'edit_others_posts'      => 'edit_other_alerts',
			'delete_posts'           => 'delete_alerts',
			'delete_private_posts'   => 'delete_private_alerts',
			'delete_others_posts'    => 'delete_other_alerts',
			'delete_published_posts' => 'delete_published_alerts',
			'publish_posts'          => 'publish_alerts',
			'read_posts'             => 'read_alerts',
			'read_private_posts'     => 'read_private_alerts',
			'create_posts'           => 'create_alerts',
		),
	);

	register_post_type( 'regulatory_alert', $args );
}
