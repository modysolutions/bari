<?php
/**
 * Support functions
 *
 * @package Tamarind_UserArea
 * phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed
 */

namespace tamarind_userarea;

defined( 'ABSPATH' ) || exit;

/**
 * Get the slug from the option key.
 *
 * @param string $option_key The option key.
 *
 * @return string
 */
function get_slug_from_option_key( $option_key ) {
    $url_settings = get_field( 'url_settings', 'option' );
    foreach ( $url_settings as $url_setting ) {
        if ( $url_setting['url_key_slug'] === $option_key ) {
            return '/' . $url_setting['url_slug'];
        }
    }
    return '';
}


/**
 * Get the label from the option key.
 *
 * @param string $option_key The option key.
 *
 * @return string
 */
function get_label_from_option_key( $option_key ) {
    $url_settings = get_field( 'url_settings', 'option' );
    foreach ( $url_settings as $url_setting ) {
        if ( $url_setting['url_key_slug'] === $option_key ) {
            return $url_setting['url_label'];
        }
    }
    return '';
}

/**
 * Get the dashboard options.
 * 
 * @param string $rol The role.
 * @return array|bool
 */
function get_dashboard_options ( $rol ) {
    if ( ! $rol ) {
        $rol = wp_get_current_user()->roles[0];
    }
    switch ( $rol ) {
        case 'client':
            return get_field( 'dashboard_client', 'option' );
        case 'subscriber':
            return get_field( 'dashboard_subscriber', 'option' );
        case 'administrator':
            return get_field( 'dashboard_subscriber', 'option' );
        default:
    }
    return false;
}
