<?php
/**
 * Functions for Geography Taxonomy
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\taxonomies;

defined( 'ABSPATH' ) || exit;


add_action( 'init', __NAMESPACE__ . '\register_taxonomy_geography' );

/**
 * Register the Geography taxonomy for posts and regulatory alerts.
 *
 * @return void
 */
function register_taxonomy_geography(): void {
	$labels = array(
		'name'                       => _x( 'Geography', TM_LANGUAGE_DOMAIN ),
		'singular_name'              => _x( 'Geography', TM_LANGUAGE_DOMAIN ),
		'search_items'               => _x( 'Search Geography', TM_LANGUAGE_DOMAIN ),
		'popular_items'              => _x( 'Popular Geography', TM_LANGUAGE_DOMAIN ),
		'all_items'                  => _x( 'All Geography', TM_LANGUAGE_DOMAIN ),
		'parent_item'                => _x( 'Parent Geography', TM_LANGUAGE_DOMAIN ),
		'parent_item_colon'          => _x( 'Parent Geography:', TM_LANGUAGE_DOMAIN ),
		'edit_item'                  => _x( 'Edit Geography', TM_LANGUAGE_DOMAIN ),
		'update_item'                => _x( 'Update Geography', TM_LANGUAGE_DOMAIN ),
		'add_new_item'               => _x( 'Add New Geography', TM_LANGUAGE_DOMAIN ),
		'new_item_name'              => _x( 'New Geography', TM_LANGUAGE_DOMAIN ),
		'separate_items_with_commas' => _x( 'Separate geography with commas', TM_LANGUAGE_DOMAIN ),
		'add_or_remove_items'        => _x( 'Add or remove geography', TM_LANGUAGE_DOMAIN ),
		'choose_from_most_used'      => _x( 'Choose from the most used geography', TM_LANGUAGE_DOMAIN ),
		'menu_name'                  => _x( 'Geography', TM_LANGUAGE_DOMAIN ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'show_in_nav_menus'  => true,
		'show_ui'            => true,
		'show_tagcloud'      => true,
		'show_admin_column'  => false,
		'hierarchical'       => true,

		'rewrite'            => true,
		'query_var'          => true,

		'publicly_queryable' => true,
		'show_in_menu'       => true,
		'show_in_quick_edit' => true,

		'capabilities'       => array(
			'edit_terms'   => 'manage_options',
			'delete_terms' => 'manage_options',
		),

		'show_in_rest'       => true,
	);

	register_taxonomy( 'geography', array( 'post', 'regulatory_alert' ), $args );
}
