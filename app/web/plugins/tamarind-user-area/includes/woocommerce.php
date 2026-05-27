<?php
/**
 * Woocommerce functions
 *
 * @package Tamarind_UserArea
 * phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed
 */

namespace tamarind_userarea;

defined( 'ABSPATH' ) || exit;

/**
 * Get the address to load.
 *
 * @return string
 */
function tm_get_load_address() {
    return sanitize_key( isset( $_GET['address'] ) ? $_GET['address'] : false );
}

/**
 * Get the address.
 *
 * @param string $load_address The address to load.
 * @return array
 */
function tm_get_address ( $load_address ) {

    // Código de WooCommerce para obtener la dirección.
    // TODO: public static function edit_address.
    $country      = get_user_meta( get_current_user_id(), $load_address . '_country', true );

    if ( ! $country ) {
        $country = WC()->countries->get_base_country();
    }

    if ( 'billing' === $load_address ) {
        $allowed_countries = WC()->countries->get_allowed_countries();

        if ( ! array_key_exists( $country, $allowed_countries ) ) {
            $country = current( array_keys( $allowed_countries ) );
        }
    }

    if ( 'shipping' === $load_address ) {
        $allowed_countries = WC()->countries->get_shipping_countries();

        if ( ! array_key_exists( $country, $allowed_countries ) ) {
            $country = current( array_keys( $allowed_countries ) );
        }
    }

    $address = WC()->countries->get_address_fields( $country, $load_address . '_' );

    // Enqueue scripts.
    wp_enqueue_script( 'wc-country-select' );
    wp_enqueue_script( 'wc-address-i18n' );

    $current_user = wp_get_current_user();

    // Prepare values.
    foreach ( $address as $key => $field ) {

        $value = get_user_meta( get_current_user_id(), $key, true );

        if ( ! $value ) {
            switch ( $key ) {
                case 'billing_email':
                case 'shipping_email':
                    $value = $current_user->user_email;
                    break;
            }
        }

        $address[ $key ]['value'] = apply_filters( 'woocommerce_my_account_edit_address_field_value', $value, $key, $load_address );
    }

    return $address;
}

/**
 * Save the address.
 *
 * @param int    $user_id      The user ID.
 * @param string $address_type The address type. Billing or Shipping.
 */
function tm_save_address( $user_id, $address_type ) {

    if ( ! wp_verify_nonce( $_POST['woocommerce-edit-address-nonce'], 'woocommerce-edit_address' ) ) {
		wp_die( __( 'Action failed. Please refresh the page and retry.', 'woocommerce' ) );
	}

    $address = array(
        'first_name' => $_POST[ $address_type . '_first_name' ],
        'last_name'  => $_POST[ $address_type . '_last_name' ],
        'company'    => $_POST[ $address_type . '_company' ],
        'email'      => $_POST[ $address_type . '_email' ],
        'phone'      => $_POST[ $address_type . '_phone' ],
        'address_1'  => $_POST[ $address_type . '_address_1' ],
        'address_2'  => $_POST[ $address_type . '_address_2' ],
        'city'       => $_POST[ $address_type . '_city' ],
        'postcode'   => $_POST[ $address_type . '_postcode' ],
        'country'    => $_POST[ $address_type . '_country' ],
        'state'      => $_POST[ $address_type . '_state' ],
    );

    array_walk(
        $address,
        function( $value, $key ) use ( $user_id, $address_type ) {
            update_user_meta( $user_id, $address_type . '_' . $key, $value );
        }
    );

    echo 'Address saved!!!!<br /><br />';

    // TODO: utilizar el wc_add_notice para mostrar mensajes de error o éxito.
	wc_add_notice( __( 'Address changed successfully.', 'woocommerce' ) );
}
