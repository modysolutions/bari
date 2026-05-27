<?php
/**
 * ACFs for Tamarind Users.
 *
 * @package tamarind_subscriptions
 */

namespace tamarind_subscriptions\users;

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

	$related_client = array(
		'key'                  => 'field_user_related_client',
		'label'                => __( 'Related Client', 'tamarind-subscriptions' ),
		'name'                 => 'related_client',
		'type'                 => 'post_object',
		'post_type'            => array( 'client' ),
		'multiple'             => 0,
		'allow_null'           => 1,
		'return_format'        => 'id',
		'ui'                   => 1,
		'bidirectional'        => 1,
		'bidirectional_target' => array(
			0 => 'field_client_users',
		),
		'wrapper'              => array( 'width' => '50' ),
	);

	$related_plan = array(
		'key'                  => 'field_user_related_plan',
		'label'                => __( 'Related Subscription Plan', 'tamarind-subscriptions' ),
		'name'                 => 'related_subscription_plan',
		'type'                 => 'post_object',
		'post_type'            => array( 'subscription-plan' ),
		'multiple'             => 0,
		'allow_null'           => 1,
		'return_format'        => 'id',
		'ui'                   => 1,
		'bidirectional'        => 1,
		'bidirectional_target' => array(
			0 => 'field_subscription_plan_users',
		),
		'wrapper'              => array( 'width' => '50' ),
	);

	$start_date_plan = array(
		'key'            => 'field_user_plan_start_date',
		'label'          => __( 'Start Date Plan', 'tamarind-subscriptions' ),
		'name'           => 'user_start_date',
		'type'           => 'date_picker',
		'display_format' => 'd/m/Y',
		'return_format'  => 'Y-m-d',
		'first_day'      => 1,
	);

	$is_user_active = array(
		'key'     => 'field_is_user_active',
		'label'   => __( 'Is user active', 'tamarind-subscriptions' ),
		'name'    => 'is_user_active',
		'type'    => 'true_false',
		'ui'      => 1,
		'wrapper' => array(
			'width' => '50',
		),
	);

	$expire_date_plan = array(
		'key'            => 'field_user_plan_expirate_date',
		'label'          => __( 'Expiration Date Plan', 'tamarind-subscriptions' ),
		'name'           => 'user_expirate_date',
		'type'           => 'date_picker',
		'display_format' => 'd/m/Y',
		'return_format'  => 'Y-m-d',
		'first_day'      => 1,
	);

	$expiration_banner_active = array(
		'key'           => 'tm_expiration_banner_user_active',
		'label'         => __( 'Active Expiration banner countdown', 'tamarind-subscriptions' ),
		'name'          => 'expiration_banner_user_active',
		'aria-label'    => '',
		'type'          => 'true_false',
		'message'       => __( 'You can disable the expiry banner globally, at the client level, or at the user level.', 'tamarind-subscriptions' ),
		'default_value' => 1,
		'ui'            => 1,
	);

	$expiration_banner_days = array(
		'key'               => 'tm_expiration_banner_days_user',
		'label'             => __( 'Days for expiration banner', 'tamarind-subscriptions' ),
		'name'              => 'expiration_banner_days_user',
		'type'              => 'number',
		'instructions'      => __( 'Define the number of days before expiration the countdown banner should start. You can define this value at global and client level too.', 'tamarind-subscriptions' ),
		'conditional_logic' => array(
			array(
				array(
					'field'    => 'tm_expiration_banner_user_active',
					'operator' => '==',
					'value'    => '1',
				),
			),
		),
		'min'               => 1,
		'step'              => 1,
	);

	$expiration_modal_active = array(
		'key'           => 'tm_expiration_modal_user_active',
		'label'         => __( 'Active Expiration Modal', 'tamarind-subscriptions' ),
		'name'          => 'expiration_modal_user_active',
		'aria-label'    => '',
		'type'          => 'true_false',
		'message'       => __( 'You can disable the expiry modal globally or at the client level.<br>This field is automatically deactivated when the user has viewed the modal. It is reactivated when the expiration date is updated.', 'tamarind-subscriptions' ),
		'conditional_logic' => array(
			array(
				array(
					'field'    => 'tm_expiration_banner_user_active',
					'operator' => '==',
					'value'    => '1',
				),
			),
		),
		'default_value' => 1,
		'ui'            => 1,
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_user_subscription_fields',
			'title'    => __( 'User Subscription Fields', 'tamarind-subscriptions' ),
			'fields'   => array(
				$related_client,
				$related_plan,
				$start_date_plan,
				$expire_date_plan,
				$is_user_active,
				$expiration_banner_active,
				$expiration_banner_days,
				$expiration_modal_active,
			),
			'location' => array(
				array(
					array(
						'param'    => 'user_form',
						'operator' => '==',
						'value'    => 'all',
					),
				),
			),
			'style'    => 'default',
		)
	);
}
