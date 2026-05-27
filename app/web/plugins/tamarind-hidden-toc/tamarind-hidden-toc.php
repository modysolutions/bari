<?php
/**
 * Plugin Name: Tamarind - Hidden [toc] shortcode
 * Description: Replaces [toc] shortcode with nothing if "Tamarind - Table of Contents" is not active.
 * Author: Omitsis
 * Author URI:      https://www.omitsis.com
 * Text Domain: tamarind-hidden-toc
 * Domain Path: /languages
 * version: 1.0
 *
 * @package Tamarind_Hidden_Toc
 */

namespace tamarind_hidden_toc;

defined( 'ABSPATH' ) || exit;

/**
 * Replaces [toc] shortcode with nothing if "Tamarind - Table of Contents" is not active.
 */
add_filter( 'the_content', __NAMESPACE__ . '\replace_toc_shortcode' );

/**
 * Replaces [toc] shortcode with nothing if "Tamarind - Table of Contents" is not active.
 *
 * @param string $content The post content.
 * @return string The post content with [toc] shortcode replaced with nothing.
 */
function replace_toc_shortcode( $content ) 
{
    if ( ! function_exists( '\tamarind_toc\get_toc_and_new_content' ) ) {
        $content = str_replace( '[toc]', '', $content );
    }
    return $content;
}