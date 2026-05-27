<?php
/**
 * Functions for Topics Taxonomy
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\taxonomies;

defined( 'ABSPATH' ) || exit;


add_action( 'init', __NAMESPACE__ . '\register_taxonomy_topics' );

/**
 * Register the Topics taxonomy for posts and regulatory alerts.
 *
 * @return void
 */
function register_taxonomy_topics(): void {
	$labels = array(
		'name'                       => _x( 'Topics', TM_LANGUAGE_DOMAIN ),
		'singular_name'              => _x( 'Topic', TM_LANGUAGE_DOMAIN ),
		'search_items'               => _x( 'Search Topics', TM_LANGUAGE_DOMAIN ),
		'popular_items'              => _x( 'Popular Topics', TM_LANGUAGE_DOMAIN ),
		'all_items'                  => _x( 'All Topics', TM_LANGUAGE_DOMAIN ),
		'parent_item'                => _x( 'Parent Topic', TM_LANGUAGE_DOMAIN ),
		'parent_item_colon'          => _x( 'Parent Topic:', TM_LANGUAGE_DOMAIN ),
		'edit_item'                  => _x( 'Edit Topic', TM_LANGUAGE_DOMAIN ),
		'update_item'                => _x( 'Update Topic', TM_LANGUAGE_DOMAIN ),
		'add_new_item'               => _x( 'Add New Topic', TM_LANGUAGE_DOMAIN ),
		'new_item_name'              => _x( 'New Topic', TM_LANGUAGE_DOMAIN ),
		'separate_items_with_commas' => _x( 'Separate topics with commas', TM_LANGUAGE_DOMAIN ),
		'add_or_remove_items'        => _x( 'Add or remove topics', TM_LANGUAGE_DOMAIN ),
		'choose_from_most_used'      => _x( 'Choose from the most used topics', TM_LANGUAGE_DOMAIN ),
		'menu_name'                  => _x( 'Topics', TM_LANGUAGE_DOMAIN ),
	);

	$args = array(
		'labels'            => $labels,
		'public'            => true,
		'show_in_nav_menus' => true,
		'show_ui'           => true,
		'show_tagcloud'     => true,
		'show_admin_column' => false,
		'hierarchical'      => true,
		'rewrite'           => true,
		'query_var'         => true,
		'has_archive'       => true,
	);

	register_taxonomy( 'topics', array( 'post', 'regulatory_alert' ), $args );
}
