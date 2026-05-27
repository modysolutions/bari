<?php
/**
 * Dynamic URL: Address.
 *
 * @package Tamarind_UserArea
 * phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed
 */

namespace tamarind_userarea; 

?>

<header>
    <h2>Address</h2>
</header>

<?php
if ( class_exists( 'WooCommerce' ) ) {
    $user_id = get_current_user_id();
    if ( $user_id ) {
        
		$load_address = tm_get_load_address();
		$address      = tm_get_address( $load_address );

        // Pasa las variables necesarias a la plantilla.
        wc_get_template(
			'myaccount/form-edit-address.php',
			array(
				'load_address' => $load_address,
				'address'      => apply_filters( 'woocommerce_address_to_edit', $address, $load_address ),
			)
		);
    } else {
        echo '<p>No estás logueado. Por favor, inicia sesión para ver tu dirección.</p>';
    }
} else {
    echo '<p>WooCommerce no está activo.</p>';
}
