<?php
/**
 * ACF Group: Single Page Widgets.
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\single_content;

if ( ! function_exists( 'acf_add_local_field_group' ) ) {
	return;
}

$tab_latest_contents = array(
	'key'       => 'field_641c3a386c352',
	'label'     => __('Latest Contents', TM_LANGUAGE_DOMAIN),
	'name'      => '',
	'type'      => 'tab',
	'placement' => 'left',
);

$field_latest_title = array(
	'key'           => 'field_641c3ac868bac',
	'label'         => __('Title', TM_LANGUAGE_DOMAIN),
	'name'          => 'w_latest_contents_title',
	'type'          => 'text',
	'default_value' => __('Latest Contents', TM_LANGUAGE_DOMAIN),
	'wrapper'       => array(
		'width' => '25',
	),
);

$field_latest_num = array(
	'key'     => 'field_641c3b2668bad',
	'label'   => __('Quantity', TM_LANGUAGE_DOMAIN),
	'name'    => 'w_latest_contents_num',
	'type'    => 'number',
	'wrapper' => array(
		'width' => '25',
	),
);

$tab_related_contents = array(
	'key'       => 'field_641c3a386c38e',
	'label'     => __('Related Contents', TM_LANGUAGE_DOMAIN),
	'name'      => '',
	'type'      => 'tab',
	'placement' => 'left',
);

$field_related_title = array(
	'key'           => 'field_641c3bcbb95b6',
	'label'         => __('Title', TM_LANGUAGE_DOMAIN),
	'name'          => 'w_related_contents_title',
	'type'          => 'text',
	'default_value' => __('Related Contents', TM_LANGUAGE_DOMAIN),
	'wrapper'       => array(
		'width' => '25',
	),
);

$field_related_num = array(
	'key'     => 'field_641c3be1b95b7',
	'label'   => __('Quantity', TM_LANGUAGE_DOMAIN),
	'name'    => 'w_related_contents_num',
	'type'    => 'number',
	'wrapper' => array(
		'width' => '25',
	),
);

$tab_latest_alerts = array(
	'key'       => 'field_w_latest_alerts',
	'label'     => __('Latest Alerts', TM_LANGUAGE_DOMAIN),
	'name'      => '',
	'type'      => 'tab',
	'placement' => 'left',
);

$field_latest_alerts_title = array(
	'key'           => 'field_w_latest_alerts_title',
	'label'         => __('Title', TM_LANGUAGE_DOMAIN),
	'name'          => 'w_latest_alerts_title',
	'type'          => 'text',
	'default_value' => __('Latest Alerts', TM_LANGUAGE_DOMAIN),
	'wrapper'       => array(
		'width' => '25',
	),
);

$field_latest_alerts_num = array(
	'key'     => 'field_w_latest_alerts_num',
	'label'   => __('Quantity', TM_LANGUAGE_DOMAIN),
	'name'    => 'w_latest_alerts_num',
	'type'    => 'number',
	'wrapper' => array(
		'width' => '25',
	),
);

$tab_banner = array(
	'key'       => 'field_641c3a386c3c7',
	'label'     => __('Banner Store', TM_LANGUAGE_DOMAIN),
	'name'      => '',
	'type'      => 'tab',
	'placement' => 'left',
);

$field_banner_title = array(
	'key'           => 'field_641c3cf94582b',
	'label'         => __('Title', TM_LANGUAGE_DOMAIN),
	'name'          => 'w_banner_title',
	'type'          => 'text',
	'default_value' => __('Visit Store', TM_LANGUAGE_DOMAIN),
	'wrapper'       => array(
		'width' => '25',
	),
);

$field_banner_subtitle = array(
	'key'           => 'field_641c3d1b4582c',
	'label'         => __('SubTitle', TM_LANGUAGE_DOMAIN),
	'name'          => 'w_banner_subtitle',
	'type'          => 'text',
	'default_value' => 'Check out our reports available for purchase',
	'wrapper'       => array(
		'width' => '25',
	),
);

$field_banner_link = array(
	'key'           => 'field_641c3d734582d',
	'label'         => __('Link', TM_LANGUAGE_DOMAIN),
	'name'          => 'w_banner_link',
	'type'          => 'link',
	'return_format' => 'array',
	'wrapper'       => array(
		'width' => '50',
	),
);

$field_banner_background = array(
	'key'           => 'field_641c3d994582e',
	'label'         => __('Background', TM_LANGUAGE_DOMAIN),
	'name'          => 'w_banner_background',
	'type'          => 'image',
	'return_format' => 'array',
	'preview_size'  => 'medium',
	'library'       => 'all',
	'wrapper'       => array(
		'width' => '25',
	),
);

$field_banner_link_product = array(
	'key'           => 'field_642694b7ee6da',
	'label'         => __('Link to Product', TM_LANGUAGE_DOMAIN),
	'name'          => 'w_banner_link_to_product',
	'type'          => 'true_false',
	'default_value' => 0,
	'ui'            => 1,
	'ui_on_text'    => '',
	'ui_off_text'   => '',
	'wrapper'       => array(
		'width' => '25',
	),
);

$field_banner_link_text = array(
	'key'     => 'field_64269da829b46',
	'label'   => __('Text - Link to Product', TM_LANGUAGE_DOMAIN),
	'name'    => 'w_banner_link_to_product_text',
	'type'    => 'text',
	'wrapper' => array(
		'width' => '25',
	),
);

$field_popup_product = array(
	'key'           => 'field_65a14275c5506',
	'label'         => __('PopUp to Product', TM_LANGUAGE_DOMAIN),
	'name'          => 'w_banner_popup_to_product',
	'type'          => 'true_false',
	'default_value' => 0,
	'ui'            => 1,
	'ui_on_text'    => '',
	'ui_off_text'   => '',
	'wrapper'       => array(
		'width' => '25',
	),
);

$tab_benefits = array(
	'key'       => 'field_641c3a386c42d',
	'label'     => __('Benefits', TM_LANGUAGE_DOMAIN),
	'name'      => '',
	'type'      => 'tab',
	'placement' => 'left',
);

$field_benefits_title = array(
	'key'           => 'field_641c3c3ab95b8',
	'label'         => __('Title', TM_LANGUAGE_DOMAIN),
	'name'          => 'w_benefits_title',
	'type'          => 'text',
	'default_value' => 'Our Key Benefits',
	'wrapper'       => array(
		'width' => '25',
	),
);

$field_benefits_text = array(
	'key'           => 'field_641c3c67b95b9',
	'label'         => __('Text', TM_LANGUAGE_DOMAIN),
	'name'          => 'w_benefits_text',
	'type'          => 'wysiwyg',
	'default_value' => '',
	'tabs'          => 'all',
	'toolbar'       => 'full',
	'media_upload'  => 1,
	'wrapper'       => array(
		'width' => '75',
	),
);

$tab_chat = array(
	'key'       => 'field_642bfe501024a',
	'label'     => __('Chat', TM_LANGUAGE_DOMAIN),
	'name'      => '',
	'type'      => 'tab',
	'placement' => 'left',
);

$field_chat_active = array(
	'key'           => 'field_642bff9fcef07',
	'label'         => __('Active', TM_LANGUAGE_DOMAIN),
	'name'          => 'w_chat_active',
	'type'          => 'true_false',
	'default_value' => 0,
	'ui'            => 1,
	'ui_on_text'    => '',
	'ui_off_text'   => '',
	'wrapper'       => array(
		'width' => '10',
	),
);

$tab_general = array(
	'key'       => 'field_64214b2265f4a',
	'label'     => __('General Settings', TM_LANGUAGE_DOMAIN),
	'name'      => '',
	'type'      => 'tab',
	'placement' => 'left',
);

$field_clone_settings = array(
	'key'          => 'field_64214b3265f4b',
	'label'        => __('Settings', TM_LANGUAGE_DOMAIN),
	'name'         => 'settings',
	'type'         => 'clone',
	'clone'        => array(
		'group_641c3565c1d13',
	),
	'display'      => 'seamless',
	'layout'       => 'block',
	'prefix_label' => 0,
	'prefix_name'  => 0,
);

acf_add_local_field_group(
	array(
		'key'                   => 'group_641c3a3842aea',
		'title'                 => 'Single Page Widgets',
		'fields'                => array(
			$tab_latest_contents,
			$field_latest_title,
			$field_latest_num,
			$tab_related_contents,
			$field_related_title,
			$field_related_num,
			$tab_latest_alerts,
			$field_latest_alerts_title,
			$field_latest_alerts_num,
			$tab_banner,
			$field_banner_title,
			$field_banner_subtitle,
			$field_banner_link,
			$field_banner_background,
			$field_banner_link_product,
			$field_banner_link_text,
			$field_popup_product,
			$tab_benefits,
			$field_benefits_title,
			$field_benefits_text,
			$tab_chat,
			$field_chat_active,
			$tab_general,
			$field_clone_settings,
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
