<?php extract($args);?>
<nav class="tamarind-report-pagination usage-pagination">
    <div class="usage-pagination-links">
		<?php $prev_url = add_query_arg( array_merge( $_GET, array( 'report_page' => $report_page - 1 ) ), get_permalink() ); ?>
        <a class="tm-btn btn-transparent prev-button"
	        <?php if ($report_page <= 1) echo 'disabled'; ?>
           href="<?php echo esc_url( $prev_url ); ?>" data-page="<?php echo esc_attr( $report_page - 1 ); ?>">
			<?php _e('Prev', 'tamarind-report');?>
        </a>

        <span class="tm-btn btn-default button-primary pagination-info" style="pointer-events:none; cursor:default;">
            <?php echo sprintf(
	            __( 'Page <span class="page">%d</span> of <span class="total">%d</span>', 'tamarind-reports' ),
	            esc_html( $report_page ),
	            esc_html( $total_pages)
            ); ?>
        </span>

		<?php $next_url = add_query_arg( array_merge( $_GET, array( 'report_page' => $report_page + 1 ) ), get_permalink() ); ?>
        <a class="tm-btn btn-transparent next-button"
	        <?php if ($report_page >= $total_pages) echo 'disabled'; ?>
           href="<?php echo esc_url( $next_url ); ?>" data-page="<?php echo esc_attr( $report_page + 1 ); ?>">
			<?php _e('Next', 'tamarind-report');?>
        </a>
    </div>
    <span class="usage-pagination-count">
        <?php echo sprintf( __( '%d results', 'tamarind-reports' ), esc_html( $total ) ); ?>
    </span>
</nav>