<?php
/**
 * The template for displaying single "Notifications" CPT.
 *
 * @package Tamarind_Notifications
 */

namespace tamarind_notifications;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

\tamarind_userarea\redirect_if_no_access_cpt();

$notification_id = get_the_ID();
$is_read         = in_array( $notification_id, tamarind_get_read_notifications(), true );
$date_formatted  = tm_format_notification_date_archive( get_the_date( 'Y-m-d H:i:s' ) );
?>

<div class="super_title">
	<div class="wrap">
		<a href="<?php echo esc_url( get_post_type_archive_link( 'notifications' ) ); ?>"><?php esc_html_e( 'Notifications', 'tm-notifications' ); ?></a>
	</div>
</div>

<div id="tm-single-notifications" class="tm-layout-main tm-layout-wrapper tm-layout-main--sidebar tm-layout-main--sidebar-right">
	<main class="tm-layout-main__content">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

				<tm-accordion class="tm-accordion--pills tm-notifications-accordion" role="region" aria-label="<?php esc_attr_e( 'Notification', 'tm-notifications' ); ?>">
					<ul>
						<li class="accordion-item is-expanded<?php echo $is_read ? ' is-read' : ''; ?> is-expanded">
							<button
								id="notification-<?php echo esc_attr( $notification_id ); ?>-button"
								class="menu-title accordion-header"
								aria-expanded="true"
								aria-controls="notification-<?php echo esc_attr( $notification_id ); ?>-panel"
								type="button"
								data-id="<?php echo esc_attr( $notification_id ); ?>">
								<div class="accordion-title"><?php the_title(); ?></div>
								<div class="accordion-preview">
									<div class="accordion-excerpt"><?php echo esc_html( tm_archive_notifications_excerpt( get_post() ) ); ?></div>
									<div class="accordion-date"><?php echo esc_html( $date_formatted['time_ago'] ); ?></div>
								</div>
							</button>
							<div
								id="notification-<?php echo esc_attr( $notification_id ); ?>-panel"
								class="menu-options"
								role="region"
								aria-labelledby="notification-<?php echo esc_attr( $notification_id ); ?>-button">
								<?php the_content(); ?>
								<div class="accordion-full-date"><?php echo esc_html( $date_formatted['time_ago'] ); ?> <?php echo esc_html( $date_formatted['full_date'] ); ?></div>
							</div>
						</li>
					</ul>
				</tm-accordion>

			</article>
		<?php endwhile; ?>
	</main>
	<aside class="tm-layout-main__aside">
		<?php
		// Display Notifications Sidebar.
		$title_other = __( 'Other notifications', 'tm-notifications' );
		$limit       = 5;
		?>
		<section class="notifications-sidebar tm-sidebar-module">
			<h2 class="tm-sidebar-module__title"><?php echo esc_html( $title_other ); ?></h2>
			<?php
			echo tamarind_notifications_list( $limit ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			// Get the URL for the "View all" link.
			$my_notifications_url = get_post_type_archive_link( 'notifications' );
			echo '<p class="tm-sidebar-module__view-more"><a href="' . esc_url( $my_notifications_url ) . '">' . __( 'View all', 'tm-notifications' ) . '</a></p>';
			?>
		</section>
	</aside>
</div>

<?php get_footer(); ?>