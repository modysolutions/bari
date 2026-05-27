<?php
/**
 * ACFs for Tamarind Access Module.
 *
 * @package tamarind_subscriptions
 */

namespace tamarind_subscriptions\access;

use function tamarind_subscriptions\subscription_plan\{get_all_plans};

defined( 'ABSPATH' ) || exit;

add_filter( 'acf/init', __NAMESPACE__ . '\register_acf_fields' );
add_filter( 'acf/load_field/key=field_access_users_groups_allowed', __NAMESPACE__ . '\populate_select_subscription_plan_field' );

/**
 * Register ACF fields for Clients.
 *
 * @return void
 */
function register_acf_fields(): void {
	if ( ! function_exists( 'acf_add_options_sub_page' ) || ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$open_content_bool = array(
		'key'           => 'field_access_open_content_bool',
		'label'         => __( 'Open content', 'tamarind-subscriptions' ),
		'name'          => 'open',
		'type'          => 'true_false',
		'default_value' => 0,
		'ui'            => 1,
	);

	$allow_user_bool = array(
		'key'               => 'field_access_allow_user_bool',
		'label'             => __( 'Allow to specific user', 'tamarind-subscriptions' ),
		'name'              => 'allow_to_specific_user',
		'type'              => 'true_false',
		'default_value'     => 0,
		'ui'                => 1,
		'conditional_logic' => array(
			array(
				array(
					'field'    => 'field_access_open_content_bool',
					'operator' => '==',
					'value'    => '0',
				),
			),
		),
	);

	$users_allowed = array(
		'key'               => 'field_access_users_allowed',
		'label'             => __( 'Users allowed', 'tamarind-subscriptions' ),
		'name'              => 'users',
		'type'              => 'user',
		'multiple'          => 1,
		'allow_null'        => 0,
		'role'              => '',
		'return_format'     => 'id',
		'ui'                => 1,
		'conditional_logic' => array(
			array(
				array(
					'field'    => 'field_access_allow_user_bool',
					'operator' => '==',
					'value'    => '1',
				),
				array(
					'field'    => 'field_access_open_content_bool',
					'operator' => '==',
					'value'    => '0',
				),
			),
		),
	);

	$allow_group_bool = array(
		'key'               => 'field_access_allow_user_group_bool',
		'label'             => __( 'Allow to specific subscription plan', 'tamarind-subscriptions' ),
		'name'              => 'allow_to_specific_user_group',
		'type'              => 'true_false',
		'default_value'     => 0,
		'ui'                => 1,
		'conditional_logic' => array(
			array(
				array(
					'field'    => 'field_access_open_content_bool',
					'operator' => '==',
					'value'    => '0',
				),
			),
		),
	);

	$groups_allowed = array(
		'key'               => 'field_access_users_groups_allowed',
		'label'             => __( 'Subscription Plan allowed', 'tamarind-subscriptions' ),
		'name'              => 'user_groups',
		'type'              => 'select',
		'choices'           => array(), // Populate dynamically with subscription plans.
		'allow_null'        => 0,
		'multiple'          => 1,
		'ui'                => 1,
		'return_format'     => 'value',
		'conditional_logic' => array(
			array(
				array(
					'field'    => 'field_access_allow_user_group_bool',
					'operator' => '==',
					'value'    => '1',
				),
				array(
					'field'    => 'field_access_open_content_bool',
					'operator' => '==',
					'value'    => '0',
				),
			),
		),
	);

	acf_add_local_field_group(
		array(
			'key'          => 'group_access_posts_fields',
			'title'        => __( 'Permissions access', 'tamarind-subscriptions' ),
			'fields'       => array(
				$open_content_bool,
				$allow_user_bool,
				$users_allowed,
				$allow_group_bool,
				$groups_allowed,
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
			'menu_order'   => 0,
			'position'     => 'side',
			'style'        => 'default',
			'show_in_rest' => 0,
		)
	);

	$menu_slug = 'subscription-plans-settings';

	acf_add_options_sub_page(
		array(
			'page_title'  => 'Settings',
			'menu_title'  => 'Settings',
			'parent_slug' => 'edit.php?post_type=subscription-plan',
			'menu_slug'   => $menu_slug,
			'capability'  => 'manage_options',
			'redirect'    => false,
		)
	);

	$content_types_open_ids = array(
		'key'           => 'field_access_open_content_types',
		'label'         => 'Content Types',
		'name'          => 'open_all_content_type',
		'type'          => 'taxonomy',
		'instructions'  => 'Allows access to all the content of the content type selected.',
		'taxonomy'      => 'content_types',
		'return_format' => 'id',
		'field_type'    => 'checkbox',
		'multiple'      => 0,
		'allow_null'    => 0,
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_open_all_content_fields',
			'title'    => 'Open all content',
			'fields'   => array(
				$content_types_open_ids,
			),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => $menu_slug,
					),
				),
			),
			'style'    => 'default',
		)
	);
}

/**
 * Populate select form field with subscription plans.
 *
 * @param array $field The field array to populate.
 * @return array The populated field array.
 */
function populate_select_subscription_plan_field( array $field ): array {
	$data_all_plans = get_all_plans();
	$choices        = array();
	if ( $data_all_plans ) {
		foreach ( $data_all_plans as $plan ) {
			$choices[ $plan['plan_id'] ] = $plan['plan_name'];
		}
	}
	$field['choices'] = $choices;
	return $field;
}
