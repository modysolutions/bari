<?php
/**
 * ACF Fields Options for Tamarind Subscription.
 *
 * @package tamarind_subscriptions
 */

namespace tamarind_subscriptions;

defined( 'ABSPATH' ) || exit;

add_filter( 'acf/init', __NAMESPACE__ . '\register_acf_fields' );
add_filter( 'acf/load_field/name=roles_administrative_access', __NAMESPACE__ . '\populate_select_roles_option_field' );
add_filter( 'acf/load_field/name=roles_client_access', __NAMESPACE__ . '\populate_select_roles_option_field' );

/**
 * Register ACF fields for Tamarind Subscriptions.
 *
 * @return void
 */
function register_acf_fields() : void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	acf_add_options_page(
		array(
			'page_title'  => __( 'Subscriptions', 'tamarind-subscriptions' ),
			'menu_title'  => __( 'Subscriptions', 'tamarind-subscriptions' ),
			'menu_slug'   => 'tm-subscription',
			'capability'  => 'manage_options',
			'parent_slug' => 'tamarind-base',
			'redirect'    => false,
		)
	);

	$roles_adminitrative_access = array(
		'key'           => 'tm_roles_administrative_access',
		'label'         => __( 'Administrative access roles', 'tamarind-subscriptions' ),
		'name'          => 'roles_administrative_access',
		'aria-label'    => '',
		'type'          => 'select',
		'choices'       => array(), // Populate with roles.
		'multiple'      => 1,
		'ui'            => 1,
		'allow_null'    => 0,
		'return_format' => 'value',
		'ajax'          => 0,
		'placeholder'   => __( 'Select roles', 'tamarind-subscriptions' ),
		'instructions'  => 'Roles that will have full access to the site content.',
		'wrapper'       => array(
			'width' => '50',
		),
	);

	$roles_client_access = array(
		'key'           => 'tm_roles_client_access',
		'label'         => __( 'Client access roles', 'tamarind-subscriptions' ),
		'name'          => 'roles_client_access',
		'aria-label'    => '',
		'type'          => 'select',
		'choices'       => array(), // Populate with roles.
		'multiple'      => 1,
		'ui'            => 1,
		'allow_null'    => 0,
		'return_format' => 'value',
		'ajax'          => 0,
		'placeholder'   => __( 'Select roles', 'tamarind-subscriptions' ),
		'instructions'  => 'Roles that will have access to the site content according to their subscription plan.',
		'wrapper'       => array(
			'width' => '50',
		),
	);

	// Expiration Banner fields.
	$expiration_banner_active = array(
		'key'           => 'tm_expiration_banner_global_active',
		'label'         => __( 'Active expiration banner countdown', 'tamarind-subscriptions' ),
		'name'          => 'expiration_banner_global_active',
		'type'          => 'true_false',
		'instructions'  => __( 'You can disable the expiry banner globally, at the client level, or at the user level.', 'tamarind-subscriptions' ),
		'wrapper'       => array(
			'width' => '50',
		),
		'default_value' => 1,
		'ui'            => 1,
	);

	$expiration_banner_days = array(
		'key'               => 'tm_expiration_banner_days',
		'label'             => __( 'Days for expiration banner', 'tamarind-subscriptions' ),
		'name'              => 'expiration_banner_days_global',
		'type'              => 'number',
		'instructions'      => __( 'Define the number of days before expiration the countdown banner should start. You can define this value at the client and user level.', 'tamarind-subscriptions' ),
		'conditional_logic' => array(
			array(
				array(
					'field'    => 'tm_expiration_banner_global_active',
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

	$expiration_banner_text = array(
		'key'               => 'tm_expiration_banner_text',
		'label'             => __( 'Text', 'tamarind-subscriptions' ),
		'name'              => 'expiration_banner_text',
		'type'              => 'text',
		'instructions'      => 'Write {days} in the position where the remaining days of the countdown should appear.',
		'conditional_logic' => array(
			array(
				array(
					'field'    => 'tm_expiration_banner_global_active',
					'operator' => '==',
					'value'    => '1',
				),
			),
		),
		'wrapper'           => array(
			'width' => '50',
		),
	);

	$expiration_banner_link = array(
		'key'               => 'tm_expiration_banner_link',
		'label'             => __( 'Link', 'tamarind-subscriptions' ),
		'name'              => 'expiration_banner_link',
		'type'              => 'link',
		'conditional_logic' => array(
			array(
				array(
					'field'    => 'tm_expiration_banner_global_active',
					'operator' => '==',
					'value'    => '1',
				),
			),
		),
		'wrapper'           => array(
			'width' => '50',
		),
		'return_format'     => 'array',
	);

	$expiration_banner_background_color = array(
		'key'               => 'tm_expiration_banner_background_color',
		'label'             => __( 'Background Color', 'tamarind-subscriptions' ),
		'name'              => 'expiration_banner_background_color',
		'type'              => 'color_picker',
		'conditional_logic' => array(
			array(
				array(
					'field'    => 'tm_expiration_banner_global_active',
					'operator' => '==',
					'value'    => '1',
				),
			),
		),
		'wrapper'           => array(
			'width' => '25',
		),
		'default_value'     => '#16163a',
		'enable_opacity'    => 0,
		'return_format'     => 'string',
		'show_color_wheel'  => true,
	);

	$expiration_banner_background_featured_color = array(
		'key'               => 'tm_expiration_banner_background_featured_color',
		'label'             => __( 'Background Featured Color', 'tamarind-subscriptions' ),
		'name'              => 'expiration_banner_background_featured_color',
		'type'              => 'color_picker',
		'conditional_logic' => array(
			array(
				array(
					'field'    => 'tm_expiration_banner_global_active',
					'operator' => '==',
					'value'    => '1',
				),
			),
		),
		'wrapper'           => array(
			'width' => '25',
		),
		'default_value'     => '#49499e',
		'enable_opacity'    => 0,
		'return_format'     => 'string',
		'show_color_wheel'  => true,
	);

	$expiration_banner_style = array(
		'key'               => 'tm_expiration_banner_style',
		'label'             => __( 'Style', 'tamarind-subscriptions' ),
		'name'              => 'expiration_banner_style',
		'type'              => 'radio',
		'conditional_logic' => array(
			array(
				array(
					'field'    => 'tm_expiration_banner_global_active',
					'operator' => '==',
					'value'    => '1',
				),
			),
		),
		'wrapper'           => array(
			'width' => '25',
		),
		'choices'           => array(
			'light' => __( 'Light', 'tamarind-subscriptions' ),
			'dark'  => __( 'Dark', 'tamarind-subscriptions' ),
		),
		'default_value'     => 'dark',
		'return_format'     => 'value',
		'allow_null'        => 0,
		'other_choice'      => 0,
		'layout'            => 'horizontal',
		'save_other_choice' => 0,
	);

	$expiration_banner_fontsize = array(
		'key'               => 'tm_expiration_banner_fontsize',
		'label'             => __( 'Font Size', 'tamarind-subscriptions' ),
		'name'              => 'expiration_banner_fontsize',
		'type'              => 'radio',
		'conditional_logic' => array(
			array(
				array(
					'field'    => 'tm_expiration_banner_global_active',
					'operator' => '==',
					'value'    => '1',
				),
			),
		),
		'wrapper'           => array(
			'width' => '25',
		),
		'choices'           => array(
			'small'  => __( 'Small', 'tamarind-subscriptions' ),
			'medium' => __( 'Medium', 'tamarind-subscriptions' ),
			'big'    => __( 'Big', 'tamarind-subscriptions' ),
		),
		'default_value'     => 'medium',
		'return_format'     => 'value',
		'allow_null'        => 0,
		'other_choice'      => 0,
		'layout'            => 'horizontal',
		'save_other_choice' => 0,
	);

	// Expiration Modal fields.
	$expiration_modal_active = array(
		'key'           => 'tm_expiration_modal_global_active',
		'label'         => __( 'Active expiration modal', 'tamarind-subscriptions' ),
		'name'          => 'expiration_modal_global_active',
		'type'          => 'true_false',
		'instructions'  => __( 'You can disable the expiry modal globally or at the client level.', 'tamarind-subscriptions' ),
		'wrapper'       => array(
			'width' => '50',
		),
		'default_value' => 1,
		'ui'            => 1,
	);

	$expiration_modal_link = array(
		'key'               => 'tm_expiration_modal_link',
		'label'             => __( 'Link', 'tamarind-subscriptions' ),
		'name'              => 'expiration_modal_link',
		'type'              => 'link',
		'conditional_logic' => array(
			array(
				array(
					'field'    => 'tm_expiration_modal_global_active',
					'operator' => '==',
					'value'    => '1',
				),
			),
		),
		'wrapper'           => array(
			'width' => '50',
		),
		'return_format'     => 'array',
	);

	$expiration_modal_title = array(
		'key'               => 'tm_expiration_modal_title',
		'label'             => __( 'Title', 'tamarind-subscriptions' ),
		'name'              => 'expiration_modal_title',
		'type'              => 'text',
		'conditional_logic' => array(
			array(
				array(
					'field'    => 'tm_expiration_modal_global_active',
					'operator' => '==',
					'value'    => '1',
				),
			),
		),
	);

	$expiration_modal_content = array(
		'key'               => 'tm_expiration_modal_content',
		'label'             => __( 'Content', 'tamarind-subscriptions' ),
		'name'              => 'expiration_modal_content',
		'type'              => 'wysiwyg',
		'tabs'              => 'all',
		'toolbar'           => 'basic',
		'media_upload'      => 0,
		'conditional_logic' => array(
			array(
				array(
					'field'    => 'tm_expiration_modal_global_active',
					'operator' => '==',
					'value'    => '1',
				),
			),
		),
	);

	$message_chinese_banner_texts = array(
		'key'               => 'tm_message_chinese_banner_texts',
		'label'             => __( 'Chinese banner texts', 'tamarind-subscriptions' ),
		'name'              => 'message_chinese_banner_texts',
		'type'              => 'message',
		'message'           => __( 'Define chinese texts for <b>expiration banner.</b>', 'tamarind-forms' ),
		'conditional_logic' => array(
			array(
				array(
					'field'    => 'tm_expiration_banner_global_active',
					'operator' => '==',
					'value'    => '1',
				),
			),
		),
	);

	$expiration_banner_text_chinese = array(
		'key'               => 'tm_expiration_banner_text_chinese',
		'label'             => __( 'Text', 'tamarind-subscriptions' ),
		'name'              => 'expiration_banner_text_chinese',
		'type'              => 'text',
		'instructions'      => 'Write {days} in the position where the remaining days of the countdown should appear.',
		'wrapper'           => array(
			'width' => '50',
		),
		'conditional_logic' => array(
			array(
				array(
					'field'    => 'tm_expiration_banner_global_active',
					'operator' => '==',
					'value'    => '1',
				),
			),
		),
	);

	$expiration_banner_expired_chinese = array(
		'key'               => 'tm_expiration_banner_expired_chinese',
		'label'             => __( 'Expired message', 'tamarind-subscriptions' ),
		'name'              => 'expiration_banner_expired_chinese',
		'type'              => 'text',
		'instructions'      => 'Your subscription has expired message.',
		'wrapper'           => array(
			'width' => '50',
		),
		'conditional_logic' => array(
			array(
				array(
					'field'    => 'tm_expiration_banner_global_active',
					'operator' => '==',
					'value'    => '1',
				),
			),
		),
	);

	$message_chinese_modal_texts = array(
		'key'               => 'tm_message_chinese_modal_texts',
		'label'             => __( 'Chinese modal texts', 'tamarind-subscriptions' ),
		'name'              => 'message_chinese_modal_texts',
		'type'              => 'message',
		'message'           => __( 'Define chinese texts for <b>expiration modal.</b>', 'tamarind-forms' ),
		'conditional_logic' => array(
			array(
				array(
					'field'    => 'tm_expiration_modal_global_active',
					'operator' => '==',
					'value'    => '1',
				),
			),
		),
	);

	$expiration_modal_title_chinese = array(
		'key'               => 'tm_expiration_modal_title_chinese',
		'label'             => __( 'Title', 'tamarind-subscriptions' ),
		'name'              => 'expiration_modal_title_chinese',
		'type'              => 'text',
		'wrapper'           => array(
			'width' => '50',
		),
		'conditional_logic' => array(
			array(
				array(
					'field'    => 'tm_expiration_modal_global_active',
					'operator' => '==',
					'value'    => '1',
				),
			),
		),
	);

	$expiration_modal_link_chinese = array(
		'key'               => 'tm_expiration_modal_link_chinese',
		'label'             => __( 'Link', 'tamarind-subscriptions' ),
		'name'              => 'expiration_modal_link_chinese',
		'type'              => 'link',
		'wrapper'           => array(
			'width' => '50',
		),
		'return_format'     => 'array',
		'conditional_logic' => array(
			array(
				array(
					'field'    => 'tm_expiration_modal_global_active',
					'operator' => '==',
					'value'    => '1',
				),
			),
		),
	);

	$expiration_modal_content_chinese = array(
		'key'               => 'tm_expiration_modal_content_chinese',
		'label'             => __( 'Content', 'tamarind-subscriptions' ),
		'name'              => 'expiration_modal_content_chinese',
		'type'              => 'wysiwyg',
		'tabs'              => 'all',
		'toolbar'           => 'basic',
		'media_upload'      => 0,
		'conditional_logic' => array(
			array(
				array(
					'field'    => 'tm_expiration_modal_global_active',
					'operator' => '==',
					'value'    => '1',
				),
			),
		),
	);

	$tab_field_role = array(
		'key'       => 'field_tamarind_subscriptions_tab_role',
		'label'     => __( 'Role settings', 'tamarind-subscriptions' ),
		'name'      => 'tab_role_settings',
		'type'      => 'tab',
		'placement' => 'left',
	);

	$tab_field_expiration_banner = array(
		'key'       => 'field_tamarind_subscriptions_tab_expiration_banner',
		'label'     => __( 'Expiration banner', 'tamarind-subscriptions' ),
		'name'      => 'tab_expiration_banner',
		'type'      => 'tab',
		'placement' => 'left',
	);

	$tab_field_expiration_modal = array(
		'key'               => 'field_tamarind_subscriptions_tab_expiration_modal',
		'label'             => __( 'Expiration modal', 'tamarind-subscriptions' ),
		'name'              => 'tab_expiration_modal',
		'type'              => 'tab',
		'placement'         => 'left',
		'conditional_logic' => array(
			array(
				array(
					'field'    => 'tm_expiration_banner_global_active',
					'operator' => '==',
					'value'    => '1',
				),
			),
		),
	);

	$tab_field_chinese_texts = array(
		'key'       => 'field_tamarind_subscriptions_tab_chinese_texts',
		'label'     => __( 'Chinese texts', 'tamarind-subscriptions' ),
		'name'      => 'tab_chinese_texts',
		'type'      => 'tab',
		'placement' => 'left',
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'tm_trial_demo_settings_group',
			'title'                 => __( 'Subscription settings', 'tamarind-subscriptions' ),
			'fields'                => array(
				$tab_field_role,
				$roles_adminitrative_access,
				$roles_client_access,
				$tab_field_expiration_banner,
				$expiration_banner_active,
				$expiration_banner_days,
				$expiration_banner_text,
				$expiration_banner_link,
				$expiration_banner_background_color,
				$expiration_banner_background_featured_color,
				$expiration_banner_style,
				$expiration_banner_fontsize,
				$tab_field_expiration_modal,
				$expiration_modal_active,
				$expiration_modal_link,
				$expiration_modal_title,
				$expiration_modal_content,
				$tab_field_chinese_texts,
				$message_chinese_banner_texts,
				$expiration_banner_text_chinese,
				$expiration_banner_expired_chinese,
				$message_chinese_modal_texts,
				$expiration_modal_title_chinese,
				$expiration_modal_link_chinese,
				$expiration_modal_content_chinese,
			),
			'location'              => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'tm-subscription',
					),
				),
			),
			'menu_order'            => 1,
			'position'              => 'normal',
			'label_placement'       => 'top',
			'instruction_placement' => 'field',
			'hide_on_screen'        => '',
			'active'                => true,
			'description'           => '',
			'show_in_rest'          => 0,
		)
	);
}

/**
 * Populate the select field with WordPress roles.
 *
 * @param array $field The ACF field array.
 * @return array
 */
function populate_select_roles_option_field( array $field ) : array {
	$field['choices'] = array();
	if ( function_exists( 'wp_roles' ) ) {
		$roles = wp_roles()->roles;
		foreach ( $roles as $role_key => $role_data ) {
			$field['choices'][ $role_key ] = translate_user_role( $role_data['name'] );
		}
	}

	return $field;
}
