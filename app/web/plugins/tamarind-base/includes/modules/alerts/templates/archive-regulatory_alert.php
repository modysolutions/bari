<?php
/**
 * The template for displaying Archive "Regulatory Alert" CPT.
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\alerts;

use function tamarind_subscriptions\access\{current_user_can_read_post};
use function tamarind_subscriptions\subscription_plan\{is_alerts_plan};

defined( 'ABSPATH' ) || exit;

get_header();

$current_page = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

if ( is_plugin_active( 'subscribers-report/subscribers-report.php' ) && ( is_user_logged_in() ) ) {
	do_action( 'subscribers_report_add_pageview' );
}
?>

<div id="main-content">
	<?php
	if ( isset( $_GET['geography'] ) ) {
		$geoslug = $_GET['geography'];
	} else {
		$geoslug = '';
	}

	if ( isset( $_GET['content_types'] ) ) {
		$otherslug = $_GET['content_types'];
	} else {
		$otherslug = '';
	}

	$geoslug = get_term_by( 'slug', $geoslug, 'geography' );
	if ( $geoslug && ! empty( $geoslug ) ) {
		$geoname = $geoslug->name;
	} else {
		$geoname = '';
	}

	$otherslug = get_term_by( 'slug', $otherslug, 'content_types' );


	if ( $otherslug && ! empty( $otherslug ) ) {
		$othername = $otherslug->name;
	} else {
		$othername = '';
	}
	?>

	<div class="super_title">
		<div class="wrap">
			<h1>
				<?php
				$el_term      = get_queried_object();
				$archive_link = get_post_type_archive_link( 'regulatory_alert' );

				$pre_title = '';
				if ( isset( $el_term->taxonomy ) ) {
					$pre_title = $el_term->taxonomy;
					$pre_title = str_replace( 'post_tag', 'TobaccoIntelligence - complete coverage of', $pre_title );
					$pre_title = str_replace( 'topics', '', $pre_title );
					$pre_title = str_replace( 'Topics', '', $pre_title );
					echo esc_html( ucfirst( $pre_title ) ) . ' ';
				}
				if ( empty( $geoslug ) || empty( $otherslug ) ) {
					echo "<a href='" . esc_url( $archive_link ) . "'>" . esc_html( get_field( 'alerts_title_landing', 'options' ) ) . '</a>';
				} else {
					$titulo_cat = $othername . ' / ' . $geoname;
					echo esc_html( $titulo_cat );
				}
				?>

				<?php
				if ( isset( $_GET['geoalerts'] ) ) {
					$slug     = $_GET['geoalerts'];
					$term_geo = get_term_by( 'slug', $slug, 'geography' );

					if ( $slug !== '' ) {
						if ( $slug == 'americas' ) {
							$term_slug = $slug;
							$term_name = 'AMERICAS';
						} else {
							$term_slug = $term_geo->slug;
							$term_name = strtoupper( $term_geo->name );
						}
						echo ' / <a href="' . esc_url( $archive_link . '?geoalerts=' . $term_slug . '&filter-tax=geo' ) . '">' . esc_html( $term_name ) . '</a>';
					}
				}
				?>

			</h1>
		</div>
	</div>
	<div id="regulatory-alerts-content">

		<div class="regulatory-alerts-messages tm-layout-main tm-layout-wrapper">
			<?php
			$alerts_tooltip      = get_field( 'alerts_tooltip', 'options' );
			$alerts_tooltip_desc = get_field( 'alerts_new_format_tip', 'options' );
			$alert_desc          = get_field( 'alerts_archive_desc', 'options' );

			// Show only for users without access.
			if ( ! current_user_can_read_post() && ( ! empty( $alerts_tooltip ) || ! empty( $alerts_tooltip_desc ) ) ) {
				?>
				<div class="box-info">
					<?php
					if ( ! empty( $alerts_tooltip ) ) {
						?>
						<div class="tooltip-tip"><?php echo esc_html( $alerts_tooltip ); ?></div>
						<?php
					}
					if ( ! empty( $alerts_tooltip_desc ) ) {
						?>
						<div class="tooltip-tip-content"><?php echo wp_kses_post( $alerts_tooltip_desc ); ?></div>
						<?php
					}
					?>
				</div>
				<?php
			}

			if ( current_user_can_read_post() && ! empty( $alert_desc ) ) {
				?>
				<div class="term-description term-description-archive-alerts">
					<?php echo wp_kses_post( $alert_desc ); ?>
				</div>
				<?php
			}
			?>
		</div>

		<div id="content-area" class="tm-layout-main tm-layout-wrapper tm-layout-main--sidebar tm-layout-main--sidebar-right">

			<?php
			// Show only for non-logged in users.
			if ( ! is_user_logged_in() ) {
				$alert_desc_nologged  = get_field( 'alert_desc_nologged', 'options' );
				$alert_video_nologged = get_field( 'alert_video_nologged', 'options' );
				?>

				<div class="tm-layout-grid tm-layout-grid--xl">
					<div class="mb-30">

						<?php
						if ( ! empty( $alert_desc_nologged ) ) {
							?>
							<div class="alert-desc-nologged">
								<?php echo wp_kses_post( $alert_desc_nologged ); ?>
							</div>
							<?php
						}
						if ( ! empty( $alert_video_nologged ) ) {
							?>
							<div class="alert-video-nologged mt-30">
								<?php echo $alert_video_nologged; // phpcs:ignore ?>
							</div>
							<?php
						}
						?>

					</div>
					<div class="mb-60">
						<?php
						if ( is_plugin_active( 'tamarind-forms/tamarind-forms.php' ) ) {
							\tamarind_forms\display_form\display_form( 'register_regulatory_alerts', true, get_the_id() );
						}
						?>
					</div>
				</div>


				<?php
			} else {
				// Show full content for logged-in users.
				?>

				<main class="tm-layout-main__content">
					<div class="archive-regulatory-alert">
						<?php
						if ( have_posts() ) {
							$counter = 1;
							?>
							<ul class="tm-list--alert">
							<?php
							while ( have_posts() ) {
								the_post();
								$class_article = 'alert-' . $counter;
								
								$alert_anchor = 'alert-' . sanitize_title( get_the_title() );
								?>
								<li id="<?php echo esc_attr( $alert_anchor ); ?>" class="tm-list__item tm-list__item--alert article-alert <?php echo esc_attr( $class_article ); ?>" data-post-id="<?php echo esc_attr( get_the_ID() ); ?>" data-alert-anchor="<?php echo esc_attr( $alert_anchor ); ?>">
									<?php
										$user_can_read_block = false;
										if ( current_user_can_read_post() ) {
											$user_can_read_block = true;
										}

										$geoterms_alert_all  = '';
										$archive_alerts_link = get_post_type_archive_link( 'regulatory_alert' );
										$hiterms             = wp_get_post_terms( get_the_ID(), 'geography', array( 'orderby' => 'name' ) );
										$first_continent     = true;

										foreach ( $hiterms as $key => $hiterm ) {
											if ( ! $first_continent ) {
												$geoterms_alert_all = $geoterms_alert_all . ', ';
											} else {
												$first_continent = false;
											}
											$geoterms_alert_all = $geoterms_alert_all . '<a href="' . $archive_alerts_link . '?geoalerts=' . $hiterm->slug . '&filter-tax=geo"> ' . $hiterm->name . '</a>';
										}

										if ( $user_can_read_block && is_alerts_plan() ) {
											$locked = '<i class="fa fa-unlock" aria-hidden="true"></i>';
										} else {
											$locked = '<i class="fa fa-lock" aria-hidden="true"></i>';
										}

										echo ( '<div class="metainfo">' . get_the_date() . ' - ' . wp_kses_post( $geoterms_alert_all . ' | ' . $locked ) . '</div>' );
									?>
									<div id="tm-list__item--alert-<?php the_ID(); ?>" class="tm-list--alert-content tm-list--alert-content--archive">
										<?php
										if ( $user_can_read_block && is_alerts_plan() ) {
											echo apply_filters( 'the_content', ( get_the_content() ) ); 
										}
										else {
											$content = apply_filters( 'the_content', get_the_content() );
											$allowed_html = array(
												'a'      => array(
													'id' => true,
													'title' => true,
												),
												'strong' => array(),
												'b'      => array(),
											);
											$clear_post = wp_kses( $content, $allowed_html );
											$words = explode( ' ', $clear_post );
											$first_words = implode( ' ', array_slice( $words, 0, 20 ) );
											echo '<p>' . $first_words . '...</p>';
										}
										?>
									</div>
									<?php 
									if ( $user_can_read_block && is_alerts_plan() ) {
										?>
										<button class="tm-list--alert-button tm-list--alert-button--archive" aria-controls="tm-list__item--alert-<?php the_ID(); ?>" aria-expanded="false" title="Mostrar más">
											<i class='fa fa-plus'></i>
										</button>
										<?php
									}
									?>	
								</li>
								<?php
								++$counter;
							};

							?>
							</ul>


							<div class="tm-pagination">
							<?php
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							echo paginate_links(
								array(
									'format'   => '?paged=%#%',
									'current'  => max( 1, $current_page ),
									'mid_size' => 5,
								)
							);
							?>
							</div>


							<?php

							$bottom_term_text = get_field( 'bottom_description', $el_term );

							if ( $bottom_term_text && $bottom_term_text != '' ) {
								?>
								<div class="term-bottom-description term-bottom-description-geography mt-30 mb-30">
									<?php echo $bottom_term_text; ?>
								</div>
								<?php
							}

						} else {
							?>
							<p><?php esc_html_e( 'No Regulatory alerts found.', 'tamarind-base' ); ?></p>
							<?php
						}
						?>
					</div>
				</main>

				<aside class="sidebar-recent-alerts tm-layout-main__aside">
					<?php
					if ( get_field( 'show_alerts_sidebar_geo_filter', 'options' ) ) {
						include plugin_dir_path( __FILE__ ) . '../template-parts/sidebar-alerts-filter.php';
					}
					?>

					<?php
					$term_alerts = get_term_by( 'slug', 'alerts', 'content_types' );
					if ( $term_alerts && $term_alerts->count > 0 ) {
						include plugin_dir_path( __FILE__ ) . '../template-parts/sidebar-alerts-recents-roundups.php';
					}

					$show_term_podcasts = false; // @todo: turn this into a setting
					if ( $show_term_podcasts ) {
						$term_poscast = get_term_by( 'slug', 'regulatory-alerts-podcasts', 'content_types' );
						if ( $term_poscast && $term_poscast->count > 0 ) {
							include plugin_dir_path( __FILE__ ) . '../template-parts/sidebar-alerts-recents-podcast-alerts.php';
						}
					}
					?>
				</aside>

			<?php } ?>

		</div>
	</div>
</div>

<?php get_footer(); ?>

<?php if ( is_user_logged_in() ) : ?>
<script>
(function($) {
	'use strict';

	function trackPageView() {
		if ( window.tamarindAlertsPageTracked ) {
			return;
		}
		window.tamarindAlertsPageTracked = true;

		var pageId = <?php echo get_queried_object_id(); ?>;
		var pageUrl = window.location.href;

		$.ajax({
			url: '/wp-json/tamarind/v2/log-usage',
			method: 'POST',
			data: {
				type: 'view',
				page_id: pageId,
				page_full_url: pageUrl
			},
			beforeSend: function( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', '<?php echo wp_create_nonce( 'wp_rest' ); ?>' );
			}
		});
	}

	function trackAlertExpansion( article ) {
		var postId = article.data( 'post-id' );
		var anchor = article.data( 'alert-anchor' );
		
		if ( ! postId || ! anchor ) {
			return;
		}

		var baseUrl = window.location.href.split( '#' )[0];
		var fullUrl = baseUrl + '#' + anchor;

		$.ajax({
			url: '/wp-json/tamarind/v2/log-usage',
			method: 'POST',
			data: {
				type: 'view',
				page_id: parseInt( postId, 10 ),
				page_full_url: fullUrl
			},
			beforeSend: function( xhr ) {
				xhr.setRequestHeader( 'X-WP-Nonce', '<?php echo wp_create_nonce( 'wp_rest' ); ?>' );
			}
		});
	}

	$( document ).ready( trackPageView );

	$( document ).on( 'click', '.op-btn .fa-plus-circle', function() {
		var article = $( this ).closest( 'article.article-alert' );
		setTimeout(function() {
			trackAlertExpansion( article );
		}, 100);
	});

})(jQuery);
</script>
<?php endif; ?>
