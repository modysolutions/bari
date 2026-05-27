<?php
/**
 * Dynamic URL:  Orders.
 *
 * @package Tamarind_UserArea
 * phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed
 * phpcs:disable Generic.Arrays.DisallowShortArraySyntax.Found
 */

namespace tamarind_userarea;

?>

<header>
    <h2>Orders</h2>
</header>

<?php
// Asegúrate de que WooCommerce está cargado.
if ( class_exists( 'WooCommerce' ) ) {
    $user_id = get_current_user_id();

    if ( $user_id ) {
        $customer_orders = wc_get_orders(
            [
                'customer_id' => $user_id,
                'paginate'    => true,
                'limit'       => -1,
            ]
        );

        $has_orders = count( $customer_orders->orders ) > 0; // Determina si hay órdenes.
        $tm_current_user = wp_get_current_user(); // Usuario actual.

        // Pasa las variables necesarias a la plantilla.
        wc_get_template(
            'myaccount/orders.php',
            [
                'current_user'    => $tm_current_user,
                'has_orders'      => $has_orders,
                'customer_orders' => $customer_orders,
            ]
        );
    } else {
        echo '<p>No estás logueado. Por favor, inicia sesión para ver tus pedidos.</p>';
    }
} else {
    echo '<p>WooCommerce no está activo.</p>';
}
