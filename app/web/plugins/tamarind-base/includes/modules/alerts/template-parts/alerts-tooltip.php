<?php
/**
 * Template Part: Alerts Tooltip for Home Module
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\alerts;

defined( 'ABSPATH' ) || exit;


if ( get_field( 'show_alerts_tooltip', 'options' ) ) :
	$tooltip_text = get_field( 'alerts_tooltip', 'options' );
	$new_format_tip = get_field( 'alerts_new_format_tip', 'options' );
	if ( ! empty( $tooltip_text ) && ! empty( $new_format_tip ) ) :
		?>
		<div class="alerts-tooltip-wrapper">
			<span class='alerts-tooltip'><?php echo esc_html( $tooltip_text ); ?></span>
			<div class='alerts-tooltip-tip'><?php echo wp_kses_post( $new_format_tip ); ?></div>
		</div>
		<?php
	endif;
endif;
