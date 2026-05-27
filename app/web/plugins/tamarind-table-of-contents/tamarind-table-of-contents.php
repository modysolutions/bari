<?php
/**
 * Plugin Name: Tamarind - Table of Contents
 * Description: Replaces [toc] shortcode with dynamic HTML table of contents in all posts.
 * Author: Omitsis & Matias Scheinkman
 * Author URI:      https://www.omitsis.com
 * Text Domain: tamarind-toc
 * Domain Path: /languages
 * version: 1.1.0
 *
 * @package Tamarind_Table_of_Contents
 */

namespace tamarind_toc;

/**
 * Version number of the omitsis base plugin
 */
const VERSION = '1.1.0';

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue the styles and scripts
 */
function tamarind_toc_enqueue_scripts() {
	$plugin_url = plugin_dir_url( __FILE__ );

	wp_enqueue_style( 'tm-toc', $plugin_url . 'assets/css/toc.css', array(), VERSION );
	wp_enqueue_script( 'tm-toc', $plugin_url . 'assets/js/toc.js', array(), VERSION, true );
}
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\tamarind_toc_enqueue_scripts' );

require_once __DIR__ . '/includes/acf-options.php';

/**
 * Get the table of contents and new post content with IDs added to headings.
 *
 * @param string $content The post content.
 * @return array An array containing the table of contents and new post content.
 */
function get_toc_and_new_content( $content )
{
    $toc_void = true;
    $toc = '<div class="dynamic-toc"><p><strong>Contents</strong></p><ol class="toc">';
    $content_with_toc_ids = preg_replace_callback( '/<h([1-3])[^>]*>(.*?)<\/h\1>/s', function( $match ) use ( &$toc, &$toc_void ) {
        $heading_text = strip_tags( $match[2] );
        $id = sanitize_title( $heading_text );
        $toc .= '<li><a href="#' . $id . '">' . $heading_text . '</a></li>';
        $toc_void = false;
        return '<h' . $match[1] . ' id="' . $id . '">' . $match[2] . '</h' . $match[1] . '>';
    }, $content);

    $toc .= '</ol></div>';

    if ($toc_void) {
        $toc = '';
    }

    return array( 'toc' => $toc, 'content' => $content_with_toc_ids );
}

/**
 * Update the post content with the new table of contents and save the TOC to an ACF field.
 *
 * @param int $post_id The post ID.
 * @param array $data An array containing the table of contents and new post content.
 */
function update_toc_in_post( $post_id, $data ) {
    // Para evitar un bucle infinito, removemos el hook save_post antes de actualizar el post.
    remove_action('save_post', __NAMESPACE__ . '\replace_toc_on_save_post');
    wp_update_post( array( 'ID' => $post_id, 'post_content' => $data['content'] ) );
    add_action('save_post', __NAMESPACE__ . '\replace_toc_on_save_post');
    update_field('res_tocs', $data['toc'], $post_id);
}

/**
 * Replace the [toc] shortcode with the dynamic table of contents on post save.
 *
 * @param int $post_id The post ID.
 * @return bool True if the TOC was replaced, false otherwise.
 */
function replace_toc_on_save_post( $post_id ) {
    if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id  ) ) {
        return;
    }

    $post    = get_post($post_id);
    $content = $post->post_content;

    if ( has_shortcode( $content, 'toc') ) {
        $data = get_toc_and_new_content( $content );
        update_toc_in_post ( $post_id, $data );
        return true;
    }
    return false; 
}
add_action( 'save_post', __NAMESPACE__ . '\replace_toc_on_save_post' );

/**
 * Add the [toc] shortcode to display the dynamic table of contents. [toc style="default"] 
 */
add_shortcode( 'toc', 'toc_shortocode_function' );
function toc_shortocode_function( $atts ) {
	return "style = {$atts['style']}";
}

/**
 * Display the dynamic table of contents when the [toc] shortcode is used.
 */
function display_dynamic_toc( $content ) {
    $post    = get_post();

	if ( $post && isset( $post->ID ) ) {
		$toc_html = get_field('res_tocs', $post->ID);
		if ( has_shortcode( $content, 'toc') ) {
			$content = str_replace( '[toc]', $toc_html, $content );
		}  
	}       
    return $content;
}
add_filter ('the_content', __NAMESPACE__ . '\display_dynamic_toc');

/**
 * Delete links in the table of contents.
 *
 * @param string $toc The table of contents HTML.
 * @return string The table of contents HTML without links.
 */
function delete_links_in_toc( $toc ) {
    $dom = new \DOMDocument();
    $dom->loadHTML( $toc );
    $xpath = new \DOMXPath($dom);
    $links = $xpath->query('//a');
    foreach ($links as $link) {
        $link->parentNode->replaceChild($dom->createTextNode($link->nodeValue), $link);
    }
    return $dom->saveHTML();
}

/**
 * Print the widget table of contents for non-subscribers.
 *
 * @return bool True if the widget was printed, false otherwise.
 */
function printWidgetTOC() {
	if (get_field('show_toc')) { 
        ?>
		<section class="widget new-widget widget-toc-content widget-benefits-content">
			<div class="widget-wrap">
				<h3 class="widget-title">
					<img src="<?php echo get_stylesheet_directory_uri(); ?>/images/new-design/icon-toc.svg" alt="">
					Table of contents
				</h3>
				<div class="widget-content">
					<?php 
					$toc = get_field('res_tocs');
                    if ($toc) {
                        echo delete_links_in_toc( $toc );
                    }
					?>
				</div>
			</div>
		</section>
		<?php 
		return true;
	}
	return false;
}
