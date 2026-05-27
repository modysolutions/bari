<?php
/**
 * ACF Group: Single Page - Modules Bottom Content.
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\single_content;

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
	return;
}

$field_modules_select = array(
	'key'           => 'field_64215e78a5923',
	'label'         => __('Modules', TM_LANGUAGE_DOMAIN),
	'name'          => 'select_setting_widget',
	'type'          => 'select',
	'choices'       => array(
		'author'           => __('Author Box', TM_LANGUAGE_DOMAIN),
		'benefits'         => __('Our Key Benefits', TM_LANGUAGE_DOMAIN),
		'related'          => __('Related Contents', TM_LANGUAGE_DOMAIN),
		'related-products' => __('Related Products', TM_LANGUAGE_DOMAIN),
		'related-free'     => __('Related Open Content', TM_LANGUAGE_DOMAIN),
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
		'key'      => 'group_64215e7874cc3',
		'title'    => __('Single Page - Modules Bottom Content', TM_LANGUAGE_DOMAIN),
		'fields'   => array(
			$field_modules_select,
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
