<?php
/**
 * Dynamic URL: View Order.
 *
 * @package Tamarind_UserArea
 * phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed
 * phpcs:disable Generic.Arrays.DisallowShortArraySyntax.Found
 */

namespace tamarind_userarea;

if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'process_order_num' ) ) {
    $num_order = isset( $_GET['num'] ) ? sanitize_text_field( wp_unslash( $_GET['num'] ) ) : '';
} else {
    $num_order = '';
    echo '<p>Invalid request. Nonce verification failed.</p>';
	return;
}

// Asegúrate de que WooCommerce está cargado.
if ( class_exists( 'WooCommerce' ) ) {
    $user_id = get_current_user_id();

    if ( $user_id && $num_order && wc_get_order( $num_order ) ) {
        // Get $order object from order ID.
        $tm_order = wc_get_order( $num_order );

        // Pasa las variables necesarias a la plantilla.
        wc_get_template(
            'myaccount/view-order.php',
            [
                'order'    => $tm_order,
                'order_id' => $num_order,
            ]
        );
    } else {
        echo '<p>Order ' . esc_attr( $num_order ) . ' does not exist.</p>';
    }
}
