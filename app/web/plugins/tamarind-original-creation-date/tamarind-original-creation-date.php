<?php
/**
 * Plugin Name:     Tamarind Original Creation Date
 * Plugin URI:      https://www.tamarind.com
 * Description:     Adds the original creation date to posts..
 * Author:          Omitsis & Matias Scheinkman
 * Author URI:      https://www.tamarind.com
 * Text Domain:     tamarind-original-creation-date
 * Domain Path:     /languages
 * Version:         1.5
 *
 * @package         Tamarind_Original_Creation_Date
 */

namespace tamarind_original_creation_date;

include_once plugin_dir_path( __FILE__ ) . 'acf-register.php';

if ( function_exists( 'get_field' ) || function_exists( 'update_field' )) {
    add_action( 'save_post', __NAMESPACE__ . '\save_original_creation_date' );
}

/**
 * Save the original creation date when the post is saved
 *
 * @param int $post_id The post ID.
 */
function save_original_creation_date( $post_id ) 
{
    // Verify that it is not an autosave.
    if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
        return;
    }

    $original_date = get_field( 'original_creation_date', $post_id );

    if ( empty( $original_date ) ) {
        $post = get_post( $post_id );
        $creation_date = $post->post_date;
        update_field( 'original_creation_date', $creation_date, $post_id );
    }
}
