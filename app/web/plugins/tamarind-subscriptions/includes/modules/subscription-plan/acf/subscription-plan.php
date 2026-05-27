<?php
/**
 * ACFs for Tamarind Subscription Plans.
 *
 * @package tamarind_subscriptions
 */

namespace tamarind_subscriptions\subscription_plan;

defined( 'ABSPATH' ) || exit;

add_filter( 'acf/init', __NAMESPACE__ . '\register_acf_fields' );

/**
 * Register ACF fields for Subscription Plan.
 *
 * @return void
 */
function register_acf_fields() :void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	// Plan Info.
	$plan_name = array(
		'key'          => 'field_subscription_plan_name',
		'label'        => __( 'Plan name', 'tamarind-subscriptions' ),
		'name'         => 'plan_name',
		'type'         => 'text',
		'instructions' => 'Name displayed to the user',
		'wrapper'      => array( 'width' => '33' ),
	);

	$plan_slug = array(
		'key'          => 'field_subscription_plan_slug',
		'label'        => __( 'Plan slug', 'tamarind-subscriptions' ),
		'name'         => 'plan_slug',
		'type'         => 'text',
		'instructions' => 'Must be the same slug as the user group created',
		'wrapper'      => array( 'width' => '33' ),
	);

	$plan_contents = array(
		'key'           => 'field_subscription_plan_contents',
		'label'         => __( 'Plan contents', 'tamarind-subscriptions' ),
		'name'          => 'plan_contents',
		'type'          => 'taxonomy',
		'taxonomy'      => 'content_types',
		'field_type'    => 'checkbox',
		'return_format' => 'id',
		'wrapper'       => array( 'width' => '33' ),
	);

	// Filters.
	$plan_filters = array(
		'key'           => 'field_subscription_plan_filters',
		'label'         => __( 'Filters: Limit by country (contents or alerts)', 'tamarind-subscriptions' ),
		'name'          => 'plan_filters',
		'type'          => 'true_false',
		'wrapper'       => array( 'width' => '33' ),
		'ui'            => 1,
		'ui_on_text'    => 'Yes',
		'ui_off_text'   => 'No',
		'default_value' => 0,
	);

	$plan_filters_geo_tax = array(
		'key'           => 'field_subscription_plan_filters_geo_tax',
		'label'         => __( 'Filter Geography', 'tamarind-subscriptions' ),
		'name'          => 'plan_filters_geo_tax',
		'type'          => 'taxonomy',
		'taxonomy'      => 'geography',
		'field_type'    => 'multi_select',
		'allow_null'    => 1,
		'return_format' => 'id',
		'wrapper'       => array( 'width' => '33' ),
	);

	$plan_filters_geo_alerts_tax = array(
		'key'           => 'field_subscription_plan_filters_geo_alerts_tax',
		'label'         => __( 'Filter Geography (alerts)', 'tamarind-subscriptions' ),
		'name'          => 'plan_filters_geo_alerts_tax',
		'type'          => 'taxonomy',
		'taxonomy'      => 'geography',
		'field_type'    => 'multi_select',
		'allow_null'    => 1,
		'return_format' => 'id',
		'wrapper'       => array( 'width' => '33' ),
	);

	// Access & Features.
	$plan_clients = array(
		'key'                  => 'field_subscription_plan_clients',
		'label'                => __( 'Clients', 'tamarind-subscriptions' ),
		'name'                 => 'plan_clients',
		'type'                 => 'post_object',
		'post_type'            => array( 'client' ),
		'multiple'             => 1,
		'allow_null'           => 1,
		'return_format'        => 'id',
		'ui'                   => 1,
		'bidirectional'        => 1,
		'bidirectional_target' => array(
			0 => 'field_client_subscription_plan',
		),
		'wrapper'              => array(
			'width' => '50',
		),
	);

	$plan_users = array(
		'key'                  => 'field_subscription_plan_users',
		'label'                => __( 'Users', 'tamarind-subscriptions' ),
		'name'                 => 'plan_users',
		'type'                 => 'user',
		'role'                 => '',
		'multiple'             => 1,
		'allow_null'           => 1,
		'return_format'        => 'id',
		'bidirectional'        => 1,
		'bidirectional_target' => array(
			0 => 'field_user_related_plan',
		),
		'wrapper'              => array(
			'width' => '50',
		),
	);

	$plan_demo = array(
		'key'           => 'field_subscription_plan_demo',
		'label'         => __( 'Allow Trial Demo', 'tamarind-subscriptions' ),
		'name'          => 'plan_demo',
		'type'          => 'true_false',
		'wrapper'       => array( 'width' => '33' ),
		'ui'            => 1,
		'ui_on_text'    => 'Demo',
		'ui_off_text'   => 'Normal',
		'default_value' => 0,
	);

	$plan_allow_pdf_downloads = array(
		'key'           => 'field_subscription_plan_allow_pdf_downloads',
		'label'         => __( 'Allow PDF downloads', 'tamarind-subscriptions' ),
		'name'          => 'plan_allow_pdf_downloads',
		'type'          => 'true_false',
		'wrapper'       => array( 'width' => '33' ),
		'ui'            => 1,
		'ui_on_text'    => 'Yes',
		'ui_off_text'   => 'No',
		'default_value' => 0,
	);

	// Tabs.
	$tab_plan_info = array(
		'key'       => 'field_subscription_plan_tab_info',
		'label'     => __( 'Plan Info', 'tamarind-subscriptions' ),
		'name'      => '',
		'type'      => 'tab',
		'placement' => 'left',
	);

	$tab_filters = array(
		'key'       => 'field_subscription_plan_tab_filters',
		'label'     => __( 'Filters', 'tamarind-subscriptions' ),
		'name'      => '',
		'type'      => 'tab',
		'placement' => 'left',
	);

	$tab_access = array(
		'key'       => 'field_subscription_plan_tab_access',
		'label'     => __( 'Access & Features', 'tamarind-subscriptions' ),
		'name'      => '',
		'type'      => 'tab',
		'placement' => 'left',
	);

	$tab_clients = array(
		'key'       => 'field_subscription_plan_tab_clients',
		'label'     => __( 'Clients', 'tamarind-subscriptions' ),
		'name'      => '',
		'type'      => 'tab',
		'placement' => 'left',
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_subscription_plan_fields',
			'title'    => __( 'Subscription Plan Fields', 'tamarind-subscriptions' ),
			'fields'   => array(
				$tab_plan_info,
				$plan_name,
				$plan_slug,
				$plan_contents,
				$tab_filters,
				$plan_filters,
				$plan_filters_geo_tax,
				$plan_filters_geo_alerts_tax,
				$tab_clients,
				$plan_clients,
				$plan_users,
				$tab_access,
				$plan_demo,
				$plan_allow_pdf_downloads,
			),
			'location' => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'subscription-plan',
					),
				),
			),
			'style'    => 'default',
		)
	);
}
