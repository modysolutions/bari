<?php

namespace tamarind_reports;

defined( 'ABSPATH' ) || exit;

add_filter( 'theme_page_templates', __NAMESPACE__ . '\theme_page_templates' );
add_filter( 'template_include', __NAMESPACE__ . '\template_include' );

function get_template_part( string $template, string $slug, string $name ): string {
	$report_template = TM_REPORTS_DIR . "template-parts/{$slug}-{$name}.php";
	if ( 'tamarind-reports' !== $slug || ! file_exists( $report_template ) ) {
		return $template;
	}

	return $report_template;
}


/**
 * Registers and merges custom templates with the existing templates.
 *
 * @param array $templates An array of existing templates.
 *
 * @return array The merged array of existing templates and custom templates.
 */
function theme_page_templates( array $templates ): array {
	return array_merge( $templates, get_custom_templates() );
}

/**
 * Filters the template path and allows custom templates to be used for posts.
 *
 * @param string $template The path to the current template.
 *
 * @return string The filtered template path, potentially replaced with a custom template path.
 */
function template_include( string $template ): string {
	$post = get_post();

	if ( ! $post ) {
		return $template;
	}

	$page_template_slug   = get_post_meta( $post->ID, '_wp_page_template', true );
	$registered_templates = get_custom_templates();
	if ( array_key_exists( $page_template_slug, $registered_templates ) ) {
		$new_template = TM_REPORTS_DIR . 'templates/' . $page_template_slug;
		if ( file_exists( $new_template ) ) {
			return $new_template;
		}
	}

	return $template;
}

/**
 * Returns a multidimensional array with all the custom templates we create. This needs to be populated by hand.
 * @return array
 */
function get_custom_templates(): array {
	return array(
		'page-usage-report.php' => __( 'Usage Report (Clients/Users)', 'tamarind-reports' ),
	);
}

/**
 * Parse and sanitize usage report request filters.
 *
 * @param array $filters
 * @param array $options
 * @return array
 */
function get_usage_report_request_filters( array $filters, array $options = array() ): array {
	$today        = isset( $options['today'] ) ? (string) $options['today'] : current_time( 'Y-m-d' );
	$default_from = isset( $options['default_from'] ) ? (string) $options['default_from'] : date( 'Y-m-d', strtotime( $today . ' -90 days' ) );
	$default_to   = isset( $options['default_to'] ) ? (string) $options['default_to'] : $today;

	$from_input = isset( $filters['from'] ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) $filters['from'] ) ?
		sanitize_text_field( (string) $filters['from'] ) :
		$default_from;
	$to_input   = isset( $filters['to'] ) && preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) $filters['to'] ) ?
		sanitize_text_field( (string) $filters['to'] ) :
		$default_to;

	$include_empty = false;
	if ( array_key_exists( 'include_empty', $filters ) ) {
		$include_empty = ! ! $filters['include_empty'];
	}

	$default_per_page = isset( $options['default_per_page'] ) ? (int) $options['default_per_page'] : 100;
	$per_page         = isset( $filters['per_page'] ) ? (int) $filters['per_page'] : $default_per_page;
	if ( $per_page <= 0 ) {
		$per_page = $default_per_page;
	}

	$allowed_per_page = isset( $options['allowed_per_page'] ) && is_array( $options['allowed_per_page'] ) ? $options['allowed_per_page'] : array();
	if ( ! empty( $allowed_per_page ) && ! in_array( $per_page, $allowed_per_page, true ) ) {
		$fallback_per_page = isset( $options['fallback_per_page'] ) ? (int) $options['fallback_per_page'] : $default_per_page;
		$per_page          = $fallback_per_page > 0 ? $fallback_per_page : $default_per_page;
	}

	$report_page = isset( $filters['report_page'] ) ? max( 1, (int) $filters['report_page'] ) : 1;

	$client_q       = isset( $filters['client'] ) ? trim( sanitize_text_field( (string) $filters['client'] ) ) : '';
	$user_id_raw    = isset( $filters['user_id'] ) ? trim( sanitize_text_field( (string) $filters['user_id'] ) ) : '';
	$user_email_raw = isset( $filters['user_email'] ) ? trim( sanitize_text_field( (string) $filters['user_email'] ) ) : '';
	$detail_filters = get_usage_report_detail_filters( $filters );
	$user_q         = $user_id_raw !== '' ? $user_id_raw : $user_email_raw;
	$run            = isset( $filters['run'] ) && (string) $filters['run'] === '1';

	return array(
		'today'          => $today,
		'default_from'   => $default_from,
		'default_to'     => $default_to,
		'from_input'     => $from_input,
		'to_input'       => $to_input,
		'from'           => $from_input . ' 00:00:00',
		'to'             => $to_input . ' 23:59:59',
		'include_empty'  => $include_empty,
		'per_page'       => $per_page,
		'report_page'    => $report_page,
		'paged'          => $report_page,
		'client_q'       => $client_q,
		'user_id_raw'    => $user_id_raw,
		'user_email_raw' => $user_email_raw,
		'detail_filters' => $detail_filters,
		'subscription_plan_id' => $detail_filters['subscription_plan_id'],
		'content_type_id'      => $detail_filters['content_type_id'],
		'subcontent_type_id'   => $detail_filters['subcontent_type_id'],
		'has_download'         => $detail_filters['has_download'],
		'has_favourite'        => $detail_filters['has_favourite'],
		'user_q'         => $user_q,
		'run'            => $run,
	);
}

/**
 * Parse and sanitize detailed usage report filters.
 *
 * @param array $filters
 * @return array
 */
function get_usage_report_detail_filters( array $filters ): array {
	$subscription_plan_id = isset( $filters['subscription_plan'] ) ? absint( $filters['subscription_plan'] ) : 0;
	$content_type_id      = isset( $filters['content_type'] ) ? absint( $filters['content_type'] ) : 0;
	$subcontent_type_id   = isset( $filters['subcontent_type'] ) ? absint( $filters['subcontent_type'] ) : 0;
	$has_download         = isset( $filters['has_download'] ) ? sanitize_key( (string) $filters['has_download'] ) : '';
	$has_favourite        = isset( $filters['has_favourite'] ) ? sanitize_key( (string) $filters['has_favourite'] ) : '';
	if ( ! in_array( $has_download, array( 'yes', 'no' ), true ) ) {
		$has_download = '';
	}
	if ( ! in_array( $has_favourite, array( 'yes', 'no' ), true ) ) {
		$has_favourite = '';
	}

	return array(
		'subscription_plan_id' => $subscription_plan_id,
		'content_type_id'      => $content_type_id,
		'subcontent_type_id'   => $subcontent_type_id,
		'has_download'         => $has_download,
		'has_favourite'        => $has_favourite,
	);
}

function get_client_id( $client_q ): array {
	$client_term_id_filter = 0;
	$user_filter_id        = 0;
	if ( ! current_user_can( 'manage_options' ) ) {
		$client_term_id_filter = (int) get_user_meta( get_current_user_id(), 'clientes', true );
		return array( $client_term_id_filter, $user_filter_id );
	}
	if ( $client_q !== '' ) {
		if ( str_contains( $client_q, '@' ) ) {
			$u = get_user_by( 'email', $client_q );
			if ( $u ) {
				$user_filter_id = (int) $u->ID;
			}
		} elseif ( is_numeric( $client_q ) ) {
			$term = get_term( (int) $client_q, 'clientes' );
			if ( $term && ! is_wp_error( $term ) ) {
				$client_term_id_filter = (int) $client_q;
			}
		} else {
			$slug = sanitize_title( $client_q );
			$term = get_term_by( 'slug', $slug, 'clientes' );
			if ( ! $term || is_wp_error( $term ) ) {
				$terms = get_terms( array(
					'taxonomy'   => 'clientes',
					'name'       => $client_q,
					'hide_empty' => false,
					'number'     => 1
				) );
				if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
					$term = $terms[0];
				}
			}
			if ( $term && ! is_wp_error( $term ) ) {
				$client_term_id_filter = (int) $term->term_id;
			}
		}
	}

	return array( $client_term_id_filter, $user_filter_id );
}

/**
 * Resolve client name from a mixed client reference (client CPT ID or legacy `clientes` term ID).
 *
 * @param int|string|null $client_reference
 * @param string $default
 * @return string
 */
function get_client_name_from_reference( int|string|null $client_reference, string $default = 'N/A' ): string {
	$client_id = (int) $client_reference;
	if ( $client_id <= 0 ) {
		return $default;
	}

	$company_name = get_post_type( $client_id ) === 'client' ? get_the_title( $client_id ) : false;
	if ( false !== $company_name ) {
		return (string) $company_name;
	}

	$term = get_term( $client_id, 'clientes' );
	if ( $term && ! is_wp_error( $term ) ) {
		return (string) $term->name;
	}

	return $default;
}

function get_download_view_data( array $users, string $from, string $to ): array {
	global $wpdb;
	$pv_counts      = array();
	$dl_counts      = array();
	$user_posts_map = array();
	if ( ! empty( $users ) ) {
		$ids = array();
		foreach ( $users as $u ) {
			$ids[] = (int) $u->ID;
		}
		$ids = array_values( array_filter( array_unique( $ids ) ) );
		if ( ! empty( $ids ) ) {
			$ids_in   = implode( ', ', array_map( 'intval', $ids ) );
			$pv_table = $wpdb->prefix . 'subscriber_report_pageviews';
			$dl_table = $wpdb->prefix . 'subscriber_report_downloaded_docs';
			// Grouped counts per user
			$rows_pv = (array) $wpdb->get_results( $wpdb->prepare(
				"SELECT user_id, COUNT(*) c FROM {$pv_table}
             WHERE page_date BETWEEN %s AND %s AND user_id IN ({$ids_in}) GROUP BY user_id",
				$from, $to
			) );
			foreach ( $rows_pv as $r ) {
				$pv_counts[ (int) $r->user_id ] = (int) $r->c;
			}
			$rows_dl = (array) $wpdb->get_results( $wpdb->prepare(
				"SELECT user_id, COUNT(*) c FROM {$dl_table}
             WHERE page_date BETWEEN %s AND %s AND user_id IN ({$ids_in}) GROUP BY user_id",
				$from, $to
			) );
			foreach ( $rows_dl as $r ) {
				$dl_counts[ (int) $r->user_id ] = (int) $r->c;
			}
			// Interacted posts per user (views âˆª downloads) in one union
			$rows_up = (array) $wpdb->get_results( $wpdb->prepare(
				"SELECT user_id, post_id FROM (
                SELECT user_id, post_id FROM {$pv_table} WHERE post_id > 0 AND page_date BETWEEN %s AND %s AND user_id IN ({$ids_in})
                UNION
                SELECT user_id, post_id FROM {$dl_table} WHERE post_id > 0 AND page_date BETWEEN %s AND %s AND user_id IN ({$ids_in})
            ) t",
				$from, $to, $from, $to
			) );
			foreach ( $rows_up as $r ) {
				$uid = (int) $r->user_id;
				$pid = (int) $r->post_id;
				if ( $uid && $pid ) {
					if ( ! isset( $user_posts_map[ $uid ] ) ) {
						$user_posts_map[ $uid ] = array();
					}
					$user_posts_map[ $uid ][ $pid ] = true;
				}
			}
		}
	}

	return array( $pv_counts, $dl_counts, $user_posts_map );
}

function get_clients(): array {
	$terms = get_terms( array( 'taxonomy' => 'clientes', 'hide_empty' => true ) );
	if ( is_wp_error( $terms ) ) {
		return array();
	}

	return array_map( function ( $term ) {
		return array(
			'id'    => $term->term_id,
			'name'  => $term->name,
			'count' => $term->count,
		);
	}, $terms );
}

function prepare_user_response( array $filters = array() ): array {
	list( $total_users, $users, $total_pages ) = \tamarind_reports\map_users_to_clients( $filters );
	$paged = isset( $filters['paged'] ) ? (int) $filters['paged'] : 1;

	return array(
		'total'       => $total_users,
		'users'       => $users,
		'is_admin'    => current_user_can( 'manage_options' ),
		'total_pages' => $total_pages,
		'page'        => $paged,
	);
}

function map_users_to_clients( array $filters ): array {
	$view                  = isset( $filters['view'] ) ? (string) $filters['view'] : 'all';
	$from                  = isset( $filters['from'] ) ? (string) $filters['from'] : '';
	$to                    = isset( $filters['to'] ) ? (string) $filters['to'] : '';
	$client_term_id_filter = isset( $filters['client_term_id_filter'] ) ? (int) $filters['client_term_id_filter'] : null;
	$include_empty         = ! empty( $filters['include_empty'] );
	$per_page              = isset( $filters['per_page'] ) ? (int) $filters['per_page'] : 100;
	$paged                 = isset( $filters['paged'] ) ? (int) $filters['paged'] : 1;
	$user_id               = $filters['user_id'] ?? null;
	$user_email            = isset( $filters['user_email'] ) ? (string) $filters['user_email'] : '';

	if ( $view === 'all' ) {
		$res = \tamarind_reports\user_ids_paginated(
			$from,
			$to,
			$client_term_id_filter,
			$include_empty,
			$per_page,
			$paged
		);
	} else {
		$include_empty = true;
		// Convert email to user_id if user_id not provided
		$single_user_id = $user_id ?? null;
		if ( ! $single_user_id && ! empty( $user_email ) ) {
			$user = get_user_by( 'email', $user_email );
			$single_user_id = $user ? $user->ID : null;
		}
		$res = array(
			'ids'   => $single_user_id ? array( $single_user_id ) : array(),
			'total' => $single_user_id ? 1 : 0
		);
	}
	$user_ids    = (array) ( $res['ids'] ?? array() );
	$total_pages = 1;
	$total_users = (int) ( $res['total'] ?? 0 );
	$users       = array();
	if ( ! empty( $user_ids ) ) {
		$args    = array(
			'include' => $user_ids,
			'orderby' => 'include',
			'fields'  => array( 'ID', 'user_email' ),
		);
		$q       = new \WP_User_Query( $args );
		$results = (array) $q->get_results();
		$ct_sets = \tamarind_reports\content_types_sets();
		list( $pv_counts, $dl_counts, $user_posts_map ) = \tamarind_reports\get_download_view_data( $results, $from, $to );
		$users = \tamarind_reports\user_rows( $results, $ct_sets, $pv_counts, $dl_counts, $user_posts_map, $include_empty );
		$total_pages = (int) ceil( $total_users / $per_page );
	}

	return array( $total_users, $users, $total_pages );
}
