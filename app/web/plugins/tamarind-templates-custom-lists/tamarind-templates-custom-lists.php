<?php
/**
 * Plugin Name: Tamarind Templates Custom Lists
 * Description: A plugin to manage Tamarind custom lists templates for special taxonomies and post template
 * Author:      Tamarind Intelligence
 * Author URI:  https://tamarindintelligence.com
 * Text Domain: tamarind-templates-custom-lists
 * Domain Path: /languages
 * version:     0.0.1
 *
 * @package tamarind_templates_custom_lists
 */

namespace tamarind_templates_custom_lists;

defined( 'ABSPATH' ) || die;

define( 'tamarind_templates_custom_lists_VERSION', '0.0.1' );
define( 'tamarind_templates_custom_lists_URL', plugin_dir_url( __FILE__ ) );
define( 'tamarind_templates_custom_lists_PATH', plugin_dir_path( __FILE__ ) );

require_once plugin_dir_path( __FILE__ ) . 'includes/functions.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/acf-options.php';
