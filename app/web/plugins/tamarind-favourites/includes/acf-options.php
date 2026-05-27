<?php
/**
 * ACF Options for Tamarind Favourites
 *
 * @package Tamarind_Favourites
 */

namespace tamarind_favourites;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the ACF Options fields.
 */
if ( function_exists( 'acf_add_options_page' ) ) {
	add_filter( 'acf/init', __NAMESPACE__ . '\register_acf_field_options' );
	add_action( 'acf/options_page/save', __NAMESPACE__ . '\flush_rewrite_rules_options_save', 10, 2 );
	add_action( 'acf/init', __NAMESPACE__ . '\tamarind_add_favourites_acf_field_groups' );
}

/**
 * Flush rewrite rules on save options page.
 *
 * @param int    $post_id   The post ID.
 * @param string $menu_slug The menu slug.
 */
function flush_rewrite_rules_options_save( $post_id, $menu_slug ) {
	if ( 'tm-favourites-settings' === $menu_slug ) {
		delete_option( 'rewrite_rules' );
	}
}

/**
 * Set the options for the select field.
 *
 * @return array
 */
function set_select_url_options() {
	$choices = array(
		'my_favourites' => 'My favourites',
	);
	$choices = array( '' => 'Select Key' ) + $choices;
	return $choices;
}


/**
 * Register the ACF fields for the URL repeater.
 *
 * @return array
 */
function register_url_repeater_settings() {
	$field_url_key_slug = array(
		'key'     => 'tm_favourites_key_url',
		'label'   => __( 'Key', 'tm-favourites' ),
		'name'    => 'favourites_url_key_slug',
		'type'    => 'select',
		'choices' => set_select_url_options(),
		'wrapper' => array(
			'width' => '33',
		),
	);

	$field_url_slug = array(
		'key'     => 'tm_favourites_url_value',
		'label'   => __( 'URL', 'tm-favourites' ),
		'name'    => 'favourites_url_slug',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '33',
		),
	);

	$field_url_label = array(
		'key'     => 'tm_favourites_url_label',
		'label'   => __( 'Label', 'tm-favourites' ),
		'name'    => 'favourites_url_label',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '33',
		),
	);

	$acf_code_repeater = array(
		'key'          => 'tm_favourites_url_settings',
		'label'        => __( 'URL', 'tm-favourites' ),
		'name'         => 'favourites_url_settings',
		'type'         => 'repeater',
		'parent'       => 'group_tm_url_settings', // Associate with the base field group.
		'layout'       => 'block',
		'sub_fields'   => array(
			$field_url_key_slug,
			$field_url_slug,
			$field_url_label,
		),
		'button_label' => 'Añadir Opción',
	);
	return $acf_code_repeater;
}

/**
 * Registers the ACF fields for Options Page.
 *
 * @return void
 */
function register_acf_field_options() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	// Tab "Favourites".
	$favourites_tab = array(
		'key'       => 'field_tm_favourites_tab',
		'label'     => 'Favourites',
		'name'      => 'tm_favourites_tab',
		'type'      => 'tab',
		'placement' => 'left',
		'parent'    => 'group_tm_url_settings', // Associate with the base field group.
	);

	$message_field_url_favourites = array(
		'key'     => 'field_tamarind_message_url_favourites',
		'name'    => 'message_url_favourites',
		'type'    => 'message',
		'message' => __( '<h3>Config dynamic URL</h3>Define URL for <b>Favourites page</b>.', 'tm-favourites' ),
		'parent'  => 'group_tm_url_settings', // Associate with the base field group.
	);

	$urls_settings = register_url_repeater_settings();

	// Register the Favourites Tab fields for URL Settings.
	acf_add_local_field( $favourites_tab );
	acf_add_local_field( $message_field_url_favourites );
	acf_add_local_field( $urls_settings );
}

/**
 * Add ACF field groups for Tamarind Favourites:
 * User Profile and Post Meta (Relationship and bidirectional).
 */
function tamarind_add_favourites_acf_field_groups() {
	if ( function_exists( 'acf_add_local_field_group' ) ) {

		// Add field group for User Profile.
		acf_add_local_field_group(
			array(
				'key'          => 'group_user_favourites',
				'title'        => 'User Favourites',
				'fields'       => array(
					array(
						'key'                  => 'field_user_favourite_posts',
						'label'                => 'Favourite Posts',
						'name'                 => 'user_favourite_posts',
						'type'                 => 'relationship',
						'post_type'            => '',
						'filters'              => array( 'search', 'post_type' ),
						'elements'             => array( 'featured_image' ),
						'return_format'        => 'id',
						'bidirectional'        => 1,
						'bidirectional_target' => array(
							0 => 'field_favourited_by_users',
						),
					),
				),
				'location'     => array(
					array(
						array(
							'param'    => 'user_form',
							'operator' => '==',
							'value'    => 'all',
						),
					),
				),
				'show_in_rest' => true,
			)
		);

		// Add field group for Post Meta.
		acf_add_local_field_group(
			array(
				'key'          => 'group_post_favourites',
				'title'        => 'Post Favourites',
				'fields'       => array(
					array(
						'key'                  => 'field_favourited_by_users',
						'label'                => 'Favourited By Users',
						'name'                 => 'favourited_by_users',
						'type'                 => 'user',
						'role'                 => '',
						'multiple'             => 1,
						'return_format'        => 'array',
						'bidirectional'        => 1,
						'bidirectional_target' => array(
							0 => 'field_user_favourite_posts',
						),
					),
				),
				'location'     => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'post',
						),
					),
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'regulatory_alert',
						),
					),
				),
				'show_in_rest' => true,
			)
		);
	}
}
