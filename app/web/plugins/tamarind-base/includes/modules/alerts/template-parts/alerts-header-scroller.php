<?php
/**
 * Template for Alerts Header Scroller.
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\alerts;

defined( 'ABSPATH' ) || exit;

use function tamarind_subscriptions\access\{current_user_can_read_post};

$num_slider_alerts = get_field( 'slider_alerts_recent', 'options' );
?>

<div class="new-alerts">
	<div class="wrap">
		<?php
		$tax_geo_query = '';

		$args = array(
			'post_type'      => array( 'regulatory_alert' ),
			'posts_per_page' => $num_slider_alerts,
			'post_status'    => 'publish',
		);

		$geo_group = \tamarind_base\taxonomies\alerts_europe_us_news();

		if ( ! empty( $geo_group ) ) {
			$geo = $geo_group;

			$args['tax_query'] = array(
				array(
					'taxonomy' => 'geography',
					'field'    => 'slug',
					'terms'    => $geo,
				),
			);
		}

		$cache_args = array(
			'user_id' => get_current_user_id(),
			'args'    => $args,
		);
		$cache_key = 'alerts_header_scroller_' . md5( serialize( $cache_args ) );
		$cache_group = 'alerts';
		$alerts_query = wp_cache_get( $cache_key, $cache_group );
		if(!$alerts_query){
			$alerts_query = new \WP_Query( $args );
			wp_cache_set( $cache_key, $alerts_query, $cache_group, HOUR_IN_SECONDS );
		}
		if ( $alerts_query->have_posts() ) {
			?>
			<div class="header-alerts-carousel">
				<h4 class="header-alerts-carousel--title">
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo \tamarind_base\get_svg_icon( 'alert-icon', 'new-icon new-letter', 'alerts' );
					esc_html_e( 'LATEST ALERTS', 'tamarind-base' );
					?>
				</h4>
				<div class="header-alerts-carousel--slideshow swiper">
					<div class="swiper-wrapper">
					<?php
					while ( $alerts_query->have_posts() ) {
						$alerts_query->the_post();

						$user_can_read_block = false;
						if ( current_user_can_read_post() ) {
							$user_can_read_block = true;
						}

						$geography = wp_get_post_terms( get_the_ID(), 'geography' );

						$geoterms_alert_all_no_parents = '';
						$geoterms_alert_all_parents = '';

						$archive_alerts_link = get_post_type_archive_link( 'regulatory_alert' );

						$hiterms = wp_get_post_terms( get_the_ID(), 'geography', array( 'orderby' => 'name' ) );
						$first_continent = true;
						$first_country = true;

						foreach ( $hiterms as $key => $hiterm ) {
							if ( 0 !== $hiterm->parent ) {
								if ( ! $first_country ) {
									$geoterms_alert_all_no_parents = $geoterms_alert_all_no_parents . ' - ';
								} else {
									$first_country = false;
								}
								$geoterms_alert_all_no_parents = $geoterms_alert_all_no_parents . $hiterm->name;
							} else {
								if ( ! $first_continent ) {
									$geoterms_alert_all_parents = $geoterms_alert_all_parents . ' - ';
								} else {
									$first_continent = false;
								}
								$geoterms_alert_all_parents = $geoterms_alert_all_parents . $hiterm->name;
							}
						}
						if ( $geoterms_alert_all_no_parents == '' ) {
							$geoterms_alert_all_no_parents = $geoterms_alert_all_parents;
						}

						$canreadclass = '';
						$canreadattr = '';
						$cant_read_icon = " <i class='fa fa-lock' title='Restricted to subscribers'></i>";

						if ( $user_can_read_block ) {
							$canreadclass = 'canread';
							$cant_read_icon = " <i class='fa fa-plus-circle'></i>";
							$user_can_read_block = true;
							$canreadattr = wp_strip_all_tags( get_the_content() );
						}
						?>
						<div class="swiper-slide <?php echo esc_attr( $canreadclass ); ?>" title="<?php echo esc_attr( $canreadattr ); ?>">
							<span>
								<strong><?php echo esc_html( $geoterms_alert_all_no_parents ); ?></strong> <?php echo esc_html( alertTitleMaxLength( get_the_title() ) ); ?>
							</span>
					</div>
						<?php
					}
					?>
					</div>
				</div>
				<?php

				$link_get_access = get_field( 'alerts_get_access_link', 'options' );
				if ( $link_get_access && is_user_logged_in() ) {
					echo '<a href="' . esc_url( $link_get_access['url'] ) . '" class="allalerts allalerts-register">' . esc_html( $link_get_access['title'] ) . '</a>';
				}

				?>
			</div>
			<?php
		};

		wp_reset_postdata();
		?>
	</div>

</div>
