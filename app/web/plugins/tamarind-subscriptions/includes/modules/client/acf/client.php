<?php
/**
 * ACFs for Tamarind Clients.
 *
 * @package tamarind_subscriptions
 */

namespace tamarind_subscriptions\client;

defined( 'ABSPATH' ) || exit;

add_filter( 'acf/init', __NAMESPACE__ . '\register_acf_fields' );

/**
 * Register ACF fields for Clients.
 *
 * @return void
 */
function register_acf_fields() :void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$old_term_id = array(
		'key'          => 'field_client_old_term_id',
		'label'        => __( 'Old Term ID', 'tamarind-subscriptions' ),
		'name'         => 'old_term_id',
		'type'         => 'number',
		'instructions' => 'The old term ID for the client.',
		'wrapper'      => array(
			'width' => '50',
		),
	);

	$old_term_slug = array(
		'key'          => 'field_client_old_term_slug',
		'label'        => __( 'Old Term Slug', 'tamarind-subscriptions' ),
		'name'         => 'old_term_slug',
		'type'         => 'text',
		'instructions' => 'The old term slug for the client.',
		'wrapper'      => array(
			'width' => '50',
		),
	);

	$subscription_plan = array(
		'key'                  => 'field_client_subscription_plan',
		'label'                => __( 'Subscription Plan', 'tamarind-subscriptions' ),
		'name'                 => 'subscription_plan',
		'type'                 => 'post_object',
		'post_type'            => array( 'subscription-plan' ),
		'multiple'             => 0,
		'allow_null'           => 1,
		'return_format'        => 'id',
		'ui'                   => 1,
		'bidirectional'        => 1,
		'bidirectional_target' => array(
			0 => 'field_subscription_plan_clients',
		),
		'wrapper'              => array(
			'width' => '50',
		),
	);

	$is_client_active = array(
		'key'     => 'field_is_client_active',
		'label'   => __( 'Is client active', 'tamarind-subscriptions' ),
		'name'    => 'is_client_active',
		'type'    => 'true_false',
		'ui'      => 1,
		'wrapper' => array(
			'width' => '25',
		),
	);

	$is_client_chinese = array(
		'key'     => 'field_is_client_chinese',
		'label'   => __( 'Is client chinese', 'tamarind-subscriptions' ),
		'name'    => 'is_client_chinese',
		'type'    => 'true_false',
		'ui'      => 1,
		'wrapper' => array(
			'width' => '25',
		),
	);

	$start_date_plan = array(
		'key'            => 'field_client_start_date_plan',
		'label'          => __( 'Start Date Plan', 'tamarind-subscriptions' ),
		'name'           => 'start_date_plan',
		'type'           => 'date_picker',
		'display_format' => 'd/m/Y',
		'return_format'  => 'Y-m-d',
		'first_day'      => 1,
		'wrapper'        => array(
			'width' => '50',
		),
	);

	$expiration_date_plan = array(
		'key'            => 'field_client_expiration_date_plan',
		'label'          => __( 'Expiration Date Plan', 'tamarind-subscriptions' ),
		'name'           => 'expiration_date_plan',
		'type'           => 'date_picker',
		'display_format' => 'd/m/Y',
		'return_format'  => 'Y-m-d',
		'first_day'      => 1,
		'wrapper'        => array(
			'width' => '50',
		),
	);

	$client_users = array(
		'key'                  => 'field_client_users',
		'label'                => __( 'Users', 'tamarind-subscriptions' ),
		'name'                 => 'users',
		'type'                 => 'user',
		'role'                 => '',
		'multiple'             => 1,
		'allow_null'           => 1,
		'return_format'        => 'id',
		'bidirectional'        => 1,
		'bidirectional_target' => array(
			0 => 'field_user_related_client',
		),
	);

	$expiration_banner_active = array(
		'key'           => 'tm_expiration_banner_client_active',
		'label'         => __( 'Active expiration banner countdown', 'tamarind-subscriptions' ),
		'name'          => 'expiration_banner_client_active',
		'type'          => 'true_false',
		'instructions'  => __( 'You can disable the expiry banner globally, at the client level, or at the user level.', 'tamarind-subscriptions' ),
		'wrapper'       => array(
			'width' => '50',
		),
		'default_value' => 1,
		'ui'            => 1,
	);

	$expiration_banner_days = array(
		'key'               => 'tm_expiration_banner_days_client',
		'label'             => __( 'Days for expiration banner', 'tamarind-subscriptions' ),
		'name'              => 'expiration_banner_days_client',
		'type'              => 'number',
		'instructions'      => __( 'Define the number of days before expiration the countdown banner should start. You can define this value at global and user level too.', 'tamarind-subscriptions' ),
		'conditional_logic' => array(
			array(
				array(
					'field'    => 'tm_expiration_banner_client_active',
					'operator' => '==',
					'value'    => '1',
				),
			),
		),
		'wrapper'           => array(
			'width' => '50',
		),
		'min'               => 1,
		'step'              => 1,
	);

	$expiration_modal_active = array(
		'key'               => 'tm_expiration_modal_client_active',
		'label'             => __( 'Active expiration modal', 'tamarind-subscriptions' ),
		'name'              => 'expiration_modal_client_active',
		'type'              => 'true_false',
		'instructions'      => __( 'You can disable the expiry modal globally or at the client level.', 'tamarind-subscriptions' ),
		'conditional_logic' => array(
			array(
				array(
					'field'    => 'tm_expiration_banner_client_active',
					'operator' => '==',
					'value'    => '1',
				),
			),
		),
		'wrapper'           => array(
			'width' => '50',
		),
		'default_value'     => 1,
		'ui'                => 1,
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_client_fields',
			'title'    => __( 'Client Fields', 'tamarind-subscriptions' ),
			'fields'   => array(
				$subscription_plan,
				$is_client_active,
				$is_client_chinese,
				$start_date_plan,
				$expiration_date_plan,
				$client_users,
				$expiration_banner_active,
				$expiration_banner_days,
				$expiration_modal_active,
				$old_term_id,
				$old_term_slug,
			),
			'location' => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'client',
					),
				),
			),
			'style'    => 'default',
		)
	);
}
