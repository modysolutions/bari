<?php
/**
 * Dynamic URL:  Downloads.
 *
 * @package Tamarind_UserArea
 * phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed
 */

namespace tamarind_userarea; 

?>

<header>
    <h2>Downloads</h2>
</header>

<?php
// Asegúrate de que WooCommerce está cargado.
if ( class_exists( 'WooCommerce' ) ) {
    $user_id = get_current_user_id();

    if ( $user_id ) {
        // Pasa las variables necesarias a la plantilla.
        wc_get_template(
            'myaccount/downloads.php'
        );
    } else {
        echo '<p>No estás logueado. Por favor, inicia sesión para ver tus pedidos.</p>';
    }
} else {
    echo '<p>WooCommerce no está activo.</p>';
}
