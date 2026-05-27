<?php
/**
 * @package tamarind_reports
 */

namespace tamarind_reports;

defined( 'ABSPATH' ) || exit;

function get_usage_report_summary_headers(): array {
	return array(
		'Client',
		'User Email',
		'Page Views',
		'Downloads',
		'Topics',
		'Top Topics (5)',
		'Geographies',
		'Top Geographies (10)',
		'Regulatory %',
		'Market %'
	);
}

function get_usage_report_detail_headers(): array {
	return array(
		'Date',
		'User Email',
		'User status',
		'Company',
		'Client status',
		'Subscription plan',
		'N. users',
		'Post Title',
		'Post ID',
		'Event Type',
		'Download URL',
		'Download Type',
		'Favourites',
		'Content Types',
		'Subcontent Types',
		'Geographies',
		'Topics',
		'Author',
		'Publication Date',
		'Last login',
		'Client creation date'
	);
}

function map_usage_report_summary_row_to_csv( array $row ): array {
	return array(
		(string) ( $row['client'] ?? '' ),
		(string) ( $row['email'] ?? '' ),
		(int) ( $row['page_views'] ?? 0 ),
		(int) ( $row['downloads_count'] ?? 0 ),
		(int) ( $row['topics_count'] ?? 0 ),
		(string) ( $row['top_topics'] ?? '' ),
		(string) ( $row['geos'] ?? '' ),
		(string) ( $row['top_geos'] ?? '' ),
		(int) ( $row['regulatory_percent'] ?? 0 ),
		(int) ( $row['market_percent'] ?? 0 ),
	);
}

function map_usage_report_detail_row_to_csv( array $row ): array {
	return array(
		(string) ( $row['date'] ?? '' ),
		(string) ( $row['email'] ?? '' ),
		(string) ( $row['user_status'] ?? '' ),
		(string) ( $row['company'] ?? '' ),
		(string) ( $row['client_status'] ?? '' ),
		(string) ( $row['subscription_plan'] ?? '' ),
		(int) ( $row['client_users_count'] ?? 0 ),
		(string) ( $row['title'] ?? '' ),
		(int) ( $row['post_id'] ?? 0 ),
		(string) ( $row['event_type'] ?? '' ),
		(string) ( $row['download_url'] ?? '' ),
		(string) ( $row['download_type'] ?? '' ),
		(string) ( $row['favourites'] ?? '' ),
		(string) ( $row['content_types'] ?? '' ),
		(string) ( $row['subcontent_types'] ?? '' ),
		(string) ( $row['geographies'] ?? '' ),
		(string) ( $row['topics'] ?? '' ),
		(string) ( $row['author'] ?? '' ),
		(string) ( $row['publication_date'] ?? '' ),
		(string) ( $row['last_login'] ?? '' ),
		(string) ( $row['client_creation_date'] ?? '' ),
	);
}

function resolve_usage_report_single_filters( string $user_q ): array {
	$user_filter_id = null;
	$user_email     = null;

	if ( $user_q !== '' ) {
		if ( ctype_digit( $user_q ) ) {
			$user_filter_id = (int) $user_q;
		} else {
			$user_email = $user_q;
		}
	}

	return array( $user_filter_id, $user_email );
}

function export_csv(
	$view,
	$from_input,
	$to_input,
	$from,
	$to,
	$client_term_id_filter,
	$user_filter_id,
	$include_empty,
	$user_q,
	int $subscription_plan_id = 0,
	int $content_type_id = 0,
	int $subcontent_type_id = 0,
	string $has_download = '',
	string $has_favourite = ''
): void {
	nocache_headers();
	$filename = ( $view === 'detail' ? 'usage-report-activity-log' : 'usage-report-' . $view ) . '-' . $from_input . '_to_' . $to_input . '-' . time() . '.csv';
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=' . $filename );

	$out = fopen( 'php://output', 'w' );

	if ( $view === 'detail' ) {
		// Headers
		fputcsv( $out, get_usage_report_detail_headers() );

		$chunk       = 200;
		$paged       = 1;
		$total_pages = 1;

		do {
			$response = \tamarind_reports\detail( array(
				'from'                  => $from,
				'to'                    => $to,
				'client_term_id_filter' => $client_term_id_filter ? (int) $client_term_id_filter : null,
				'user_id'               => $user_filter_id,
				'include_empty'         => false,
				'per_page'              => $chunk,
				'paged'                 => $paged,
				'subscription_plan_id'  => $subscription_plan_id,
				'content_type_id'       => $content_type_id,
				'subcontent_type_id'    => $subcontent_type_id,
				'has_download'          => $has_download,
				'has_favourite'         => $has_favourite,
			) );

			$rows = (array) ( $response['users'] ?? array() );
			foreach ( $rows as $row ) {
				fputcsv( $out, map_usage_report_detail_row_to_csv( (array) $row ) );
			}

			$total_pages = max( 1, (int) ( $response['total_pages'] ?? 1 ) );
			$paged ++;
		} while ( $paged <= $total_pages );
	} elseif ( $view === 'all' ) {
		// Headers
		fputcsv( $out, get_usage_report_summary_headers() );

		$chunk       = 200;
		$paged       = 1;
		$total_pages = 1;

		do {
			$response = \tamarind_reports\prepare_user_response( array(
				'view'                  => 'all',
				'from'                  => $from,
				'to'                    => $to,
				'client_term_id_filter' => $client_term_id_filter ? (int) $client_term_id_filter : null,
				'include_empty'         => (bool) $include_empty,
				'per_page'              => $chunk,
				'paged'                 => $paged,
			) );

			$rows = (array) ( $response['users'] ?? array() );
			foreach ( $rows as $row ) {
				fputcsv( $out, map_usage_report_summary_row_to_csv( (array) $row ) );
			}

			$total_pages = max( 1, (int) ( $response['total_pages'] ?? 1 ) );
			$paged ++;
		} while ( $paged <= $total_pages );
	} else { // single
		fputcsv( $out, get_usage_report_summary_headers() );

		list( $single_user_id, $single_user_email ) = resolve_usage_report_single_filters( (string) $user_q );
		$response = \tamarind_reports\prepare_user_response( array(
			'view'                  => 'single',
			'from'                  => $from,
			'to'                    => $to,
			'client_term_id_filter' => $client_term_id_filter ? (int) $client_term_id_filter : null,
			'include_empty'         => true,
			'per_page'              => 1,
			'paged'                 => 1,
			'user_id'               => $single_user_id,
			'user_email'            => $single_user_email,
		) );

		$rows = (array) ( $response['users'] ?? array() );
		foreach ( $rows as $row ) {
			fputcsv( $out, map_usage_report_summary_row_to_csv( (array) $row ) );
		}
	}

	fclose( $out );
	exit;
}
