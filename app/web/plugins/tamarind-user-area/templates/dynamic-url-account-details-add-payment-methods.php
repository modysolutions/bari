<?php
/**
 * Dynamic URL: Add Payment Methods.
 *
 * @package Tamarind_UserArea
 * phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed
 */

namespace tamarind_userarea; 

?>

<header>
    <h2>Add payment method</h2>
</header>

<?php
if ( class_exists( 'WooCommerce' ) ) {
    \WC_Shortcode_My_Account::add_payment_method();

    // wc_get_template( 'myaccount/form-add-payment-method.php' );

} else {
    echo '<p>WooCommerce no está activo.</p>';
}
