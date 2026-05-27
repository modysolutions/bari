<?php
/**
 * The template for displaying archive pages for the "Notifications" CPT.
 *
 * @package Tamarind_Notifications
 */

namespace tamarind_notifications;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

\tamarind_userarea\redirect_if_no_access_cpt();

$current_page = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
$read_notifications = tamarind_get_read_notifications();

$args = array(
	'post_type'      => 'notifications',
	'post_status'    => 'publish',
	'posts_per_page' => 8,
	'paged'          => $current_page,
);

$notifications_query = new \WP_Query( $args );
?>

<div id="main-content">
	<div class="super_title">
		<div class="wrap">
			<h1>
				<?php post_type_archive_title(); ?>
			</h1>
		</div>
	</div>

	<div id="tm-archive-notifications" class="tm-layout-main tm-layout-wrapper">
		<main class="tm-layout-main__content">
			<?php if ( $notifications_query->have_posts() ) : ?>
				<tm-accordion class="tm-accordion--pills tm-notifications-accordion" role="region" aria-label="<?php esc_attr_e( 'Notifications Accordion', 'tm-notifications' ); ?>">
					<ul>
						<?php 
						foreach ( $notifications_query->posts as $notification ) :
							$notification_id    = $notification->ID;
							$item_id            = 'notification-item-' . $notification_id;
							$notification_title = get_the_title( $notification );
							$excerpt            = tm_archive_notifications_excerpt( $notification );
							$content            = apply_filters( 'the_content', $notification->post_content );
							$date_formatted     = tm_format_notification_date_archive( $notification->post_date );
							$is_read            = in_array( $notification_id, $read_notifications, true );
							?>
							<li class="accordion-item<?php echo $is_read ? ' is-read' : ''; ?>">
								<button 
									id="<?php echo esc_attr( $item_id ); ?>-button" 
									class="menu-title accordion-header" 
									aria-expanded="false" 
									aria-controls="<?php echo esc_attr( $item_id ); ?>-panel"
									type="button" 
									data-id="<?php echo esc_attr( $notification_id ); ?>"
								>
									<div class="accordion-title"><?php echo esc_html( $notification_title ); ?></div>
									<div class="accordion-preview">
										<div class="accordion-excerpt"><?php echo esc_html( $excerpt ); ?></div>
										<div class="accordion-date"><?php echo esc_html( $date_formatted['time_ago'] ); ?></div>
									</div>
								</button>
								<div 
									id="<?php echo esc_attr( $item_id ); ?>-panel" 
									class="menu-options" 
									role="region"
									aria-labelledby="<?php echo esc_attr( $item_id ); ?>-button"
									hidden
								>
									<?php echo wp_kses_post( $content ); ?>
									<div class="accordion-full-date"><?php echo esc_html( $date_formatted['time_ago'] ); ?> <?php echo esc_html( $date_formatted['full_date'] ); ?></div>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				</tm-accordion>

				<div class="tm-pagination">
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- paginate_links outputs safe HTML.
					echo paginate_links(
						array(
							'format'  => '?paged=%#%',
							'current' => max( 1, $current_page ),
							'total'   => $notifications_query->max_num_pages,
						)
					);
					?>
				</div>

			<?php else : ?>
				<p><?php esc_html_e( 'No new notifications found.', 'tm-notifications' ); ?></p>
			<?php endif; ?>
		</main>
	</div>
</div>

<?php get_footer(); ?>