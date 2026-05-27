<?php
/**
 * Dynamic URL: Dashboard.
 *
 * @package Tamarind_UserArea
 *
 * phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed
 */

namespace tamarind_userarea;

defined( 'ABSPATH' ) || exit;

$dashboard_options = get_dashboard_options( $rol );
if ( $dashboard_options ) {
    ?>
    <div class="tm-layout-grid">
    <?php
    foreach ( $dashboard_options as $dashboard_option ) {
        $dashboard_link = get_option_menu_link( $dashboard_option['select_dashboard_option'] );
        $dashboard_label = get_label_from_option_key( $dashboard_option['select_dashboard_option'] );
        if ( $dashboard_link ) {
            $dashboard_link = esc_url( $dashboard_link['link'] );
            echo '<div class="tm-dashboard-content-option"><a href="' . esc_url( $dashboard_link ) . '">' . esc_html( $dashboard_label ) . '</a></div>';
        }
    }
    ?>
    </div>
    <?php
}

?>
