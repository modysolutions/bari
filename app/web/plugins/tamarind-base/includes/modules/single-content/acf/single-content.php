<?php
/**
 * ACF Group: Single Page Contents.
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\single_content;

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
	return;
}

$tab_default = array(
	'key'       => 'field_641db532c37dd',
	'label'     => __('Default (Restricted Contents)', TM_LANGUAGE_DOMAIN),
	'name'      => '',
	'type'      => 'tab',
	'placement' => 'left',
);

$field_enable_default_restricted_contents = array(
	'key'           => 'tm_enable_default_restricted_contents',
	'label'         => __('Enable default for restricted contents', TM_LANGUAGE_DOMAIN),
	'name'          => 'enable_default_restricted_contents',
	'type'          => 'true_false',
	'instructions'  => __('This will disable the free sample content, button download PDF and link default.', TM_LANGUAGE_DOMAIN),
	'default_value' => 0,
	'ui'            => 1,
	'ui_on_text'    => 'Yes',
	'ui_off_text'   => 'No',
	'wrapper'       => array(
		'width' => '25',
	),
);

$field_cta_main = array(
	'key'               => 'field_641db532c381c',
	'label'             => __('Button default', TM_LANGUAGE_DOMAIN),
	'name'              => 'r_cta_main',
	'type'              => 'link',
	'return_format'     => 'array',
	'conditional_logic' => array(
		'field'    => 'tm_enable_default_restricted_contents',
		'operator' => '!=',
		'value'    => '1',
	),
	'wrapper'           => array(
		'width' => '25',
	),
);

$field_cta_pdf = array(
	'key'               => 'field_65564bf2897b7',
	'label'             => __('Button Download Pdf', TM_LANGUAGE_DOMAIN),
	'name'              => 'r_cta_main_full_pdf',
	'type'              => 'text',
	'conditional_logic' => array(
		'field'    => 'tm_enable_default_restricted_contents',
		'operator' => '!=',
		'value'    => '1',
	),
	'wrapper'           => array(
		'width' => '25',
	),
);

$field_cta_sample = array(
	'key'               => 'field_65564c12897b8',
	'label'             => __('Button Free Sample', TM_LANGUAGE_DOMAIN),
	'name'              => 'r_cta_main_full_free_sample',
	'type'              => 'text',
	'conditional_logic' => array(
		'field'    => 'tm_enable_default_restricted_contents',
		'operator' => '!=',
		'value'    => '1',
	),
	'wrapper'           => array(
		'width' => '25',
	),
);

$field_restricted_content_message = array(
	'key'               => 'tm_r_cta_restricted_content_message',
	'label'             => __('Restricted Content Message', TM_LANGUAGE_DOMAIN),
	'name'              => 'r_cta_restricted_content_message',
	'type'              => 'wysiwyg',
	'toolbar'           => 'basic',
	'media_upload'      => 0,
	'rows'              => 2,
	'conditional_logic' => array(
		'field'    => 'tm_enable_default_restricted_contents',
		'operator' => '!=',
		'value'    => '0',
	),
	'wrapper'           => array(
		'width' => '50',
	),
);

$field_restricted_content_cta = array(
	'key'               => 'tm_r_cta_restricted_content_cta',
	'label'             => __('CTA/Link', TM_LANGUAGE_DOMAIN),
	'name'              => 'r_cta_restricted_content_cta',
	'type'              => 'link',
	'return_format'     => 'array',
	'conditional_logic' => array(
		'field'    => 'tm_enable_default_restricted_contents',
		'operator' => '!=',
		'value'    => '0',
	),
	'wrapper'           => array(
		'width' => '25',
	),

);

$field_cta_secondary = array(
	'key'     => 'field_642eaf59c7335',
	'label'   => __('Suscriptor - Text Button', TM_LANGUAGE_DOMAIN),
	'name'    => 'r_cta_secoundary_button',
	'type'    => 'text',
	'wrapper' => array(
		'width' => '25',
	),
);

$tab_related_contents = array(
	'key'       => 'field_6425632fbdbce',
	'label'     => __('Related Contents', TM_LANGUAGE_DOMAIN),
	'name'      => '',
	'type'      => 'tab',
	'placement' => 'left',
);

$field_related_post_title = array(
	'key'     => 'field_64256365bdbd0',
	'label'   => __('Title', TM_LANGUAGE_DOMAIN),
	'name'    => 'settings_related_post_title',
	'type'    => 'text',
	'wrapper' => array(
		'width' => '50',
	),
);

$field_related_post_num = array(
	'key'     => 'field_642563e8bdbd2',
	'label'   => __('Quantity', TM_LANGUAGE_DOMAIN),
	'name'    => 'settings_related_post_num',
	'type'    => 'number',
	'wrapper' => array(
		'width' => '25',
	),
);

$message_related_post_placement = array(
	'key'     => 'field_message_related_post_placement',
	'label'   => __('Inline Placement', TM_LANGUAGE_DOMAIN),
	'name'    => 'message_related_post_placement',
	'type'    => 'message',
	'message' => __('In the selected content types, Related Posts will be displayed inline in a midway placement. In all others, they will be displayed at the end of the content.', TM_LANGUAGE_DOMAIN),
);

$field_related_post_content_type_inline = array(
	'key'           => 'field_related_post_content_type_inline',
	'label'         => __('Content Types to show inline', TM_LANGUAGE_DOMAIN),
	'name'          => 'related_post_content_type_inline',
	'type'          => 'taxonomy',
	'taxonomy'      => 'content_types',
	'field_type'    => 'multi_select',
	'return_format' => 'object',
	'wrapper'       => array(
		'width' => '50',
	),
);

$field_related_post_content_type_inline_excluded = array(
	'key'           => 'field_related_post_content_type_inline_excluded',
	'label'         => __('Content Types to Exclude', TM_LANGUAGE_DOMAIN),
	'name'          => 'related_post_content_type_inline_excluded',
	'type'          => 'taxonomy',
	'taxonomy'      => 'content_types',
	'field_type'    => 'multi_select',
	'return_format' => 'object',
	'wrapper'       => array(
		'width' => '50',
	),
);

$tab_related_free_posts = array(
	'key'       => 'field_64256fb1c1a73',
	'label'     => __('Related Free Posts', TM_LANGUAGE_DOMAIN),
	'name'      => '',
	'type'      => 'tab',
	'placement' => 'left',
);

$field_related_free_post_title = array(
	'key'     => 'field_64256fbfc1a74',
	'label'   => __('Title', TM_LANGUAGE_DOMAIN),
	'name'    => 'settings_related_free_post_title',
	'type'    => 'text',
	'wrapper' => array(
		'width' => '50',
	),
);

$field_related_free_post_num = array(
	'key'     => 'field_64256fd5c1a75',
	'label'   => __('Quantity', TM_LANGUAGE_DOMAIN),
	'name'    => 'settings_related_free_post_num',
	'type'    => 'number',
	'wrapper' => array(
		'width' => '25',
	),
);

$tab_related_products = array(
	'key'       => 'field_64256344bdbcf',
	'label'     => __('Related Products', TM_LANGUAGE_DOMAIN),
	'name'      => '',
	'type'      => 'tab',
	'placement' => 'left',
);

$field_related_product_title = array(
	'key'     => 'field_64256391bdbd1',
	'label'   => __('Title', TM_LANGUAGE_DOMAIN),
	'name'    => 'settings_related_product_title',
	'type'    => 'text',
	'wrapper' => array(
		'width' => '50',
	),
);

$field_related_product_num = array(
	'key'     => 'field_64256404bdbd3',
	'label'   => __('Quantity', TM_LANGUAGE_DOMAIN),
	'name'    => 'settings_related_product_num',
	'type'    => 'number',
	'wrapper' => array(
		'width' => '25',
	),
);

$tab_bottom_content_settings = array(
	'key'       => 'field_64214cbc8275b',
	'label'     => __('Bottom Content Settings', TM_LANGUAGE_DOMAIN),
	'name'      => '',
	'type'      => 'tab',
	'placement' => 'left',
);

$field_settings_clone = array(
	'key'          => 'field_64214cdc8275c',
	'label'        => __('Settings', TM_LANGUAGE_DOMAIN),
	'name'         => 'settings_content',
	'type'         => 'clone',
	'clone'        => array(
		'group_64214c9385945',
	),
	'display'      => 'seamless',
	'layout'       => 'block',
	'prefix_label' => 0,
	'prefix_name'  => 0,
);

acf_add_local_field_group(
	array(
		'key'                   => 'group_641db53294ff8',
		'title'                 => __('Single Page Contents', TM_LANGUAGE_DOMAIN),
		'fields'                => array(
			$tab_default,
			$field_enable_default_restricted_contents,
			$field_cta_main,
			$field_cta_pdf,
			$field_cta_sample,
			$field_restricted_content_message,
			$field_restricted_content_cta,
			$field_cta_secondary,
			$tab_related_contents,
			$field_related_post_title,
			$field_related_post_num,
			$message_related_post_placement,
			$field_related_post_content_type_inline,
			$field_related_post_content_type_inline_excluded,
			$tab_related_free_posts,
			$field_related_free_post_title,
			$field_related_free_post_num,
			$tab_related_products,
			$field_related_product_title,
			$field_related_product_num,
			$tab_bottom_content_settings,
			$field_settings_clone,
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
		'menu_order'            => 1,
		'position'              => 'normal',
		'style'                 => 'default',
		'label_placement'       => 'top',
		'instruction_placement' => 'field',
		'hide_on_screen'        => '',
		'active'                => true,
	)
);
