<?php
/**
 * Functions for Notifications
 *
 * @package Tamarind_Notifications
 */

namespace tamarind_notifications;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp', __NAMESPACE__ . '\tamarind_track_notification_view' );
add_filter( 'get_the_excerpt', __NAMESPACE__ . '\custom_notifications_excerpt', 5, 2 );
add_action( 'wp_ajax_tamarind_mark_notification_read', __NAMESPACE__ . '\tamarind_mark_notification_read_ajax' );
add_action( 'wp_ajax_nopriv_tamarind_mark_notification_read', __NAMESPACE__ . '\tamarind_mark_notification_read_ajax' );


/**
 * Format the notification date
 *
 * @param int $post_id The ID of the post for which the notification date is formatted.
 * @return string
 */
function tm_format_notification_date( $post_id ) {
	// Get the publication date.
	$post_date = get_the_date( 'Y-m-d', $post_id );

	// Get the current date.
	$current_date = gmdate( 'Y-m-d' );

	// Get the date of yesterday.
	$yesterday_date = gmdate( 'Y-m-d', strtotime( '-1 day' ) );

	// Compare the dates.
	if ( $post_date === $current_date ) {
		return 'Now';
	} elseif ( $post_date === $yesterday_date ) {
		return 'Yesterday';
	} else {
		// Return the formatted date.
		return get_the_date( 'd/m/Y', $post_id );
	}
}


/**
 * Format the notification date for the archive page.
 *
 * @param string $post_date The post date in 'Y-m-d H:i:s' format.
 * @return array An array containing 'time_ago' and 'full_date'.
 */
function tm_format_notification_date_archive( $post_date, $on = true ) {
	$time_ago = human_time_diff( strtotime( $post_date ), time() ) . ' ago';

	$full_date = date_i18n( 'F jS Y', strtotime( $post_date ) );
	$full_date = ($on ? 'on ' : '') . $full_date;

	return array(
		'time_ago'  => $time_ago,
		'full_date' => $full_date,
	);
}


/**
 * Displays the latest notifications.
 *
 * @param int $limit Maximum number of notifications to display (-1 for no limit).
 *
 * @return string HTML content.
 */
function tamarind_notifications_list( $limit = -1 ) {
	ob_start();

	$excluded_posts = tamarind_get_read_notifications();

	if ( is_singular( 'notifications' ) ) {
		$current_notification_id = get_the_ID();
		$excluded_posts[]        = $current_notification_id;
		$excluded_posts          = array_unique( $excluded_posts );
	}

	$args = array(
		'post_type'      => 'notifications',
		'posts_per_page' => $limit,
		'post_status'    => 'publish',
		'orderby'        => 'date',
		'order'          => 'DESC',
		'post__not_in'   => $excluded_posts,
	);

	$notifications_query = new \WP_Query( $args );

	// Check if there are notifications.
	if ( ! $notifications_query->have_posts() ) {
		echo '<div class="dropdown-item dropdown-info">' . esc_html__( 'No new notifications found.', 'tm-notifications' ) . '</div>';
		return ob_get_clean();
	}

	// Displays the list of notifications.
	\tamarind_base\print_post_list( $notifications_query, 'excerpt' );

	return ob_get_clean();
}


/**
 * Customize the excerpt for notifications post type.
 *
 * @param string  $excerpt The existing excerpt.
 * @param WP_Post $post The post object.
 *
 * @return string The modified excerpt.
 */
function custom_notifications_excerpt( $excerpt, $post ) {

	if ( 'notifications' !== $post->post_type ) {
		return $excerpt;
	}

	if ( ! empty( $excerpt ) ) {
		return $excerpt;
	}

	// Create the excerpt from the content.
	$text = get_the_content( '', false, $post );
	$text = strip_shortcodes( $text );
	$text = apply_filters( 'the_content', $text );
	$text = str_replace( ']]>', ']]>', $text );

	return wp_trim_words( $text, 15, '...' );
}


/**
 * Generates an excerpt for a notification post in the archive.
 *
 * @param WP_Post $post The post object.
 *
 * @return string The trimmed excerpt.
 */
function tm_archive_notifications_excerpt( $post ) {
	$text = get_the_content( '', false, $post );
	$text = strip_shortcodes( $text );
	$text = apply_filters( 'the_content', $text );
	$text = str_replace( ']]>', ']]>', $text );

	return wp_trim_words( $text, 35, '...' );
}


/**
 * Tracks notification views for logged-in users.
 *
 * Adds the notification ID to the user's list of read notifications
 * when viewing a single notification post.
 */
function tamarind_track_notification_view() {

	if ( is_singular( 'notifications' ) && is_user_logged_in() && ! is_admin() ) {
		$user_id         = get_current_user_id();
		$notification_id = get_the_ID();

		// Get the notifications already read by the user.
		$read_notifications = get_field( 'user_read_notifications', 'user_' . $user_id );

		if ( ! is_array( $read_notifications ) ) {
			$read_notifications = array();
		}

		if ( ! in_array( $notification_id, $read_notifications, true ) ) {
			$read_notifications[] = $notification_id;

			update_field( 'user_read_notifications', $read_notifications, 'user_' . $user_id );
		}
	}
}


/**
 * Retrieves the list of notifications read by the logged-in user.
 *
 * @return array An array of notification IDs that the user has read.
 */
function tamarind_get_read_notifications() {
	if ( ! is_user_logged_in() ) {
		return array();
	}

	$user_id            = get_current_user_id();
	$read_notifications = get_field( 'user_read_notifications', 'user_' . $user_id );

	return is_array( $read_notifications ) ? $read_notifications : array();
}


/**
 * Marks a notification as read via AJAX.
 */
function tamarind_mark_notification_read_ajax() {

	check_ajax_referer( 'tamarind_notifications_nonce', 'security' );

	if ( ! is_user_logged_in() ) {
		wp_send_json_error( 'User not logged in' );
	}

	if ( ! isset( $_POST['notification_id'] ) ) {
		wp_send_json_error( 'Notification ID missing' );
	}

	$user_id         = get_current_user_id();
	$notification_id = intval( $_POST['notification_id'] );

	$read_notifications = get_field( 'user_read_notifications', 'user_' . $user_id );

	if ( ! is_array( $read_notifications ) ) {
		$read_notifications = array();
	}

	if ( ! in_array( $notification_id, $read_notifications, true ) ) {
		$read_notifications[] = $notification_id;
		update_field( 'user_read_notifications', $read_notifications, 'user_' . $user_id );
	}

	wp_send_json_success();
}