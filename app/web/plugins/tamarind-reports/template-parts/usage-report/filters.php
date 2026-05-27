<?php extract( $args ); ?>
<?php
$subscription_plans = function_exists( '\tamarind_subscriptions\subscription_plan\get_all_plans' ) ?
	\tamarind_subscriptions\subscription_plan\get_all_plans() :
	array();
$content_types = get_terms( array(
	'taxonomy'   => 'content_types',
	'hide_empty' => false,
	'parent'     => 0,
) );
$subcontent_types = get_terms( array(
	'taxonomy'   => 'content_types',
	'hide_empty' => false,
) );
$subcontent_types = is_wp_error( $subcontent_types ) ? array() : array_filter( $subcontent_types, function ( $term ) {
	return isset( $term->parent ) && (int) $term->parent > 0;
} );
$content_types = is_wp_error( $content_types ) ? array() : $content_types;
?>
<form method="get" action="" class="tamarind-report-usage-form usage-form">
	<input type="hidden" name="page_id" value="<?php echo esc_attr( get_the_ID() ); ?>"/>
	<input type="hidden" name="view" value="<?php echo esc_attr( $_GET['view'] ?? 'all' ); ?>" id="tm-reports-view"/>
	<input type="hidden" name="run" value="1"/>
	<label>From: <input type="date" class="tamarind-report-filter" name="from" value="<?php echo esc_attr( $from_input ); ?>"/></label>
	<label>To: <input type="date" class="tamarind-report-filter" name="to" value="<?php echo esc_attr( $to_input ); ?>"/></label>
    <?php if(current_user_can('manage_options')) : ?>
	    <label class="dynamic-label all detail" style="display: none;">
            Client:
            <select class="tamarind-report-filter" name="client" id="tm-reports-client" value="<?php echo $client_q; ?>">
                <option value=""><?php _e('Select client', 'tamarind-report');?></option>
                <?php foreach(\tamarind_reports\get_clients() as $client) :
                    $client = (object)$client;?>
                    <option value="<?php echo $client->id; ?>" <?php selected($client_q, $client->id); ?>>
                        <?php echo $client->name; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
    <?php else :
	    $client_name = '';
	    $client_term_id = get_user_meta( get_current_user_id(), 'clientes', true );
        ?>
        <input type="hidden" name="client" value="<?php echo $client_term_id; ?>" />
    <?php endif; ?>
    <label id="tm-reports-user-email" class="dynamic-label single detail" style="display: none;">
        User email:
        <input class="tamarind-report-filter" type="text" name="user_email" value="<?php echo esc_attr( $_GET['user_email'] ?? '' ); ?>"
               id="tm-reports-user-search" placeholder="john@company.com" autocomplete="off" list="tm-reports-user-list"/>
        <datalist id="tm-reports-user-list"></datalist>
        <input type="hidden" name="user_id" value="<?php echo array_key_exists('user_id', $_GET) ? esc_attr( $_GET['user_id'] ) : ''; ?>" id="tm-reports-user-id" />
	    </label>
	<label class="dynamic-label detail" style="display: none;">
		Subscription plan:
		<select class="tamarind-report-filter" name="subscription_plan">
			<option value=""><?php _e( 'All plans', 'tamarind-report' ); ?></option>
			<?php foreach ( $subscription_plans as $plan ) : ?>
				<option value="<?php echo esc_attr( $plan['plan_id'] ?? '' ); ?>" <?php selected( (int) ( $subscription_plan_id ?? 0 ), (int) ( $plan['plan_id'] ?? 0 ) ); ?>>
					<?php echo esc_html( $plan['plan_name'] ?? '' ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</label>
	<label class="dynamic-label detail" style="display: none;">
		Content type:
		<select class="tamarind-report-filter" name="content_type">
			<option value=""><?php _e( 'All content types', 'tamarind-report' ); ?></option>
			<?php foreach ( $content_types as $term ) : ?>
				<option value="<?php echo esc_attr( $term->term_id ); ?>" <?php selected( (int) ( $content_type_id ?? 0 ), (int) $term->term_id ); ?>>
					<?php echo esc_html( $term->name ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</label>
	<label class="dynamic-label detail" style="display: none;">
		Sub content type:
		<select class="tamarind-report-filter" name="subcontent_type">
			<option value=""><?php _e( 'All sub content types', 'tamarind-report' ); ?></option>
			<?php foreach ( $subcontent_types as $term ) : ?>
				<option value="<?php echo esc_attr( $term->term_id ); ?>" <?php selected( (int) ( $subcontent_type_id ?? 0 ), (int) $term->term_id ); ?>>
					<?php echo esc_html( $term->name ); ?>
				</option>
			<?php endforeach; ?>
		</select>
	</label>
	<label class="dynamic-label detail" style="display: none;">
		Download:
		<select class="tamarind-report-filter" name="has_download">
			<option value=""><?php _e( 'All', 'tamarind-report' ); ?></option>
			<option value="yes" <?php selected( (string) ( $has_download ?? '' ), 'yes' ); ?>><?php _e( 'Yes', 'tamarind-report' ); ?></option>
			<option value="no" <?php selected( (string) ( $has_download ?? '' ), 'no' ); ?>><?php _e( 'No', 'tamarind-report' ); ?></option>
		</select>
	</label>
	<label class="dynamic-label detail" style="display: none;">
		Favourites:
		<select class="tamarind-report-filter" name="has_favourite">
			<option value=""><?php _e( 'All', 'tamarind-report' ); ?></option>
			<option value="yes" <?php selected( (string) ( $has_favourite ?? '' ), 'yes' ); ?>><?php _e( 'Yes', 'tamarind-report' ); ?></option>
			<option value="no" <?php selected( (string) ( $has_favourite ?? '' ), 'no' ); ?>><?php _e( 'No', 'tamarind-report' ); ?></option>
		</select>
	</label>
	<label class="dynamic-label all detail">Per page:
		<select name="per_page" class="tamarind-report-filter">
			<?php foreach ( $allowed_per_page as $opt ) : ?>
				<option value="<?php echo esc_attr( $opt ); ?>" <?php selected( $per_page, $opt ); ?>><?php echo esc_html( $opt ); ?></option>
			<?php endforeach; ?>
		</select>
	</label>
	<label class="dynamic-label all detail">Page:
        <?php $report_page = array_key_exists('report_page', $_GET) ? esc_html($_GET['report_page']) : 1;?>
		<select class="tamarind-report-filter" name="report_page"
                value="<?php echo $report_page;?>">
            <option value="<?php echo $report_page;?>">
	            <?php echo $report_page;?>
            </option>
        </select>
	</label>
    <div>
        <label class="full-row dynamic-label all detail" style="display: none;">
            <input type="checkbox" name="include_empty" value="1" <?php checked( $include_empty ); ?> class="tamarind-report-filter" />
            Include users with no activity
        </label>
    </div>
	<div class="actions">
		<?php
        $arguments = array_merge( array('export' => 'csv'), array_map('esc_attr', $_GET ) );
		$export_url = add_query_arg( $arguments, get_permalink() );
		?>
		<a class="tm-btn btn-default mt-10 download-report" href="<?php echo esc_url( $export_url ); ?>">
            <?php _e('Download CSV', 'tamarind-report');?>
        </a>
	</div>
</form>
