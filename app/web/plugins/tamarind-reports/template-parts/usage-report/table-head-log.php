<?php extract( $args );
$detailed_head_params = array(
	'cells' => array(
		'col-date'           => __( 'Date', 'tamarind-reports' ),
		'col-email-sm'       => __( 'Email', 'tamarind-reports' ),
		'col-user-status'    => __( 'User status', 'tamarind-reports' ),
		'col-client'         => __( 'Company', 'tamarind-reports' ),
		'col-client-status'  => __( 'Client status', 'tamarind-reports' ),
		'col-plan'           => __( 'Subscription plan', 'tamarind-reports' ),
		'col-client-users'   => __( 'N. users', 'tamarind-reports' ),
		'col-title'          => __( 'Post Title', 'tamarind-reports' ),
		'col-postid'         => __( 'Post ID', 'tamarind-reports' ),
		'col-event-type'     => __( 'Event Type', 'tamarind-reports' ),
		'col-download-url'   => __( 'Download URL', 'tamarind-reports' ),
		'col-download-type'  => __( 'Download Type', 'tamarind-reports' ),
		'col-favourites'     => __( 'Favourites', 'tamarind-reports' ),
		'col-ctypes'         => __( 'Content Types', 'tamarind-reports' ),
		'col-subctypes'      => __( 'Subcontent Types', 'tamarind-reports' ),
		'col-geos-d'         => __( 'Geographies', 'tamarind-reports' ),
		'col-topics'         => __( 'Topics', 'tamarind-reports' ),
		'col-author'         => __( 'Author', 'tamarind-reports' ),
		'col-pub-date'       => __( 'Publication Date', 'tamarind-reports' ),
		'col-last-login'     => __( 'Last login', 'tamarind-reports' ),
		'col-client-created' => __( 'Client creation date', 'tamarind-reports' ),
	)
);
?>
<tr id="tamarind-report-head-log" class="tamarind-report-head-log detail dynamic-block" style="display: none;">
	<?php if ( count( $detailed_head_params['cells'] ) > 0 ) : ?>
		<?php foreach ( $detailed_head_params['cells'] as $class_name => $cell ) : ?>
            <th class="<?php echo $class_name; ?>"><?php echo $cell; ?></th>
		<?php endforeach; ?>
	<?php endif; ?>
</tr>
