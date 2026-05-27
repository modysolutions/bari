<?php
/**
 * Register Case Studies CPT.
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\case_studies;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', __NAMESPACE__ . '\custom_post_type_case_studies' );

/**
 * Register Custom Post Type
 */
function custom_post_type_case_studies() {
	$labels = array(
		'name'                  => _x( 'Customised Analysis Case Studies', 'Post Type General Name', TM_LANGUAGE_DOMAIN ),
		'singular_name'         => _x( 'Case', 'Post Type Singular Name', TM_LANGUAGE_DOMAIN ),
		'menu_name'             => __( 'Case Studies', TM_LANGUAGE_DOMAIN ),
		'name_admin_bar'        => __( 'Customised Analysis Case Studies', TM_LANGUAGE_DOMAIN ),
		'archives'              => __( 'Item Archives', TM_LANGUAGE_DOMAIN ),
		'attributes'            => __( 'Item Attributes', TM_LANGUAGE_DOMAIN ),
		'parent_item_colon'     => __( 'Parent Item:', TM_LANGUAGE_DOMAIN ),
		'all_items'             => __( 'All case studies', TM_LANGUAGE_DOMAIN ),
		'add_new_item'          => __( 'Add New case', TM_LANGUAGE_DOMAIN ),
		'add_new'               => __( 'Add New', TM_LANGUAGE_DOMAIN ),
		'new_item'              => __( 'New Item', TM_LANGUAGE_DOMAIN ),
		'edit_item'             => __( 'Edit Item', TM_LANGUAGE_DOMAIN ),
		'update_item'           => __( 'Update Item', 'tamarind-base' ),
		'view_item'             => __( 'View Item', 'tamarind-base' ),
		'view_items'            => __( 'View Items', 'tamarind-base' ),
		'search_items'          => __( 'Search Item', 'tamarind-base' ),
		'not_found'             => __( 'Not found', 'tamarind-base' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'tamarind-base' ),
		'featured_image'        => __( 'Featured Image', 'tamarind-base' ),
		'set_featured_image'    => __( 'Set featured image', 'tamarind-base' ),
		'remove_featured_image' => __( 'Remove featured image', 'tamarind-base' ),
		'use_featured_image'    => __( 'Use as featured image', 'tamarind-base' ),
		'insert_into_item'      => __( 'Insert into item', 'tamarind-base' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'tamarind-base' ),
		'items_list'            => __( 'Items list', 'tamarind-base' ),
		'items_list_navigation' => __( 'Items list navigation', 'tamarind-base' ),
		'filter_items_list'     => __( 'Filter items list', 'tamarind-base' ),
	);

	$args = array(
		'label'               => __( 'Case Studies', 'tamarind-base' ),
		'description'         => __( 'Customised Analysis Case Studies', 'tamarind-base' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'thumbnail' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 5,
		'show_in_admin_bar'   => true,
		'show_in_nav_menus'   => true,
		'can_export'          => true,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => true,
		'rewrite'             => array( 'slug' => 'case-studies' ),

		'capability_type'     => 'case_study',
		'map_meta_cap'        => true,
		'capabilities'        => array(
			'edit_post'              => 'edit_case_study',
			'read_post'              => 'read_case_study',
			'delete_post'            => 'delete_case_study',

			'edit_posts'             => 'edit_case_studies',
			'edit_others_posts'      => 'edit_other_case_studies',
			'delete_posts'           => 'delete_case_studies',
			'delete_private_posts'   => 'delete_private_case_studies',
			'delete_others_posts'    => 'delete_other_case_studies',
			'delete_published_posts' => 'delete_published_case_studies',
			'publish_posts'          => 'publish_case_studies',
			'read_posts'             => 'read_case_studies',
			'read_private_posts'     => 'read_private_case_studies',
			'create_posts'           => 'create_case_studies',
		),
	);

	register_post_type( 'case_studies', $args );
}
