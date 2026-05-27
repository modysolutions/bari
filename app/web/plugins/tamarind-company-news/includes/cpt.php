<?php
/**
 * Custom Post Type for Company News
 *
 * @package Tamarind_Company_News
 */

namespace tamarind_company_news;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', __NAMESPACE__ . '\register_company_news_cpt', 0 );
add_filter( 'post_updated_messages', __NAMESPACE__ . '\post_updated_messages' );
add_filter( 'bulk_post_updated_messages', __NAMESPACE__ . '\bulk_post_updated_messages', 10, 2 );
add_filter( 'template_include', __NAMESPACE__ . '\company_news_archive_template' );
add_filter( 'template_include', __NAMESPACE__ . '\company_news_single_template' );
add_action( 'pre_get_posts', __NAMESPACE__ . '\modify_company_news_archive_query', 20 );

// Deactivate search indexing and Yoast sitemap for the Company News post type.
add_filter( 'wpseo_robots', __NAMESPACE__ . '\yoast_no_index_cpt' );
add_filter( 'wpseo_sitemap_exclude_post_type', __NAMESPACE__ . '\yoast_exclude_cpt_from_sitemap', 10, 2 );

/**
 * Registers the Company News Custom Post Type
 *
 * @return void
 */
function register_company_news_cpt() {
	$labels = array(
		'name'                  => _x( 'Company News', 'Post type general name', 'tm-company-news' ),
		'singular_name'         => _x( 'Company News', 'Post type singular name', 'tm-company-news' ),
		'menu_name'             => _x( 'Company News', 'Admin Menu text', 'tm-company-news' ),
		'name_admin_bar'        => _x( 'Company News', 'Add New on Toolbar', 'tm-company-news' ),
		'add_new'               => __( 'Add New', 'tm-company-news' ),
		'add_new_item'          => __( 'Add New Company News', 'tm-company-news' ),
		'new_item'              => __( 'New Company News', 'tm-company-news' ),
		'edit_item'             => __( 'Edit Company News', 'tm-company-news' ),
		'view_item'             => __( 'View Company News', 'tm-company-news' ),
		'all_items'             => __( 'All Company News', 'tm-company-news' ),
		'search_items'          => __( 'Search Company News', 'tm-company-news' ),
		'parent_item_colon'     => __( 'Parent Company News:', 'tm-company-news' ),
		'not_found'             => __( 'No company news found.', 'tm-company-news' ),
		'not_found_in_trash'    => __( 'No company news found in Trash.', 'tm-company-news' ),
		'featured_image'        => _x( 'Company News Cover Image', 'Overrides the "Featured Image" phrase for this post type.', 'tm-company-news' ),
		'set_featured_image'    => _x( 'Set cover image', 'Overrides the "Set featured image" phrase for this post type.', 'tm-company-news' ),
		'remove_featured_image' => _x( 'Remove cover image', 'Overrides the "Remove featured image" phrase for this post type.', 'tm-company-news' ),
		'use_featured_image'    => _x( 'Use as cover image', 'Overrides the "Use as featured image" phrase for this post type.', 'tm-company-news' ),
		'archives'              => _x( 'Company News archives', 'The post type archive label used in nav menus.', 'tm-company-news' ),
		'insert_into_item'      => _x( 'Insert into company news', 'Overrides the "Insert into post"/"Insert into page" phrase (used when inserting media into a post).', 'tm-company-news' ),
		'uploaded_to_this_item' => _x( 'Uploaded to this company news', 'Overrides the "Uploaded to this post"/"Uploaded to this page" phrase (used when viewing media attached to a post).', 'tm-company-news' ),
		'filter_items_list'     => _x( 'Filter company news list', 'Screen reader text for the filter links heading on the post type listing screen.', 'tm-company-news' ),
		'items_list_navigation' => _x( 'Company News list navigation', 'Screen reader text for the pagination heading on the post type listing screen.', 'tm-company-news' ),
		'items_list'            => _x( 'Company News list', 'Screen reader text for the items list heading on the post type listing screen.', 'tm-company-news' ),
	);

	$args = array(
		'labels'              => $labels,
		'public'              => true,
		'has_archive'         => true,
		'rewrite'             => array(
			'slug'       => 'company-news',
			'with_front' => false,
			'pages'      => true,
			'feeds'      => true,
		),
		'query_var'           => true,
		'show_in_rest'        => true,
		'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		'publicly_queryable'  => true,
		'exclude_from_search' => false,
		'menu_icon'           => 'dashicons-megaphone',

		'capability_type'     => 'company_news_item',
		'map_meta_cap'        => true,
		'capabilities'        => array(
			'edit_post'              => 'edit_company_news_item',
			'read_post'              => 'read_company_news_item',
			'delete_post'            => 'delete_company_news_item',

			'edit_posts'             => 'edit_company_news_items',
			'edit_others_posts'      => 'edit_other_company_news_items',
			'delete_posts'           => 'delete_company_news_items',
			'delete_private_posts'   => 'delete_private_company_news_items',
			'delete_others_posts'    => 'delete_other_company_news_items',
			'delete_published_posts' => 'delete_published_company_news_items',
			'publish_posts'          => 'publish_company_news_items',
			'read_posts'             => 'read_company_news_items',
			'read_private_posts'     => 'read_private_company_news_items',
			'create_posts'           => 'create_company_news_items',
		),
	);

	register_post_type( 'company-news', $args );
}


/**
 * Sets the post updated messages for the `company-news` post type.
 *
 * @param  array $messages Post updated messages.
 * @return array Messages for the `company-news` post type.
 */
function post_updated_messages( $messages ) {
	global $post;

	$permalink = get_permalink( $post );

	$messages['company-news'] = array(
		0  => '', // Unused. Messages start at index 1.
		/* translators: %s: post permalink */
		1  => sprintf( __( 'Company News updated. <a target="_blank" href="%s">View company news</a>', 'tm-company-news' ), esc_url( $permalink ) ),
		2  => __( 'Custom field updated.', 'tm-company-news' ),
		3  => __( 'Custom field deleted.', 'tm-company-news' ),
		4  => __( 'Company News updated.', 'tm-company-news' ),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Company News restored to revision from %s', 'tm-company-news' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false, // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		/* translators: %s: post permalink */
		6  => sprintf( __( 'Company News published. <a href="%s">View company news</a>', 'tm-company-news' ), esc_url( $permalink ) ),
		7  => __( 'Company News saved.', 'tm-company-news' ),
		/* translators: %s: post permalink */
		8  => sprintf( __( 'Company News submitted. <a target="_blank" href="%s">Preview company news</a>', 'tm-company-news' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
		/* translators: 1: Publish box date format, see https://secure.php.net/date 2: Post permalink */
		9  => sprintf( __( 'Company News scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview company news</a>', 'tm-company-news' ), date_i18n( __( 'M j, Y @ G:i', 'tm-company-news' ), strtotime( $post->post_date ) ), esc_url( $permalink ) ),
		/* translators: %s: post permalink */
		10 => sprintf( __( 'Company News draft updated. <a target="_blank" href="%s">Preview company news</a>', 'tm-company-news' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
	);

	return $messages;
}


/**
 * Sets the bulk post updated messages for the `company-news` post type.
 *
 * @param  array $bulk_messages Arrays of messages, each keyed by the corresponding post type. Messages are keyed with 'updated', 'locked', 'deleted', 'trashed', and 'untrashed'.
 * @param  int[] $bulk_counts   Array of item counts for each message, used to build internationalized strings.
 * @return array Bulk messages for the `company-news` post type.
 */
function bulk_post_updated_messages( $bulk_messages, $bulk_counts ) {
	global $post;

	$bulk_messages['company-news'] = array(
		/* translators: %s: Number of company news. */
		'updated'   => _n( '%s company news updated.', '%s company news updated.', $bulk_counts['updated'], 'tm-company-news' ),
		/* translators: %s: Number of company news. */
		'locked'    => ( 1 === $bulk_counts['locked'] ) ? __( '1 company news not updated, somebody is editing it.', 'tm-company-news' ) : _n( '%s company news not updated, somebody is editing it.', '%s company news not updated, somebody is editing them.', $bulk_counts['locked'], 'tm-company-news' ),
		/* translators: %s: Number of company news. */
		'deleted'   => _n( '%s company news permanently deleted.', '%s company news permanently deleted.', $bulk_counts['deleted'], 'tm-company-news' ),
		/* translators: %s: Number of company news. */
		'trashed'   => _n( '%s company news moved to the Trash.', '%s company news moved to the Trash.', $bulk_counts['trashed'], 'tm-company-news' ),
		/* translators: %s: Number of company news. */
		'untrashed' => _n( '%s company news restored from the Trash.', '%s company news restored from the Trash.', $bulk_counts['untrashed'], 'tm-company-news' ),
	);

	return $bulk_messages;
}


/**
 * Loads the Company News Archive template
 *
 * @param string $template Current template.
 * @return string Company News archive template.
 */
function company_news_archive_template( $template ) {
	if ( is_post_type_archive( 'company-news' ) ) {
		$plugin_path   = plugin_dir_path( __FILE__ );
		$template_name = '../templates/archive-company-news.php';

		if ( file_exists( $plugin_path . $template_name ) ) {
			return $plugin_path . $template_name; // Use the plugin template.
		}
	}

	return $template;
}


/**
 * Loads the Company News Single template
 *
 * @param string $template Current template.
 * @return string Company News single template.
 */
function company_news_single_template( $template ) {
	if ( is_singular( 'company-news' ) ) {
		$plugin_path   = plugin_dir_path( __FILE__ );
		$template_path = $plugin_path . '../templates/single-company-news.php';

		if ( file_exists( $template_path ) ) {
			return $template_path; // Use the plugin template.
		}
	}
	return $template;
}


/**
 * Modifies the query for the Company News archive.
 *
 * @param WP_Query $query The query object.
 * @return void
 */
function modify_company_news_archive_query( $query ) {
	if ( ! is_admin() && $query->is_main_query() && is_post_type_archive( 'company-news' ) ) {
		// Overrides the Groups filters for this CPT.
		$query->set( 'suppress_filters', true );
		$query->set( 'ignore_sticky_posts', true );
		$query->set( 'posts_per_page', 5 );
	}
}

/**
 * Adds a noindex meta tag to the Company News post type.
 *
 * @param string $robots The current robots meta tag.
 * @return string The modified robots meta tag.
 */
function yoast_no_index_cpt( $robots ) {
	if ( is_singular( 'company-news' ) || is_post_type_archive( 'company-news' ) ) {
		$robots = 'noindex, follow';
	}
	return $robots;
}


/**
 * Excludes the Company News post type from the Yoast sitemap.
 *
 * @param bool   $excluded Whether to exclude the post type from the sitemap.
 * @param string $post_type The post type being checked.
 * @return bool True if the post type should be excluded, false otherwise.
 */
function yoast_exclude_cpt_from_sitemap( $excluded, $post_type ) {
	if ( 'company-news' === $post_type ) {
		return true;
	}
	return $excluded;
}
