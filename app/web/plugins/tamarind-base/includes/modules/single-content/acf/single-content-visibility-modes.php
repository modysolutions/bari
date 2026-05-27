<?php
/**
 * ACF Group: Single Page - Visibility Modes.
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\single_content;

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
	return;
}

$field_visibility_select = array(
	'key'           => 'field_642463a1748bf',
	'label'         => __('Visibility', TM_LANGUAGE_DOMAIN),
	'name'          => 'select_setting_visibility',
	'type'          => 'select',
	'choices'       => array(
		'visitor'      => 'Visitor',
		'subscriber'   => 'Subscriber',
		'limited-plan' => 'Limited Plan',
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
		'key'      => 'group_642463a14a96b',
		'title'    => __('Single Page - Visibility Modes', TM_LANGUAGE_DOMAIN),
		'fields'   => array(
			$field_visibility_select,
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
