<?php
$restricted_head = array();
if ( current_user_can( 'manage_options' ) ) {
	$restricted_head = array(
		'col-client' => __( 'Client', 'tamarind-reports' ),
	);
}
$detailed_head_params = array(
	'cells' => array_merge( $restricted_head, array(
		'col-email'          => __( 'Email', 'tamarind-reports' ),
		'col-num col-pv'     => __( 'Page views', 'tamarind-reports' ),
		'col-num col-dl'     => __( 'Downloads', 'tamarind-reports' ),
		'col-topics col-num' => __( 'Topics', 'tamarind-reports' ),
		'col-topics5'        => __( 'Top topics (5)', 'tamarind-reports' ),
		'col-geos'           => __( 'Geographies', 'tamarind-reports' ),
		'col-geos10'         => __( 'Top geographies (10)', 'tamarind-reports' ),
		'col-pct'            => __( 'Regulatory / Market', 'tamarind-reports' ),
	) )
); ?>

<tr id="tamarind-reports-head" class="tamarind-reports-head all single dynamic-block" style="display: none;">
	<?php if ( count( $detailed_head_params['cells'] ) > 0 ) : ?>
		<?php foreach ( $detailed_head_params['cells'] as $class_name => $cell ) : ?>
            <th class="<?php echo $class_name; ?>"><?php echo $cell; ?></th>
		<?php endforeach; ?>
	<?php endif; ?>
</tr>
