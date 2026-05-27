<?php
/**
 * Template Name: Usage Report (Clients/Users)
 * Description: Displays Clients/User usage metrics (page views, downloads, topics/geos, regulatory vs market) with optional filters.
 */

// Load usage-report helpers early (needed for CSV export too)
if ( defined( 'TAMARIND_TEMPLATES_PATH' ) ) {
	require_once TAMARIND_TEMPLATES_PATH . 'includes/usage-report.php';
}

// Read filters from GET.
$view             = isset( $_GET['view'] ) ? sanitize_key( $_GET['view'] ) : 'all'; // 'all' | 'single'
$today            = current_time( 'Y-m-d' );
$default_from     = date( 'Y-m-d', strtotime( $today . ' -90 days' ) );
$allowed_per_page = array( 10, 25, 50, 100 );
$parsed_filters   = \tamarind_reports\get_usage_report_request_filters(
	$_GET,
	array(
		'today'             => $today,
		'default_from'      => $default_from,
		'default_to'        => $today,
		'allowed_per_page'  => $allowed_per_page,
		'default_per_page'  => 25,
		'fallback_per_page' => 100,
	)
);
$from_input       = $parsed_filters['from_input'];
$to_input         = $parsed_filters['to_input'];
$client_q         = $parsed_filters['client_q'];
$include_empty    = $parsed_filters['include_empty'];
$user_id_raw      = $parsed_filters['user_id_raw'];
$user_email_raw   = $parsed_filters['user_email_raw'];
$user_q           = $parsed_filters['user_q'];
$run              = $parsed_filters['run'];
$per_page         = $parsed_filters['per_page'];
$report_page      = $parsed_filters['report_page'];
$detail_filters   = $parsed_filters['detail_filters'];
$subscription_plan_id = $detail_filters['subscription_plan_id'];
$content_type_id      = $detail_filters['content_type_id'];
$subcontent_type_id   = $detail_filters['subcontent_type_id'];
$has_download         = $detail_filters['has_download'];
$has_favourite        = $detail_filters['has_favourite'];
$from             = $parsed_filters['from'];
$to               = $parsed_filters['to'];
$range_days = max( 1, (int) ( ( strtotime( $to_input ) - strtotime( $from_input ) ) / DAY_IN_SECONDS ) + 1 );
$csv_warn   = ( $range_days > 180 );

list( $client_term_id_filter, $user_filter_id ) = \tamarind_reports\get_client_id( $client_q );

if ( $user_q !== '' ) {
	if ( ctype_digit( $user_q ) ) {
		$user_filter_id = (int) $user_q;
	} else {
		$u = get_user_by( 'email', $user_q );
		if ( $u ) {
			$user_filter_id = (int) $u->ID;
		}
	}
}

if ( isset( $_GET['export'] ) && $_GET['export'] === 'csv' ) {
	\tamarind_reports\export_csv(
		$view,
		$from_input,
		$to_input,
		$from,
		$to,
		$client_term_id_filter,
		$user_filter_id,
		$include_empty,
		$user_q,
		$subscription_plan_id,
		$content_type_id,
		$subcontent_type_id,
		$has_download,
		$has_favourite
	);
	exit;
}

get_header();
$default_params = array(
	'from_input'    => $from_input,
	'to_input'      => $to_input,
	'default_from'  => $default_from,
	'today'         => $today,
	'client_q'      => $client_q,
	'include_empty' => ! ! $include_empty,
	'view'          => $view,
	'per_page'      => $per_page,
	'report_page'   => $report_page,
	'subscription_plan_id' => $subscription_plan_id,
	'content_type_id'      => $content_type_id,
	'subcontent_type_id'   => $subcontent_type_id,
	'has_download'         => $has_download,
	'has_favourite'        => $has_favourite,
	'total'         => 0,
    'total_pages'   => 1,
);
?>
    <div class="wrap mt-20 tm-usage-report">
        <h1><?php _e('Usage Report', 'tamarind-reports');?></h1>
        <?php if(!current_user_can('read_tamarind_report')) : ?>
            <div class="notice notice-error">
                <h3><?php _e('You are not allowed to see this page', 'tamarind-reports');?></h3>
            </div>
        <?php else:?>
		<?php load_template( TM_REPORTS_TEMPLATE_PARTS_DIR . 'usage-report/nav.php', true, $default_params ); ?>
		<?php $params = array_merge( $default_params, array( 'allowed_per_page' => $allowed_per_page ) ); ?>
		<?php load_template( TM_REPORTS_TEMPLATE_PARTS_DIR . 'usage-report/filters.php', true, $params ); ?>

        <div class="usage-report-table-wrap widefat">
            <table class="usage-report-table widefat fixed striped">
                <thead>
				<?php load_template( TM_REPORTS_TEMPLATE_PARTS_DIR . 'usage-report/table-head-users.php', false ); ?>
				<?php load_template( TM_REPORTS_TEMPLATE_PARTS_DIR . 'usage-report/table-head-log.php', false ); ?>
                </thead>
                <tbody></tbody>
            </table>
        </div>
		<?php load_template( TM_REPORTS_TEMPLATE_PARTS_DIR . 'usage-report/pagination.php', true, $params ); ?>
        <?php endif;?>
    </div>


<?php get_footer();
