<?php
/**
 * Custom Post Type for Event
 *
 * @package Tamarind_Events
 */

namespace tamarind_events;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', __NAMESPACE__ . '\register_events_cpt', 0 );
add_filter( 'post_updated_messages', __NAMESPACE__ . '\post_updated_messages' );
add_filter( 'bulk_post_updated_messages', __NAMESPACE__ . '\bulk_post_updated_messages', 10, 2 );

// Adds custom columns to the events list in the admin.
add_filter( 'manage_events_posts_columns', __NAMESPACE__ . '\add_events_columns' );
add_action( 'manage_events_posts_custom_column', __NAMESPACE__ . '\render_events_columns', 10, 2 );

// Allows sorting by ACF date fields.
add_filter( 'manage_edit-events_sortable_columns', __NAMESPACE__ . '\tamarind_events_sortable_columns' );
add_action( 'pre_get_posts', __NAMESPACE__ . '\tamarind_events_custom_orderby' );

// Adds a filter by 'Featured' field in the events list.
add_action( 'restrict_manage_posts', __NAMESPACE__ . '\tamarind_events_featured_filter' );
add_action( 'pre_get_posts', __NAMESPACE__ . '\tamarind_events_filter_featured_column' );


/**
 * Registers the Events Custom Post Type
 *
 * @return void
 */
function register_events_cpt() {
	$labels = array(
		'name'                  => _x( 'Events', 'Post type general name', 'tm-events' ),
		'singular_name'         => _x( 'Event', 'Post type singular name', 'tm-events' ),
		'menu_name'             => _x( 'Events', 'Admin Menu text', 'tm-events' ),
		'name_admin_bar'        => _x( 'Events', 'Add New on Toolbar', 'tm-events' ),
		'add_new'               => __( 'Add New', 'tm-events' ),
		'add_new_item'          => __( 'Add New Event', 'tm-events' ),
		'new_item'              => __( 'New Event', 'tm-events' ),
		'edit_item'             => __( 'Edit Event', 'tm-events' ),
		'view_item'             => __( 'View Event', 'tm-events' ),
		'all_items'             => __( 'All Events', 'tm-events' ),
		'search_items'          => __( 'Search Events', 'tm-events' ),
		'parent_item_colon'     => __( 'Parent Event:', 'tm-events' ),
		'not_found'             => __( 'No events found.', 'tm-events' ),
		'not_found_in_trash'    => __( 'No events found in Trash.', 'tm-events' ),
		'featured_image'        => _x( 'Event Cover Image', 'Overrides the "Featured Image" phrase for this post type.', 'tm-events' ),
		'set_featured_image'    => _x( 'Set cover image', 'Overrides the "Set featured image" phrase for this post type.', 'tm-events' ),
		'remove_featured_image' => _x( 'Remove cover image', 'Overrides the "Remove featured image" phrase for this post type.', 'tm-events' ),
		'use_featured_image'    => _x( 'Use as cover image', 'Overrides the "Use as featured image" phrase for this post type.', 'tm-events' ),
		'archives'              => _x( 'Events archives', 'The post type archive label used in nav menus.', 'tm-events' ),
		'insert_into_item'      => _x( 'Insert into event', 'Overrides the "Insert into post"/"Insert into page" phrase (used when inserting media into a post).', 'tm-events' ),
		'uploaded_to_this_item' => _x( 'Uploaded to this events', 'Overrides the "Uploaded to this post"/"Uploaded to this page" phrase (used when viewing media attached to a post).', 'tm-events' ),
		'filter_items_list'     => _x( 'Filter events list', 'Screen reader text for the filter links heading on the post type listing screen.', 'tm-events' ),
		'items_list_navigation' => _x( 'Events list navigation', 'Screen reader text for the pagination heading on the post type listing screen.', 'tm-events' ),
		'items_list'            => _x( 'Events list', 'Screen reader text for the items list heading on the post type listing screen.', 'tm-events' ),
	);

	$args = array(
		'labels'              => $labels,
		'public'              => false, // Hides it from automatic public queries.
		'publicly_queryable'  => false, // Prevents access via URL (neither single nor archive).
		'show_ui'             => true,  // Still visible in the admin.
		'show_in_menu'        => true,
		'show_in_rest'        => true,
		'has_archive'         => false, // No archive.
		'rewrite'             => false, // No custom URL.
		'query_var'           => false, // Not accessible via query_var like ?events=...
		'supports'            => array( 'title', 'editor', 'excerpt' ),
		'exclude_from_search' => true, // Exclude global searches.
		'menu_icon'           => 'dashicons-calendar-alt',

		'capability_type'     => 'event',
		'map_meta_cap'        => true,
		'capabilities'        => array(
			'edit_post'              => 'edit_event',
			'read_post'              => 'read_event',
			'delete_post'            => 'delete_event',

			'edit_posts'             => 'edit_events',
			'edit_others_posts'      => 'edit_other_events',
			'delete_posts'           => 'delete_events',
			'delete_private_posts'   => 'delete_private_events',
			'delete_others_posts'    => 'delete_other_events',
			'delete_published_posts' => 'delete_published_events',
			'publish_posts'          => 'publish_events',
			'read_posts'             => 'read_events',
			'read_private_posts'     => 'read_private_events',
			'create_posts'           => 'create_events',
		),
	);

	register_post_type( 'events', $args );
}


/**
 * Sets the post updated messages for the `events` post type.
 *
 * @param  array $messages Post updated messages.
 * @return array Messages for the `events` post type.
 */
function post_updated_messages( $messages ) {
	global $post;

	$permalink = get_permalink( $post );

	$messages['events'] = array(
		0  => '', // Unused. Messages start at index 1.
		/* translators: %s: post permalink */
		1  => sprintf( __( 'Event updated. <a target="_blank" href="%s">View event</a>', 'tm-events' ), esc_url( $permalink ) ),
		2  => __( 'Custom field updated.', 'tm-events' ),
		3  => __( 'Custom field deleted.', 'tm-events' ),
		4  => __( 'Event updated.', 'tm-events' ),
		/* translators: %s: date and time of the revision */
		5  => isset( $_GET['revision'] ) ? sprintf( __( 'Event restored to revision from %s', 'tm-events' ), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false, // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		/* translators: %s: post permalink */
		6  => sprintf( __( 'Event published. <a href="%s">View event</a>', 'tm-events' ), esc_url( $permalink ) ),
		7  => __( 'Event saved.', 'tm-events' ),
		/* translators: %s: post permalink */
		8  => sprintf( __( 'Event submitted. <a target="_blank" href="%s">Preview event</a>', 'tm-events' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
		/* translators: 1: Publish box date format, see https://secure.php.net/date 2: Post permalink */
		9  => sprintf( __( 'Event scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview event</a>', 'tm-events' ), date_i18n( __( 'M j, Y @ G:i', 'tm-events' ), strtotime( $post->post_date ) ), esc_url( $permalink ) ),
		/* translators: %s: post permalink */
		10 => sprintf( __( 'Event draft updated. <a target="_blank" href="%s">Preview event</a>', 'tm-events' ), esc_url( add_query_arg( 'preview', 'true', $permalink ) ) ),
	);

	return $messages;
}


/**
 * Sets the bulk post updated messages for the `events` post type.
 *
 * @param  array $bulk_messages Arrays of messages, each keyed by the corresponding post type. Messages are keyed with 'updated', 'locked', 'deleted', 'trashed', and 'untrashed'.
 * @param  int[] $bulk_counts   Array of item counts for each message, used to build internationalized strings.
 * @return array Bulk messages for the `events` post type.
 */
function bulk_post_updated_messages( $bulk_messages, $bulk_counts ) {
	global $post;

	$bulk_messages['events'] = array(
		/* translators: %s: Number of events. */
		'updated'   => _n( '%s events updated.', '%s events updated.', $bulk_counts['updated'], 'tm-events' ),
		/* translators: %s: Number of events. */
		'locked'    => ( 1 === $bulk_counts['locked'] ) ? __( '1 event not updated, somebody is editing it.', 'tm-events' ) : _n( '%s events not updated, somebody is editing it.', '%s events not updated, somebody is editing them.', $bulk_counts['locked'], 'tm-events' ),
		/* translators: %s: Number of events. */
		'deleted'   => _n( '%s events permanently deleted.', '%s events permanently deleted.', $bulk_counts['deleted'], 'tm-events' ),
		/* translators: %s: Number of events. */
		'trashed'   => _n( '%s events moved to the Trash.', '%s events moved to the Trash.', $bulk_counts['trashed'], 'tm-events' ),
		/* translators: %s: Number of events. */
		'untrashed' => _n( '%s events restored from the Trash.', '%s events restored from the Trash.', $bulk_counts['untrashed'], 'tm-events' ),
	);

	return $bulk_messages;
}

/**
 * Admin Columns and Filters for Events
 */

/**
 * Adds custom columns to the events list in the admin.
 *
 * @param array $columns The existing columns in the admin list.
 * @return array The modified columns for the admin list.
 */
function add_events_columns( $columns ) {
	$new_columns = array();

	foreach ( $columns as $key => $label ) {
		$new_columns[ $key ] = $label;

		if ( 'title' === $key ) {
			$new_columns['event_place_name'] = __( 'Place', 'tamarind' );
			$new_columns['event_website']    = __( 'Website', 'tamarind' );
			$new_columns['event_date_start'] = __( 'Start Date', 'tamarind' );
			$new_columns['event_date_end']   = __( 'End Date', 'tamarind' );
			$new_columns['event_featured']   = __( 'Featured', 'tamarind' );
		}
	}

	return $new_columns;
}

/**
 * Renders the values of the custom columns.
 *
 * @param string $column  The name of the column to display.
 * @param int    $post_id The ID of the current post.
 */
function render_events_columns( $column, $post_id ) {
	switch ( $column ) {
		case 'event_place_name':
			echo esc_html( get_field( 'event_place_name', $post_id ) );
			break;

		case 'event_website':
			$url = get_field( 'event_website', $post_id );
			if ( $url ) {
				echo '<a href="' . esc_url( $url ) . '" target="_blank">' . esc_html( $url ) . '</a>';
			}
			break;

		case 'event_date_start':
			$date = get_field( 'event_date_start', $post_id );
			echo $date ? esc_html( gmdate( 'd/m/Y', strtotime( $date ) ) ) : '';
			break;

		case 'event_date_end':
			$date = get_field( 'event_date_end', $post_id );
			echo $date ? esc_html( gmdate( 'd/m/Y', strtotime( $date ) ) ) : '';
			break;

		case 'event_featured':
			$featured = get_field( 'event_featured', $post_id );
			echo $featured ? '✅' : '—';
			break;
	}
}

/**
 * Defines the sortable columns by ACF fields.
 *
 * @param array $columns The array of columns to be sorted.
 * @return array The modified array of sortable columns.
 */
function tamarind_events_sortable_columns( $columns ) {
	$columns['event_date_start'] = 'event_date_start';
	$columns['event_date_end']   = 'event_date_end';
	return $columns;
}

/**
 * Modifies the query to allow sorting by ACF date fields.
 *
 * @param WP_Query $query The query object.
 * @return void
 */
function tamarind_events_custom_orderby( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->get( 'post_type' ) !== 'events' ) {
		return;
	}

	$orderby = $query->get( 'orderby' );

	if ( $orderby === 'event_date_start' || $orderby === 'event_date_end' ) {
		$query->set( 'meta_key', $orderby );
		$query->set( 'orderby', 'meta_value' );
		$query->set( 'meta_type', 'DATE' );
	}
}

/**
 * Añade un filtro en el admin para ver solo los eventos destacados o no destacados.
 */
function tamarind_events_featured_filter() {
	global $typenow;

	if ( 'events' !== $typenow ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$value = isset( $_GET['event_featured_filter'] ) ? $_GET['event_featured_filter'] : '';
	?>
	<select name="event_featured_filter">
		<option value=""><?php esc_html_e( 'All Events', 'tamarind' ); ?></option>
		<option value="1" <?php selected( $value, '1' ); ?>><?php esc_html_e( 'Featured Only', 'tamarind' ); ?></option>
		<option value="0" <?php selected( $value, '0' ); ?>><?php esc_html_e( 'Not Featured', 'tamarind' ); ?></option>
	</select>
	<?php
}

/**
 * Filters events in admin based on whether they are featured or not.
 *
 * @param WP_Query $query The query object.
 * @return void
 */
function tamarind_events_filter_featured_column( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->get( 'post_type' ) !== 'events' ) {
		return;
	}

	if ( isset( $_GET['event_featured_filter'] ) && '' !== $_GET['event_featured_filter'] ) {
		$query->set(
			'meta_query',
			array(
				array(
					'key'     => 'event_featured',
					'value'   => $_GET['event_featured_filter'],
					'compare' => '=',
				),
			)
		);
	}
}
