<?php
/**
 * Custom Post Type for Notifications
 *
 * @package Tamarind_Notifications
 */

namespace tamarind_notifications;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', __NAMESPACE__ . '\init' );
add_filter( 'post_updated_messages', __NAMESPACE__ . '\post_updated_messages' );
add_filter( 'bulk_post_updated_messages', __NAMESPACE__ . '\bulk_post_updated_messages', 10, 2 );
add_filter( 'template_include', __NAMESPACE__ . '\notifications_archive_template' );
add_filter( 'template_include', __NAMESPACE__ . '\notifications_single_template' );
add_action( 'pre_get_posts', __NAMESPACE__ . '\modify_notifications_archive_query' );

// Deactivate search indexing and Yoast sitemap for the Company News post type.
add_filter( 'wpseo_robots', __NAMESPACE__ . '\yoast_no_index_cpt' );
add_filter( 'wpseo_sitemap_exclude_post_type', __NAMESPACE__ . '\yoast_exclude_cpt_from_sitemap', 10, 2 );

/**
 * Registers the Notifications Custom Post Type.
 *
 * @return void
 */
function init() {
	$labels = array(
		'name'                  => _x( 'Notifications', 'Post type general name', 'tm-notifications' ),
		'singular_name'         => _x( 'Notification', 'Post type singular name', 'tm-notifications' ),
		'menu_name'             => _x( 'Notifications', 'Admin Menu text', 'tm-notifications' ),
		'name_admin_bar'        => _x( 'Notification', 'Add New on Toolbar', 'tm-notifications' ),
		'add_new'               => __( 'Add New', 'tm-notifications' ),
		'add_new_item'          => __( 'Add New Notification', 'tm-notifications' ),
		'new_item'              => __( 'New Notification', 'tm-notifications' ),
		'edit_item'             => __( 'Edit Notification', 'tm-notifications' ),
		'view_item'             => __( 'View Notification', 'tm-notifications' ),
		'all_items'             => __( 'All Notifications', 'tm-notifications' ),
		'search_items'          => __( 'Search Notifications', 'tm-notifications' ),
		'parent_item_colon'     => __( 'Parent Notifications:', 'tm-notifications' ),
		'not_found'             => __( 'No notifications found.', 'tm-notifications' ),
		'not_found_in_trash'    => __( 'No notifications found in Trash.', 'tm-notifications' ),
		'featured_image'        => _x( 'Notification Cover Image', 'Overrides the "Featured Image" phrase for this post type.', 'tm-notifications' ),
		'set_featured_image'    => _x( 'Set cover image', 'Overrides the "Set featured image" phrase for this post type.', 'tm-notifications' ),
		'remove_featured_image' => _x( 'Remove cover image', 'Overrides the "Remove featured image" phrase for this post type.', 'tm-notifications' ),
		'use_featured_image'    => _x( 'Use as cover image', 'Overrides the "Use as featured image" phrase for this post type.', 'tm-notifications' ),
		'archives'              => _x( 'Notification archives', 'The post type archive label used in nav menus.', 'tm-notifications' ),
		'insert_into_item'      => _x( 'Insert into notification', 'Overrides the "Insert into post"/"Insert into page" phrase (used when inserting media into a post).', 'tm-notifications' ),
		'uploaded_to_this_item' => _x( 'Uploaded to this notification', 'Overrides the "Uploaded to this post"/"Uploaded to this page" phrase (used when viewing media attached to a post).', 'tm-notifications' ),
		'filter_items_list'     => _x( 'Filter notifications list', 'Screen reader text for the filter links heading on the post type listing screen.', 'tm-notifications' ),
		'items_list_navigation' => _x( 'Notifications list navigation', 'Screen reader text for the pagination heading on the post type listing screen.', 'tm-notifications' ),
		'items_list'            => _x( 'Notifications list', 'Screen reader text for the items list heading on the post type listing screen.', 'tm-notifications' ),
	);

	$args = array(
		'labels'             => $labels,
		'public'             => true,
		'publicly_queryable' => true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'notifications' ),
		'has_archive'        => true,
		'hierarchical'       => false,
		'menu_position'      => null,
		'supports'           => array( 'title', 'editor', 'excerpt' ),
		'menu_icon'          => 'dashicons-bell',

		'capability_type'    => 'notification',
		'map_meta_cap'       => true,
		'capabilities'       => array(
			'edit_post'              => 'edit_notification',
			'read_post'              => 'read_notification',
			'delete_post'            => 'delete_notification',

			'edit_posts'             => 'edit_notifications',
			'edit_others_posts'      => 'edit_other_notifications',
			'delete_posts'           => 'delete_notifications',
			'delete_private_posts'   => 'delete_private_notifications',
			'delete_others_posts'    => 'delete_other_notifications',
			'delete_published_posts' => 'delete_published_notifications',
			'publish_posts'          => 'publish_notifications',
			'read_posts'             => 'read_notifications',
			'read_private_posts'     => 'read_private_notifications',
			'create_posts'           => 'create_notifications',
		),
	);

	register_post_type( 'notifications', $args );
}


/**
 * Sets the post updated messages for the `notifications` post type.
 *
 * @param  array $messages Post updated messages.
 * @return array Messages for the `notifications` post type.
 */
function post_updated_messages( $messages ) {
	global $post;

	$permalink = get_permalink( $post );

	$messages['notifications'] = array(
		0  => '', // Unused. Messages start at index 1.
		/* translators: %s: post permalink */
		1  => sprintf( __( 'Notifications update. <a target="_blank" href="%s">View notification</a>', 'tm-notifications' ), esc_url( $permalink ) ),
		2  => __( 'Custom field updated.', 'tm-notifications' ),
		3  => __( 'Custom field deleted.', 'tm-notifications' ),
		4  => __( 'Notification updated.', 'tm-notifications' ),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Notifications restored to revision from %s', 'tm-notifications' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false, // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		/* translators: %s: post permalink */
		6  => sprintf( __( 'Notification published. <a href="%s">View notification</a>', 'tm-notifications' ), esc_url( $permalink ) ),
		7  => __( 'Notifications saved.', 'tm-notifications' ),
		/* translators: %s: post permalink */
		8  => sprintf( __( 'Notification submitted. <a target="_blank" href="%s">Preview notification</a>', 'tm-notifications' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
		/* translators: 1: Publish box date format, see https://secure.php.net/date 2: Post permalink */
		9  => sprintf( __( 'Notification scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview notification</a>', 'tm-notifications' ), date_i18n( __( 'M j, Y @ G:i', 'tm-notifications' ), strtotime( $post->post_date ) ), esc_url( $permalink ) ),
		/* translators: %s: post permalink */
		10 => sprintf( __( 'Notification draft updated. <a target="_blank" href="%s">Preview notification</a>', 'tm-notifications' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
	);

	return $messages;
}

/**
 * Sets the bulk post updated messages for the `notifications` post type.
 *
 * @param  array $bulk_messages Arrays of messages, each keyed by the corresponding post type. Messages are
 *                              keyed with 'updated', 'locked', 'deleted', 'trashed', and 'untrashed'.
 * @param  int[] $bulk_counts   Array of item counts for each message, used to build internationalized strings.
 * @return array Bulk messages for the `notifications` post type.
 */
function bulk_post_updated_messages( $bulk_messages, $bulk_counts ) {
	global $post;

	$bulk_messages['notifications'] = array(
		/* translators: %s: Number of notifications. */
		'updated'   => _n( '%s notification updated.', '%s notifications updated.', $bulk_counts['updated'], 'tm-notifications' ),
		/* translators: %s: Number of notifications. */
		'locked'    => ( 1 === $bulk_counts['locked'] ) ? __( '1 notification not updated, somebody is editing it.', 'tm-notifications' ) : _n( '%s notification not updated, somebody is editing it.', '%s notifications not updated, somebody is editing them.', $bulk_counts['locked'], 'tm-notifications' ),
		/* translators: %s: Number of notifications. */
		'deleted'   => _n( '%s notification permanently deleted.', '%s notifications permanently deleted.', $bulk_counts['deleted'], 'tm-notifications' ),
		/* translators: %s: Number of notifications. */
		'trashed'   => _n( '%s notification moved to the Trash.', '%s notifications moved to the Trash.', $bulk_counts['trashed'], 'tm-notifications' ),
		/* translators: %s: Number of notifications. */
		'untrashed' => _n( '%s notification restored from the Trash.', '%s notifications restored from the Trash.', $bulk_counts['untrashed'], 'tm-notifications' ),
	);

	return $bulk_messages;
}


/**
 * Loads the Notifications Archive template
 *
 * @param string $template Current template.
 * @return string Notifications archive template.
 */
function notifications_archive_template( $template ) {
	if ( is_post_type_archive( 'notifications' ) ) {
		$plugin_path = plugin_dir_path( __FILE__ );
		$template_name = '../templates/archive-notifications.php';

		if ( file_exists( $plugin_path . $template_name ) ) {
			return $plugin_path . $template_name; // Use the plugin template.
		}
	}
	return $template;
}

/**
 * Loads the Notifications Single template
 *
 * @param string $template Current template.
 * @return string Notifications single template.
 */
function notifications_single_template( $template ) {
	if ( is_singular( 'notifications' ) ) {
		$plugin_path = plugin_dir_path( __FILE__ );
		$template_path = $plugin_path . '../templates/single-notifications.php';

		if ( file_exists( $template_path ) ) {
			return $template_path; // Use the plugin template.
		}
	}
	return $template;
}

/**
 * Modifies the Notifications Archive query to set the number of posts per page.
 *
 * @param WP_Query $query The current query object.
 */
function modify_notifications_archive_query( $query ) {
	if ( ! is_admin() && $query->is_main_query() && is_post_type_archive( 'notifications' ) ) {
		$query->set( 'posts_per_page', 5 ); // Número de publicaciones por página.
		$query->set( 'paged', ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1 ); // Paginación.
	}
}

/**
 * Adds a noindex meta tag to the Company News post type.
 *
 * @param string $robots The current robots meta tag.
 * @return string The modified robots meta tag.
 */
function yoast_no_index_cpt( $robots ) {
	if ( is_singular( 'notifications' ) || is_post_type_archive( 'notifications' ) ) {
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
	if ( 'notifications' === $post_type ) {
		return true;
	}
	return $excluded;
}
