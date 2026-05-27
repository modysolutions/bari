<?php
/**
 * Dynamic URL: Payment Methods.
 *
 * @package Tamarind_UserArea
 * phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed
 */

namespace tamarind_userarea; 

?>

<header>
    <h2>Payment methods</h2>
</header>

<?php
if ( class_exists( 'WooCommerce' ) ) {
    wc_get_template( 'myaccount/payment-methods.php' );
} else {
    echo '<p>WooCommerce no está activo.</p>';
}
