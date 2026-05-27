<?php
/**
 * Plugin Name: Tamarind Company News
 * Description: Plugin to manage Company News.
 * Version:     1.1.0
 * Author:      Omitsis
 * Author URI:  https://www.omitsis.com
 * Text Domain: tm-company-news
 * Domain Path: /languages
 *
 * @package     Tamarind_Company_News
 */

namespace tamarind_company_news;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$version = '1.1.0';
if(file_exists(dirname(__FILE__) . '/dist/css/tamarind-company-news.min.css')) {
    $version = filemtime(dirname(__FILE__) . '/dist/css/tamarind-company-news.min.css');
}

define('TM_CN_VERSION', $version);
const PLUGIN_PATH = __DIR__;

// Additional files.
require_once PLUGIN_PATH . '/includes/cpt.php';
require_once PLUGIN_PATH . '/includes/acf-options.php';

/**
 * Enqueue the plugin styles.
 */
function company_news_enqueue_styles() {
	$plugin_url = plugin_dir_url( __FILE__ );

	wp_enqueue_style( 'tm-company-news', $plugin_url . 'dist/css/tamarind-company-news.min.css', array(),
        TM_CN_VERSION );
}
// It is commented out because the JS/CSS of this plugin are not currently used.
//add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\company_news_enqueue_styles' );
