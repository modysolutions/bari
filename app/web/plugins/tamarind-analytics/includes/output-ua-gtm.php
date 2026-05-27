<?php
/**
 * Handle UA and GTM output functions
 *
 * @package Tamarind_Analytics
 * phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed
 */

namespace tamarind_analytics;

add_action( 'wp_head', __NAMESPACE__ . '\\tm_output_ua_and_gtm', 20 );

/**
 * Output UA or GTM tracking code in the header.
 *
 * @return void
 */
function tm_output_ua_and_gtm(): void {
	$cfg = array(
		'gtag_pref'               => (bool) get_field( 'gtag_pref', 'options' ),
		'ua_tracking_id'          => (string) get_field( 'ga_ua_tracking_id', 'options' ),
		'ua_domain'               => (string) get_field( 'ga_ua_domain', 'options' ),
		'gtm_container_id'        => (string) get_field( 'gtm_container_id', 'options' ),
		'consent_wait_for_update' => (int) get_field( 'consent_wait_for_update_ms', 'options' ),
	);

	/**
	 * =========================
	 * UA ONLY when:
	 * - tracking is enabled for current user
	 * - gtag_pref is OFF
	 * =========================
	 */
	if ( ! $cfg['gtag_pref'] && tm_can_track_current_user() ) {
		tm_output_ua( $cfg );
	}

	/**
	 * =========================
	 * GTM ONLY when:
	 * - gtag_pref is ON
	 * =========================
	 */
	if ( $cfg['gtag_pref'] ) {
		tm_output_gtm( $cfg );
	}
}

/**
 * Output UA tracking code.
 *
 * @param array $cfg Configuration array.
 * @return void
 */
function tm_output_ua( array $cfg ): void {
	$ua = array(
		'tracking_id' => $cfg['ua_tracking_id'],
		'domain'      => $cfg['ua_domain'],
		'is_logged'   => is_user_logged_in(),
		'user_id'     => is_user_logged_in() ? (string) wp_get_current_user()->ID : '',
		'dimensions'  => array(),
	);

	if ( is_single() ) {
		$postid = get_the_ID();
		$date   = tm_post_date_parts();

		$ua['dimensions'] = array(
			'dimension2' => tm_terms_to_pipe_list( tm_post_terms( $postid, 'content_types' ) ),
			'dimension3' => tm_terms_to_pipe_list( tm_post_terms( $postid, 'topics' ) ),
			'dimension4' => tm_terms_to_pipe_list( tm_post_terms( $postid, 'geography' ) ),
			'dimension5' => $date['fecha'],
			'dimension6' => $date['ano'],
			'dimension7' => $date['mes'],
			'dimension8' => $date['dia'],
		);
	}

	if ( is_user_logged_in() ) {
		$ua['dimensions']['dimension1'] = 'Subscribers';
	} else {
		$ua['dimensions']['dimension1'] = 'Visitor';
	}

	tm_ga4_render_template(
		'template-parts/ua.php',
		array( 'ua' => $ua )
	);
}

/**
 * Output GTM tracking code.
 *
 * @param array $cfg Configuration array.
 * @return void
 */
function tm_output_gtm( array $cfg ): void {
	tm_ga4_render_template(
		'template-parts/gtm-consent.php',
		array( 'consent_wait_for_update' => (int) $cfg['consent_wait_for_update'] )
	);

	tm_ga4_render_template(
		'template-parts/gtm-container.php',
		array( 'gtm_container_id' => (string) $cfg['gtm_container_id'] )
	);

	if ( ! tm_can_track_current_user() ) {
		return;
	}

	$gtm_events = tm_build_gtm_events();
	if ( empty( $gtm_events ) ) {
		return;
	}

	tm_ga4_render_template(
		'template-parts/gtm-events.php',
		array( 'events' => $gtm_events )
	);
}
