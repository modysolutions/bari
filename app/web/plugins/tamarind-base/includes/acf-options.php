<?php
/**
 * ACF Options for Tamarind Base
 *
 * @package Tamarind_Base
 */

namespace tamarind_base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Tamarind Base menu
 */
if ( function_exists( 'acf_add_options_page' ) ) {
	add_filter( 'acf/init', __NAMESPACE__ . '\register_base_menu', 9 );
	add_filter( 'acf/init', __NAMESPACE__ . '\register_base_acf_fields' );
	add_action( 'admin_head', __NAMESPACE__ . '\custom_admin_css' );
	add_action( 'admin_menu', __NAMESPACE__ . '\remove_tamarind_base_submenu', 999 );
}

/**
 * Register the Tamarind Base menu and submenus.
 *
 * @return void
 */
function register_base_menu(): void {
	add_menu_page(
		__('Tamarind', TM_LANGUAGE_DOMAIN),
		__('Tamarind', TM_LANGUAGE_DOMAIN),
		'manage_options',
		'tamarind-base',
		null,
		plugins_url( 'tamarind-base/assets/images/tamarind-logo.png' ),
		3
	);

	acf_add_options_sub_page(
		array(
			'page_title'  => __('Dynamic URLs', TM_LANGUAGE_DOMAIN),
			'menu_title'  => __('Dynamic URLs', TM_LANGUAGE_DOMAIN),
			'menu_slug'   => 'tm-url-settings',
			'capability'  => 'manage_options',
			'parent_slug' => 'tamarind-base',
			'redirect'    => false,
			'position'    => 2,
		)
	);

	acf_add_options_sub_page(
		array(
			'page_title'  => __('Single Page Settings', TM_LANGUAGE_DOMAIN),
			'menu_title'  => __('Single Page Settings', TM_LANGUAGE_DOMAIN),
			'menu_slug'   => 'single-page-settings',
			'capability'  => 'manage_options',
			'parent_slug' => 'tamarind-base',
			'redirect'    => false,
			'position'    => 3,
			'icon_url'    => 'dashicons-list-view',
		)
	);

	acf_add_options_sub_page(
		array(
			'page_title'  => __('Widgets', TM_LANGUAGE_DOMAIN),
			'menu_title'  => __('Widgets', TM_LANGUAGE_DOMAIN),
			'menu_slug'   => 'tm-widgets',
			'capability'  => 'manage_options',
			'parent_slug' => 'tamarind-base',
			'redirect'    => false,
			'position'    => 4,
		)
	);
}

/**
 * Removes the default submenu that duplicates the top-level "Tamarind Base" menu.
 */
function remove_tamarind_base_submenu(): void {
	remove_submenu_page( 'tamarind-base', 'tamarind-base' );
}

/**
 * Custom CSS for the Tamarind Base admin menu.
 *
 * @return void
 */
function custom_admin_css(): void {
	echo '<style>
        .toplevel_page_tamarind-base .dashicons-before img {
            width: 20px;
            padding: 7px 0 0 !important;
        }
    </style>';
}

/**
 * Register ACF fields for Tamarind Base.
 *
 * @return void
 */
function register_base_acf_fields(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_tm_url_settings',
			'title'    => __('URL Settings', TM_LANGUAGE_DOMAIN),
			'fields'   => array(), // Empty fields for now.
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'tm-url-settings',
					),
				),
			),
			'style'    => 'default',
		)
	);
}
