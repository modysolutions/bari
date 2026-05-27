<?php
/**
 * Functions for Regulatory Alerts
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\alerts;

use function tamarind_subscriptions\access\{current_user_can_read_post};

defined( 'ABSPATH' ) || exit;


add_filter( 'template_include', __NAMESPACE__ . '\regulatory_alert_archive_template' );
add_action( 'tm_add_alerts_scroller', __NAMESPACE__ . '\add_alerts_scroller' );
add_action( 'tm_add_alerts_tooltip', __NAMESPACE__ . '\add_alerts_tooltip' );
add_filter( 'tm_get_show_alerts', __NAMESPACE__ . '\show_alerts_hook', 10, 3 );


/**
 * Loads the Regulatory Alert archive template
 *
 * @param string $template Current template.
 *
 * @return string Regulatory Alert archive template.
 */
function regulatory_alert_archive_template( string $template ): string {
	if ( is_post_type_archive( 'regulatory_alert' ) ) {
		$plugin_path   = plugin_dir_path( __FILE__ );
		$template_path = $plugin_path . '/templates/archive-regulatory_alert.php';
		if ( file_exists( $template_path ) ) {
			return $template_path;
		}
	}
	return $template;
}


/**
 * Adds the Alerts Scroller to the page if conditions are met.
 *
 * Checks the header style and the 'show_alerts_slider' option before including the alerts carousel template part.
 *
 * @return void
 */
function add_alerts_scroller(): void {
	if ( 'basic' === get_post_meta( get_the_ID(), '_header_style', true ) ) {
		return;
	}

	if ( get_field( 'show_alerts_slider', 'options' ) ) {
		include plugin_dir_path( __FILE__ ) . '/template-parts/alerts-header-scroller.php';
	}
}


/**
 * Adds the Alerts Tooltip to the page.
 *
 * @return void
 */
function add_alerts_tooltip(): void {
	if ( get_field( 'show_alerts_tooltip', 'options' ) ) {
		include plugin_dir_path( __FILE__ ) . '/template-parts/alerts-tooltip.php';
	}
}


/**
 * Hook to display regulatory alerts based on geography, geo group, and topic.
 *
 * @param string $geo       The geographic region slug to filter alerts.
 * @param string $geo_group The geographic group slug to filter alerts.
 * @param string $topic     The topic slug to filter alerts.
 *
 * @return string           The HTML output of the alerts.
 */
function show_alerts_hook( string $geo, string $geo_group, string $topic ): string {
	return show_alerts( $geo, $geo_group, $topic );
}

/**
 * Displays regulatory alerts based on geography, geo group, and topic.
 *
 * @param string      $geo       The geographic region slug to filter alerts.
 * @param string      $geo_group The geographic group slug to filter alerts.
 * @param string|null $topic     The topic slugs to filter alerts (optional).
 * @return string                The HTML output of the alerts.
 */
function show_alerts( string $geo, string $geo_group, ?string $topic = '' ) : string {
	if ( ! is_user_logged_in() ) {
		return '';
	}

	$cache_key_args = array(
		'subscription_plan' => \tamarind_subscriptions\subscription_plan\get_user_plan_id(),
		'geo'       => $geo,
		'geo_group' => $geo_group,
		'topic'     => $topic,
	);

	ksort( $cache_key_args );
	$cache_key = 'alerts_result_' . md5( serialize( $cache_key_args ) );
	$cache_group = 'alerts';
	$alerts_query = wp_cache_get( $cache_key, $cache_group );
	$alerts_to_show_on_home_block = get_field( 'alerts_to_show_on_home_block', 'options' );
	if ( ! $alerts_query ) {
		$query_args = array(
			'post_type'      => 'regulatory_alert',
			'posts_per_page' => $alerts_to_show_on_home_block,
			'post_status'    => 'publish',
		);

		if ( ! empty( $geo ) ) {
			if ( 'international' === $geo ) {
				$query_args['tax_query'][] = array(
					'taxonomy' => 'geography',
					'field'    => 'slug',
					'terms'    => array( 'united-states', 'europe' ),
					'operator' => 'NOT IN',
				);
			} elseif ( 'americas' === $geo ) {
				$query_args['tax_query'][] = array(
					'taxonomy' => 'geography',
					'field'    => 'slug',
					'terms'    => array( 'north-america', 'south-america', 'central-america' ),
					'operator' => 'IN',
				);
			} else {
				$query_args['tax_query'][] = array(
					'taxonomy' => 'geography',
					'field'    => 'slug',
					'terms'    => $geo,
				);
			}
		} else {
			if ( ! empty( $geo_group ) ) {
				$query_args['tax_query'][] = array(
					'taxonomy' => 'geography',
					'field'    => 'slug',
					'terms'    => $geo_group,
				);
			}
		}

		if ( ! empty( $topic ) ) {
			if ( ! isset( $query_args['tax_query'] ) ) {
				$query_args['tax_query'] = array();
			}
			$query_args['tax_query'][] = array(
				'taxonomy' => 'topics',
				'field'    => 'slug',
				'terms'    => $topic,
			);
		}

		$alerts_query = new \WP_Query( $query_args );
        wp_cache_set( $cache_key, $alerts_query, $cache_group, 10 * MINUTE_IN_SECONDS );
	}
	$image_for_alerts = get_field( 'image_for_alerts', 'options' );
	ob_start();

	if ( $alerts_query->have_posts() ) {
		?>
		<article class="recent-alerts">
			<?php if ( $image_for_alerts ) : ?>
				<figure class="recent-alerts-image lazy" 
				data-src="<?php echo esc_url( $image_for_alerts['sizes']['medium'] ); ?>"
				title="<?php esc_attr_e( 'Alerts', 'tamarind-base' ); ?>"></figure>
			<?php endif; ?>

			<div class="recent-alerts-wrapper">
				<dl class="alerts-group tm-list--alert">

				<?php
				$alert_counter = 1;
				while ( $alerts_query->have_posts() ) {
					$alerts_query->the_post();

					$user_can_read_alert = current_user_can_read_post();

					$template_vars = array(
						'alert_item_counter'  => $alert_counter,
						'user_can_read_alert' => $user_can_read_alert,
					);

					include plugin_dir_path( __FILE__ ) . '/template-parts/geography/alert-item.php';

					$alert_counter++;
				}

				?>

				</dl>
			</div>
		</article>
		<?php
	}
	wp_reset_postdata();
	return ob_get_clean();
}
