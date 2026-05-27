<?php
/**
 * ACF Group: Single Page Bottom Content Settings.
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\single_content;

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
	return;
}

$sub_field_default = array(
	'key'           => 'field_64214c940ef07',
	'label'         => __('Default', TM_LANGUAGE_DOMAIN),
	'name'          => 'bottom_content_setting_default',
	'type'          => 'true_false',
	'default_value' => 0,
	'ui'            => 1,
	'ui_on_text'    => 'Yes',
	'ui_off_text'   => 'No',
	'wrapper'       => array(
		'width' => '20',
	),
);

$sub_field_visibility = array(
	'key'          => 'field_642464ada0dc0',
	'label'        => __('Visibility', TM_LANGUAGE_DOMAIN),
	'name'         => 'bottom_content_setting_visibility',
	'type'         => 'clone',
	'clone'        => array(
		'group_642463a14a96b',
	),
	'display'      => 'seamless',
	'layout'       => 'block',
	'prefix_label' => 0,
	'prefix_name'  => 0,
	'required'     => 1,
	'wrapper'      => array(
		'width' => '40',
	),
);

$sub_field_content_type = array(
	'key'               => 'field_64214c940ef7e',
	'label'             => __('Content Types', TM_LANGUAGE_DOMAIN),
	'name'              => 'bottom_content_setting_content_type',
	'type'              => 'taxonomy',
	'taxonomy'          => 'content_types',
	'field_type'        => 'multi_select',
	'return_format'     => 'object',
	'wrapper'           => array(
		'width' => '40',
	),
	'required'          => 1,
	'allow_null'        => 0,
	'multiple'          => 0,
	'conditional_logic' => array(
		array(
			array(
				'field'    => 'field_64214c940ef07',
				'operator' => '!=',
				'value'    => '1',
			),
		),
	),
);

$sub_field_widget = array(
	'key'          => 'field_64214c940efb7',
	'label'        => __('Widget/s', TM_LANGUAGE_DOMAIN),
	'name'         => 'bottom_content_setting_widget',
	'type'         => 'clone',
	'clone'        => array(
		'group_64215e7874cc3',
	),
	'display'      => 'seamless',
	'layout'       => 'block',
	'prefix_label' => 0,
	'prefix_name'  => 0,
	'wrapper'      => array(
		'width' => '40',
	),
);

$field_bottom_settings = array(
	'key'          => 'field_64214c93b2fde',
	'label'        => __('Bottom Content Settings', TM_LANGUAGE_DOMAIN),
	'name'         => 'single_bottom_content_settings',
	'type'         => 'flexible_content',
	'button_label' => 'New Setting',
	'layouts'      => array(
		'layout_6239a213aab70' => array(
			'key'        => 'layout_6239a213aab70',
			'name'       => 'condition_sidebar_setting',
			'label'      => __('Condition', TM_LANGUAGE_DOMAIN),
			'display'    => 'table',
			'sub_fields' => array(
				$sub_field_default,
				$sub_field_visibility,
				$sub_field_content_type,
				$sub_field_widget,
			),
		),
	),
);

acf_add_local_field_group(
	array(
		'key'                   => 'group_64214c9385945',
		'title'                 => __('Single Page Bottom Content Settings', TM_LANGUAGE_DOMAIN),
		'fields'                => array(
			$field_bottom_settings,
		),
		'location'              => array(
			array(
				array(
					'param'    => 'options_page',
					'operator' => '==',
					'value'    => 'single-page-settings',
				),
			),
		),
		'menu_order'            => 2,
		'position'              => 'normal',
		'style'                 => 'seamless',
		'label_placement'       => 'top',
		'instruction_placement' => 'field',
		'hide_on_screen'        => '',
		'active'                => false,
		'description'           => '',
	)
);
