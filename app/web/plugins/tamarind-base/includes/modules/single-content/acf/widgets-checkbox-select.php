<?php
/**
 * ACF Group: Single Page - Widget Checkbox Select.
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\single_content;

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
	return;
}

$field_select_setting_widget = array(
	'key'           => 'field_64214df151a5c',
	'label'         => __('Widgets', TM_LANGUAGE_DOMAIN),
	'name'          => 'select_setting_widget',
	'type'          => 'select',
	'choices'       => array(
		'latest'   => __('Latest Posts', TM_LANGUAGE_DOMAIN),
		'related'  => __('Related Contents', TM_LANGUAGE_DOMAIN),
		'alerts'   => __('Latest Alerts', TM_LANGUAGE_DOMAIN),
		'store'    => __('Banner Store', TM_LANGUAGE_DOMAIN),
		'benefits' => __('Our Key Benefits', TM_LANGUAGE_DOMAIN),
	),
	'default_value' => array(),
	'allow_null'    => 0,
	'multiple'      => 1,
	'ui'            => 1,
	'ajax'          => 0,
	'return_format' => 'value',
);

acf_add_local_field_group(
	array(
		'key'      => 'group_64214df14d456',
		'title'    => __('Single Page - Widget Checkbox Select', TM_LANGUAGE_DOMAIN),
		'fields'   => array(
			$field_select_setting_widget,
		),
		'location' => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'post',
				),
			),
		),
		'active'   => false,
	)
);
