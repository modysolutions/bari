<?php
/**
 * Plugin Name: Tamarind - PDFs
 * Description: A plugin to manage PDFs in the Tamarind websites
 * Author: Omitsis
 * Author URI:      https://www.omitsis.com
 * Text Domain: tamarind-pdfs
 * Domain Path: /languages
 * version: 1.1
 *
 * @package Tamarind_Pdfs
 */

namespace tamarind_pdfs;

/**
 * Version number of the plugin to manage PDFs in the Tamarind websites
 */
const VERSION = '1.0';

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/includes/acf-options.php';
require_once __DIR__ . '/includes/backend.php';
require_once __DIR__ . '/includes/frontend.php';
require_once __DIR__ . '/includes/generate.php';
require_once __DIR__ . '/includes/render.php';
require_once __DIR__ . '/includes/cli.php';
require_once __DIR__ . '/includes/class-encrypt.php';

add_filter( 'theme_page_templates', __NAMESPACE__ . '\add_theme_page_templates', 10, 4 );
add_filter( 'template_include', __NAMESPACE__ . '\add_template_include' );

function add_theme_page_templates( $post_templates, $wp_theme, $post, $post_type ) {
    $post_templates['page-download.php'] = 'Download PDF Template';
    return $post_templates;
}

function add_template_include ($template) {
    if ( is_page() ) {
        $page_template = get_page_template_slug();
        if ( $page_template === 'page-download.php' ) {
            $plugin_template = plugin_dir_path( __FILE__ ) . 'templates/page-download.php';
            if ( file_exists( $plugin_template ) ) {
                return $plugin_template;
            }
        }
    }
    return $template;
}
