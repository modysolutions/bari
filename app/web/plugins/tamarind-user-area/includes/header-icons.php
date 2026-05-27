<?php
/**
 * Header icons.
 *
 * @package Tamarind_UserArea
 *
 * phpcs:disable WordPress.Files.FileName
 */

namespace tamarind_userarea;

defined( 'ABSPATH' ) || exit;

/**
 * Display header icons.
 *
 * @return void
 */
function display_header_icons() {
	display_contact_icon();
	if ( is_plugin_active( 'tamarind-favourites/tamarind-favourites.php' ) ) {
		display_favourite_icon();
	}

	if ( is_plugin_active( 'tamarind-notifications/tamarind-notifications.php' ) && check_cpt_access( 'notifications' ) ) {
		display_notifications_icon();
	}
}

/**
 * Display contact icon.
 *
 * @return void
 */
function display_contact_icon() {
	$link = get_field( 'contact_icon_link', 'option' );

	if ( ! $link ) {
		return;
	}
	?>
	<div class="header-icon icon-contact">
		<a href="mailto:<?php echo esc_attr( $link ); ?>" title="Contact us">
			<?php echo \tamarind_base\get_svg_icon( 'contact', '', 'Contact us' ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</a>
	</div>
	<?php
}

/**
 * Display favourite icon.
 *
 * @return void
 */
function display_favourite_icon() {
	$menu = \tamarind_base\get_svg_icon( 'favourite', '', 'My Favourites' );

	// Get the user favourites.
	$user_id    = get_current_user_id();
	$favourites = get_field( 'user_favourite_posts', 'user_' . $user_id );
	$limit      = get_field( 'favourites_number_of_items', 'option' );

	if ( ! empty( $favourites ) ) {
		$favourites_query = \tamarind_base\get_posts_by_ids( $favourites, $limit, array( 'post', 'regulatory_alert' ), 'post__in' );

		ob_start();
		$content = \tamarind_base\print_post_list( $favourites_query, 'thumbnail' );
		$content = ob_get_clean();
	} else {
		$content = '<div class="dropdown-item dropdown-info">No favourites found.</div>';
	}

	$my_favourites_url = \tamarind_favourites\get_menu_link_by_key( 'my_favourites' );
	$footer = '<a href="' . $my_favourites_url . '">' . __( 'View all', 'tm-user-area' ) . '</a>';

	echo '<div class="header-icon icon-favourites">';
	\tamarind_base\render_tm_dropdown( 'tm-favourites', $menu, __( 'My Favourites', 'tm-user-area' ), $content, $footer );
	echo '</div>';
}

/**
 * Display notifications icon.
 *
 * @return void
 */
function display_notifications_icon() {
	$menu = display_notification_badge();

	$limit   = get_field( 'notifications_number_of_items', 'option' );
	$content = \tamarind_notifications\tamarind_notifications_list( $limit );

	$notifications_url = get_post_type_archive_link( 'notifications' );
	$footer            = '<a href="' . $notifications_url . '">' . __( 'View all', 'tm-user-area' ) . '</a>';

	echo '<div class="header-icon icon-notifications">';
	\tamarind_base\render_tm_dropdown( 'tm-notifications', $menu, __( 'Notifications', 'tm-user-area' ), $content, $footer );
	echo '</div>';
}

/**
 * Display the notification badge.
 *
 * @return string
 */
function display_notification_badge() {
	$badge = function_exists( 'tamarind_base\get_svg_icon' )
		? \tamarind_base\get_svg_icon( 'notification', '', 'Notifications' )
		: '';

	$read_notifications = function_exists( 'tamarind_notifications\tamarind_get_read_notifications' )
		? \tamarind_notifications\tamarind_get_read_notifications()
		: array();

	$args = array(
		'post_type'      => 'notifications',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'post__not_in'   => $read_notifications,
	);

	$notifications_query = new \WP_Query( $args );
	$notification_count  = $notifications_query->found_posts; // Número de notificaciones.

	// Si hay notificaciones, añade el número superpuesto.
	if ( $notification_count > 0 ) {
		$badge .= '<span class="icon-badge notification-badge">' . esc_html( $notification_count ) . '</span>';
	}

	return $badge;
}
