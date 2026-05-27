<?php
/**
 * ACF Options for Tamarind Notifications
 *
 * @package Tamarind_Notifications
 */

namespace tamarind_notifications;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Registers the ACF fields.
 */
if ( function_exists( 'acf_add_options_page' ) ) {
	add_action( 'acf/init', __NAMESPACE__ . '\tamarind_add_notifications_acf_field_groups' );
}

/**
 * Add ACF field groups for Tamarind Notifications:
 * User Profile and Post Meta (Relationship and bidirectional).
 */
function tamarind_add_notifications_acf_field_groups() {
	if ( function_exists( 'acf_add_local_field_group' ) ) {

		// Add field group for User Profile.
		acf_add_local_field_group(
			array(
				'key'          => 'group_user_notifications',
				'title'        => 'User Notifications',
				'fields'       => array(
					array(
						'key'                  => 'field_user_read_notifications',
						'label'                => 'Read notifications',
						'name'                 => 'user_read_notifications',
						'type'                 => 'relationship',
						'post_type'            => 'notifications',
						'filters'              => array( 'search' ),
						'return_format'        => 'id',
						'bidirectional'        => 1,
						'bidirectional_target' => array(
							0 => 'field_notifications_read_by_users',
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
				'key'          => 'group_post_notifications',
				'title'        => 'Post Notifications',
				'fields'       => array(
					array(
						'key'                  => 'field_notifications_read_by_users',
						'label'                => 'Notifications read by users',
						'name'                 => 'notifications_read_by_users',
						'type'                 => 'user',
						'role'                 => '',
						'multiple'             => 1,
						'return_format'        => 'array',
						'bidirectional'        => 1,
						'bidirectional_target' => array(
							0 => 'field_user_read_notifications',
						),
					),
				),
				'location'     => array(
					array(
						array(
							'param'    => 'post_type',
							'operator' => '==',
							'value'    => 'notifications',
						),
					),
				),
				'show_in_rest' => true,
			)
		);
	}
}
