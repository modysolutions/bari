<?php
/**
 * Handle common analytics functions
 *
 * @package Tamarind_Analytics
 * phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed
 */

namespace tamarind_analytics;

use function tamarind_subscriptions\access\{current_user_can_read_post, is_role, get_type_access_roles};

/**
 * Functions to manage Google Analytics parameters and events.
 *
 * @param string $name UTM name to look for.
 * @return string URL with GA parameters or empty string.
 */
function set_ga_params( string $name ): string {
	$link_url = '';
	if ( have_rows( 'utm_links_params', 'options' ) ) {
		while ( have_rows( 'utm_links_params', 'options' ) ) {
			the_row();

			$cta_name = get_sub_field( 'utm_name', 'options' );

			// solo para poner el mismo nombre que se utiliza a nivel interno en GA y LTW - no se utiliza para nada de programación.
			// $utmNameGA = get_sub_field( 'utm_analytics_name', 'options' );!
			if ( $cta_name === $name ) {
				$active_url = get_sub_field( 'utm_active_url', 'options' );
				if ( true === $active_url ) {
					$link_url = get_sub_field( 'utm_url', 'options' );
				} else {
					$link_url = '';
				}

				if ( '' !== $cta_name ) {
					$link_url .= '?gap=' . $cta_name;
				}
			}
		}
	}
	return $link_url;
}

/**
 * Get event name for Google Analytics based on 'gap' URL parameter.
 *
 * @return string Event name.
 */
function get_event_ga(): string {
	// default event for page.
	$event_name = 'pageview';
	// if post param named GAP is set, then use it as event name.
	if ( isset( $_GET['gap'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$name = $_GET['gap']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( have_rows( 'utm_links_params', 'options' ) ) {
			while ( have_rows( 'utm_links_params', 'options' ) ) {
				the_row();

				if ( ( get_sub_field( 'utm_name', 'options' ) === $name ) && ( get_sub_field( 'utm_event_name', 'options' ) !== '' ) ) {
					$event_name = get_sub_field( 'utm_event_name', 'options' );
				}
			}
		}
	}
	return $event_name;
}

/**
 * Safe wrapper for wp_get_post_terms().
 *
 * @param int    $post_id Post ID to get terms for.
 * @param string $taxonomy Taxonomy to get terms from.
 *
 * @return array<int, WP_Term>
 */
function tm_post_terms( int $post_id, string $taxonomy ): array {
	$terms = wp_get_post_terms( $post_id, $taxonomy );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}
	return $terms;
}

/**
 * Convert array of WP_Term to pipe-separated list of names.
 *
 * @param array<int, WP_Term> $terms Array of terms.
 *
 * @return string Pipe-separated list of term names.
 */
function tm_terms_to_pipe_list( array $terms ): string {
	if ( empty( $terms ) ) {
		return '';
	}
	$names = wp_list_pluck( $terms, 'name' );
	$names = array_map( 'strval', $names );
	return implode( '|', $names );
}

/**
 * Date strings exactly like current code uses.
 *
 * @return array{fecha: string, ano: string, mes: string, dia: string}
 */
function tm_post_date_parts(): array {
	return array(
		'fecha' => (string) get_the_date(),
		'ano'   => (string) get_the_date( 'Y' ),
		'mes'   => (string) get_the_date( 'm' ),
		'dia'   => (string) get_the_date( 'd' ),
	);
}

/**
 * True if we should load/emit analytics for the current user.
 * - Visitors: trackable (subject to consent elsewhere).
 * - Logged-in users: trackable only if NOT staff.
 *
 * @return bool True if we can track current user.
 */
function tm_can_track_current_user(): bool {
	if ( ! is_user_logged_in() ) {
		return true;
	}

	$user = wp_get_current_user();
	if ( ! ( $user instanceof \WP_User ) || ! $user->exists() ) {
		return false;
	}

	// Exclude staff roles.
	$administrative_roles = get_type_access_roles( 'roles_administrative_access' );
	if ( is_role( $administrative_roles ) ) {
		return false;
	}

	return true;
}

/**
 * 'Subscribers' or 'Visitor' depending on login status.
 *
 * @return string 'Subscribers' or 'Visitor'.
 */
function tm_login_users_val(): string {
	return is_user_logged_in() ? 'Subscribers' : 'Visitor';
}

/**
 * Build GTM events for taxonomy terms.
 *
 * @param array<int, WP_Term> $terms      Array of terms.
 * @param string              $event_name Event name.
 * @param string              $value_key  Key for term name.
 * @return array<int, array<string, string>> Array of events.
 */
function tm_build_gtm_events_taxonomy_terms( array $terms, string $event_name, string $value_key ): array {
	$events = array();

	if ( empty( $terms ) ) {
		return $events;
	}

	foreach ( $terms as $term ) {
		if ( ! isset( $term->name ) ) {
			continue;
		}

		$events[] = array(
			'event'    => $event_name,
			$value_key => (string) $term->name,
		);
	}
	return $events;
}

/**
 * Build GTM events for current page.
 *
 * @return array<string, mixed> Array of events.
 */
function tm_build_gtm_events(): array {
	$events = array();

	// LaTevaWeb ha pedido que no se envíe el email del usuario como evento del tag manager.
	$login_users_val = tm_login_users_val(); // 'Subscribers' o 'Visitor'.

	if ( ! is_single() ) {
		$events = array(
			'event'      => get_event_ga(),
			'loginUsers' => $login_users_val,
		);
		return $events;
	}

	// single post.
	$postid        = get_the_ID();
	$content_types = tm_post_terms( $postid, 'content_types' );
	$topics        = tm_post_terms( $postid, 'topics' );
	$geographies   = tm_post_terms( $postid, 'geography' );
	$date          = tm_post_date_parts();

	$events[] = array(
		'event'       => 'pageview',
		'loginUsers'  => $login_users_val,
		'contentType' => tm_terms_to_pipe_list( $content_types ),
		'topic'       => tm_terms_to_pipe_list( $topics ),
		'geography'   => tm_terms_to_pipe_list( $geographies ),
		'fecha'       => $date['fecha'],
		'ano'         => $date['ano'],
		'mes'         => $date['mes'],
		'dia'         => $date['dia'],
	);

	$events_content_types = tm_build_gtm_events_taxonomy_terms( $content_types, 'contentTypeEvent', 'contentTypeName' );
	$events_topics        = tm_build_gtm_events_taxonomy_terms( $topics, 'topicEvent', 'topicName' );
	$events_geographies   = tm_build_gtm_events_taxonomy_terms( $geographies, 'geographyEvent', 'geographyName' );
	$events = array_merge( $events, $events_content_types, $events_topics, $events_geographies );

	if ( current_user_can_read_post() ) {
		$events[] = array(
			'event'         => 'userContent',
			'contentAccess' => 'Content Allowed',
		);
	}

	return $events;
}
