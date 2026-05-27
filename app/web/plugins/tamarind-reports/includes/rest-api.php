<?php

namespace tamarind_reports;

use WP_REST_Response;

add_action( 'rest_api_init', __NAMESPACE__ . '\rest_api_init', 100 );

function rest_api_init(): void {
	register_rest_route( 'tamarind/v2', '/usage-report/(?P<type>all|single|detail)', array(
		'methods'             => \WP_REST_Server::READABLE,
		'callback'            => __NAMESPACE__ . '\get_usage_report',
		'permission_callback' => function () {
			return current_user_can( 'read_tamarind_report' );
		}
	) );

	// Usage logging endpoint for tracking page views and downloads
	register_rest_route( 'tamarind/v2', '/log-usage', array(
		'methods'             => \WP_REST_Server::CREATABLE,
		'callback'            => __NAMESPACE__ . '\log_usage',
		'permission_callback' => function () {
			return is_user_logged_in();
		},
		'args'                => array(
			'type' => array(
				'required'          => true,
				'type'              => 'string',
				'enum'              => array( 'view', 'download' ),
				'sanitize_callback' => 'sanitize_text_field',
				'validate_callback' => function( $param ) {
					return in_array( $param, array( 'view', 'download' ), true );
				},
			),
			'page_id' => array(
				'required'          => true,
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'validate_callback' => function( $param ) {
					// Archive pages have no post ID, so we allow 0 for those cases
					$numeric_value = is_numeric( $param ) ? (int) $param : -1;
					return $numeric_value >= 0;
				},
			),
			'page_full_url' => array(
				'required'          => true,
				'type'              => 'string',
				'format'            => 'uri',
				'sanitize_callback' => 'esc_url_raw',
				'validate_callback' => function( $param ) {
					return filter_var( $param, FILTER_VALIDATE_URL ) !== false;
				},
			),
		),
	) );

	register_rest_field(
		'user',
		'email',
		array(
			'get_callback'    => function ( $user ) {
				global $wpdb;
				if ( ! $user instanceof \WP_User ) {
					return null;
				}
				$a = $wpdb->query( $wpdb->prepare( "SELECT user_email FROM {$wpdb->users} WHERE ID = %d", $user->ID ) );

				return $wpdb->get_var( $wpdb->prepare( "SELECT user_email FROM {$wpdb->users} WHERE ID = %d", $user->ID ) );
			},
			'update_callback' => null,
			'schema'          => array(
				'type'        => 'string',
				'format'      => 'email',
				'description' => 'User email address.',
				'context'     => array( 'view' ),
				'readonly'    => true,
			),
		)
	);
}

function get_usage_report( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
	$filters          = $request->get_params();
	$parsed_filters   = \tamarind_reports\get_usage_report_request_filters(
		$filters,
		array(
			'default_from'     => date( 'Y-m-d', strtotime( '-6 month' ) ),
			'default_to'       => date( 'Y-m-d', time() ),
			'default_per_page' => 100,
		)
	);
	$from             = $parsed_filters['from'];
	$to               = $parsed_filters['to'];
	$client_term_id_filter = (int) get_user_meta( get_current_user_id(), 'clientes', true );
	if ( current_user_can( 'manage_options' ) ) {
		$client_term_id_filter = $parsed_filters['client_q'] !== '' ? (int) $parsed_filters['client_q'] : null;
	}
	$include_empty = (bool) $parsed_filters['include_empty'];
	$per_page      = $parsed_filters['per_page'];
	$paged         = $parsed_filters['paged'];
	$detail_filters = $parsed_filters['detail_filters'];

	$type     = $request['type'];
	$function = __NAMESPACE__ . '\\' . $type;
	$response = $function( array(
		'from'                  => $from,
		'to'                    => $to,
		'client_term_id_filter' => $client_term_id_filter,
		'include_empty'         => $include_empty,
		'per_page'              => $per_page,
		'paged'                 => $paged,
		'user_id'               => $parsed_filters['user_id_raw'] !== '' ? $parsed_filters['user_id_raw'] : null,
		'user_q'                => $parsed_filters['user_q'] !== '' ? $parsed_filters['user_q'] : null,
		'user_email'            => $parsed_filters['user_email_raw'] !== '' ? $parsed_filters['user_email_raw'] : null,
		'subscription_plan_id'  => $detail_filters['subscription_plan_id'],
		'content_type_id'       => $detail_filters['content_type_id'],
		'subcontent_type_id'    => $detail_filters['subcontent_type_id'],
		'has_download'          => $detail_filters['has_download'],
		'has_favourite'         => $detail_filters['has_favourite'],
	) );

	return rest_ensure_response( $response );
}

function all( array $filters = array() ): array {
	$filters['view'] = 'all';

	return prepare_user_response( $filters );
}

function single( $filters ): array {
	$filters['view'] = 'single';

	return prepare_user_response( $filters );
}

function detail( $filters ): array {
	$detail_filters = array(
		'subscription_plan_id' => (int) ( $filters['subscription_plan_id'] ?? 0 ),
		'content_type_id'      => (int) ( $filters['content_type_id'] ?? 0 ),
		'subcontent_type_id'   => (int) ( $filters['subcontent_type_id'] ?? 0 ),
		'has_download'         => (string) ( $filters['has_download'] ?? '' ),
		'has_favourite'        => (string) ( $filters['has_favourite'] ?? '' ),
	);

	list( $details, $total, $total_pages ) = \tamarind_reports\detailed_data_paginated(
		$filters['from'],
		$filters['to'],
		$filters['client_term_id_filter'],
		$filters['user_id'] ?? null,
		$filters['include_empty'] ?? false,
		$filters['per_page'] ?? 100,
		$filters['paged'] ?? 1,
		$detail_filters
	);

	return array(
		'users'       => $details,
		'total'       => $total,
		'total_pages' => $total_pages,
		'page'        => $filters['paged'] ?? 1,
		'is_admin'    => current_user_can( 'manage_options' ),
	);
}

/**
 * Handle usage log request (pageview or download)
 * Returns 202 Accepted immediately and queues async processing
 *
 * @param \WP_REST_Request $request
 * @return \WP_REST_Response|\WP_Error
 */
function log_usage( \WP_REST_Request $request ): \WP_REST_Response|\WP_Error {
	$params = $request->get_params();
	
	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		return new \WP_Error(
			'unauthorized',
			__( 'User must be logged in.', 'tamarind-reports' ),
			array( 'status' => 401 )
		);
	}
	
	$client_ids = get_user_client_ids( $user_id );
	
	// Non-admin users must have a client association to track usage
	if ( empty( $client_ids ) && ! current_user_can( 'manage_options' ) ) {
		return new \WP_Error(
			'no_client',
			__( 'User has no associated client.', 'tamarind-reports' ),
			array( 'status' => 400 )
		);
	}
	
	// Admins without client get placeholder ID (skipped during processing)
	if ( empty( $client_ids ) ) {
		$client_ids = array( 0 );
	}
	
	$log_data = array(
		'type'       => $params['type'],
		'user_id'    => $user_id,
		'client_ids' => $client_ids,
		'url'        => $params['page_full_url'],
		'post_id'    => $params['page_id'],
	);
	
	if ( function_exists( 'as_enqueue_async_action' ) ) {
		$action_id = as_enqueue_async_action(
			'tamarind_process_usage_log',
			array( $log_data ),
			'tamarind-usage-logging'
		);
		
		if ( false === $action_id ) {
			return new \WP_Error(
				'queue_failed',
				__( 'Failed to queue usage log.', 'tamarind-reports' ),
				array( 'status' => 500 )
			);
		}
	} else {
		process_usage_log( $log_data );
	}
	
	return rest_ensure_response(
		new \WP_REST_Response(
			array(
				'success' => true,
				'message' => __( 'Usage log queued for processing.', 'tamarind-reports' ),
			),
			202
		)
	);
}

/**
 * Get client_id(s) for a user from the 'clientes' taxonomy
 * Only returns clients with saveStadisticsClient enabled
 *
 * @deprecated In favor of new Subscription management (clients will be content types instead of taxonomies)
 * @todo Subscription management: refactor to use new client structure before release
 *
 * @param int $user_id
 * @return array Array of client term IDs
 */
function get_user_client_ids( int $user_id ): array {
	$terms = wp_get_object_terms( $user_id, 'clientes' );
	
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}
	
	$client_ids = array();
	foreach ( $terms as $term ) {
		// Check if this client should save statistics
		$save_statistics = get_term_meta( $term->term_id, 'saveStadisticsClient', true );
		if ( $save_statistics > 0 ) {
			$client_ids[] = (int) $term->term_id;
		}
	}
	
	return $client_ids;
}

/**
 * Async worker - Process usage log via Action Scheduler
 * Calls Subscribers_Report_Public->subscribers_report_sql_insert_table
 *
 * @param array $log_data Log data from log_usage()
 */
function process_usage_log( array $log_data ): void {
	// Check if Subscribers_Report_Public class is available
	if ( ! class_exists( 'Subscribers_Report_Public' ) ) {
		error_log( 'Tamarind Usage Log Error: Subscribers_Report_Public class not found' );
		return;
	}
	
	// Get or create instance
	$subscribers_report = get_subscribers_report_instance();
	
	if ( ! $subscribers_report ) {
		error_log( 'Tamarind Usage Log Error: Could not get Subscribers_Report_Public instance' );
		return;
	}
	
	// Extract data
	$type       = $log_data['type'];
	$user_id    = $log_data['user_id'];
	$client_ids = $log_data['client_ids'];
	$url        = $log_data['url'];
	$post_id    = $log_data['post_id'];
	
	// Call existing function for each client_id
	foreach ( $client_ids as $client_id ) {
		// Skip placeholder client_id for admins during development
		if ( $client_id === 0 ) {
			error_log( sprintf(
				'Tamarind Usage Log Info: Skipping %s log for user %d (no client association)',
				$type,
				$user_id
			) );
			continue;
		}
		
		try {
			$subscribers_report->subscribers_report_sql_insert_table(
				$type,
				$user_id,
				$client_id,
				$url,
				$post_id
			);
		} catch ( \Exception $e ) {
			error_log( sprintf(
				'Tamarind Usage Log Error: Failed to insert %s log for user %d, client %d: %s',
				$type,
				$user_id,
				$client_id,
				$e->getMessage()
			) );
		}
	}
}

/**
 * Get or create Subscribers_Report_Public instance
 *
 * @return \Subscribers_Report_Public|null
 */
function get_subscribers_report_instance(): ?\Subscribers_Report_Public {
	// Try to get from global if it exists
	global $subscribers_report_public;
	
	if ( $subscribers_report_public instanceof \Subscribers_Report_Public ) {
		return $subscribers_report_public;
	}
	
	// Create new instance if needed
	if ( defined( 'SUBSCRIBERS_REPORT_VERSION' ) ) {
		return new \Subscribers_Report_Public( 'subscribers-report', SUBSCRIBERS_REPORT_VERSION );
	}
	
	return null;
}

// Hook async worker to Action Scheduler
add_action( 'tamarind_process_usage_log', __NAMESPACE__ . '\process_usage_log' );
