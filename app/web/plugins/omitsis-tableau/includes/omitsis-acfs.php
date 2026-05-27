<?php

/**
 * Handle common acfs
 *
 * @package Omitsis_Data_Tamarind_Api
 */

namespace omitsis\tableau_acfs;

defined( 'ABSPATH' ) || exit;

function set_acf_settings () {
	// if( function_exists('acf_add_options_page') ) {
	// 	acf_add_options_page(array(
	// 		'page_title'     => __('Data Tamarind API 3', 'tamarind'),
	// 		'menu_title'     => __('Data Tamarind API 3', 'tamarind'),
	// 		'menu_slug'     => 'data-tamarind-api-options',
	// 		'capability'     => 'edit_posts',
	// 		'redirect'     => false,
	// 		'position' => '1',
	// 	));
	// }
}

function init_acfs () {
	set_acf_settings();
}
