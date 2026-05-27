<?php
/**
 * ACF Options for Regulatory Alerts.
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\alerts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'acf/init', __NAMESPACE__ . '\register_regulatory_alerts_acf_fields' );

/**
 * Register ACF fields.
 */
function register_regulatory_alerts_acf_fields(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_options_page(
		array(
			'page_title'  => __( 'Regulatory Alerts Preferences', TM_LANGUAGE_DOMAIN ),
			'menu_title'  => __( 'Regulatory Alerts', TM_LANGUAGE_DOMAIN ),
			'menu_slug'   => 'tm-alerts',
			'capability'  => 'manage_options',
			'parent_slug' => 'tamarind-base',
			'redirect'    => false,
		)
	);

	$show_alerts_tooltip = array(
		'key'           => 'field_6197904ceac89',
		'label'         => 'Show alerts TOOLTIP to all users',
		'name'          => 'show_alerts_tooltip',
		'type'          => 'true_false',
		'wrapper'       => array( 'width' => '32' ),
		'default_value' => 1,
	);

	$alerts_tooltip = array(
		'key'           => 'field_6196227925a5a',
		'label'         => 'Alerts tooltip New format text',
		'name'          => 'alerts_tooltip',
		'type'          => 'text',
		'wrapper'       => array( 'width' => '33' ),
		'default_value' => 'NEW FORMAT',
	);

	// Campo: alerts_title_landing
	$alerts_title_landing = array(
		'key'           => 'field_633c48b241a1a',
		'label'         => 'Alerts Title Landing',
		'name'          => 'alerts_title_landing',
		'type'          => 'text',
		'wrapper'       => array( 'width' => '33' ),
		'default_value' => 'DAILY NEWS ALERTS',
	);

	// Campo: alerts_new_format_tip
	$alerts_new_format_tip = array(
		'key'          => 'field_61961eaacaabf',
		'label'        => 'Alerts new format EDITORS TIP for users',
		'name'         => 'alerts_new_format_tip',
		'type'         => 'wysiwyg',
		'instructions' => 'Short explanation about the new alerts microformat, and where to find previous alerts archives (roundups)',
		'wrapper'      => array( 'width' => '50' ),
		'tabs'         => 'all',
		'toolbar'      => 'basic',
		'media_upload' => 0,
		'delay'        => 1,
	);

	// Campo: alerts_archive_desc
	$alerts_archive_desc = array(
		'key'          => 'field_61939ec0ddc91',
		'label'        => 'Alerts archive page description',
		'name'         => 'alerts_archive_desc',
		'type'         => 'wysiwyg',
		'instructions' => 'Description for main alerts page archive (not repeated when filtered by geography)',
		'wrapper'      => array( 'width' => '50' ),
		'tabs'         => 'all',
		'toolbar'      => 'basic',
		'media_upload' => 0,
		'delay'        => 1,
	);

	// Campo: show_alerts_sidebar_geo_filter
	$show_alerts_sidebar_geo_filter = array(
		'key'           => 'field_61979088eac8a',
		'label'         => 'Show alerts archive sidebar geography filter',
		'name'          => 'show_alerts_sidebar_geo_filter',
		'type'          => 'true_false',
		'instructions'  => 'Shows a geographies filter on alerts archive',
		'wrapper'       => array( 'width' => '25' ),
		'default_value' => 0,
	);

	// Campo: show_alerts_slider
	$show_alerts_slider = array(
		'key'           => 'field_617be22db7185',
		'label'         => 'Show alerts slider on header',
		'name'          => 'show_alerts_slider',
		'type'          => 'true_false',
		'wrapper'       => array( 'width' => '25' ),
		'default_value' => 0,
	);

	// Campo: alerts_to_show_on_home_block
	$alerts_to_show_on_home_block = array(
		'key'     => 'field_617be206b7184',
		'label'   => 'Alerts to show on home / geo page featured block',
		'name'    => 'alerts_to_show_on_home_block',
		'type'    => 'number',
		'wrapper' => array( 'width' => '25' ),
	);

	// Campo: slider_alerts_recent
	$slider_alerts_recent = array(
		'key'     => 'field_617be260b7186',
		'label'   => 'Number of most recent alerts to show on slider',
		'name'    => 'slider_alerts_recent',
		'type'    => 'number',
		'wrapper' => array( 'width' => '25' ),
	);

	// Campo: alerts_tit_len
	$alerts_tit_len = array(
		'key'           => 'field_61838f1e101c0',
		'label'         => 'Alerts title trimming: Max charachter length (letters)',
		'name'          => 'alerts_tit_len',
		'type'          => 'number',
		'instructions'  => 'Set max letter length for alerts titlings on listings; consider only subscribers can read alerts full text content; also editors have control for each alert title;',
		'wrapper'       => array( 'width' => '50' ),
		'default_value' => 60,
	);

	// Campo: alerts_get_access_link
	$alerts_get_access_link = array(
		'key'           => 'field_619cdd4dd7f9c',
		'label'         => 'Get Access Link',
		'name'          => 'alerts_get_access_link',
		'type'          => 'link',
		'wrapper'       => array( 'width' => '50' ),
		'return_format' => 'array',
	);

	// Campo: image_for_alerts
	$image_for_alerts = array(
		'key'           => 'field_617be1d9b7183',
		'label'         => 'Image for alerts / show image',
		'name'          => 'image_for_alerts',
		'type'          => 'image',
		'instructions'  => 'Set to show an image for alerts block (Recents / Featured home / geo page)',
		'wrapper'       => array( 'width' => '33' ),
		'return_format' => 'array',
		'preview_size'  => 'medium',
		'library'       => 'all',
	);

	// Campo: alerts_thumb
	$alerts_thumb = array(
		'key'           => 'field_61838cd5d28e1',
		'label'         => 'Image for alerts (thumbnail)',
		'name'          => 'alerts_thumb',
		'type'          => 'image',
		'instructions'  => 'Default thumbnail image for alerts (listings, search results). New alerts post type does not use featured image.',
		'wrapper'       => array( 'width' => '33' ),
		'return_format' => 'array',
		'preview_size'  => 'medium',
		'library'       => 'all',
	);

	// Campo: alerts_icon
	$alerts_icon = array(
		'key'           => 'field_6184ff2137a6f',
		'label'         => 'Icon for alerts (icon)',
		'name'          => 'alerts_icon',
		'type'          => 'image',
		'instructions'  => 'Small icon',
		'wrapper'       => array( 'width' => '33' ),
		'return_format' => 'array',
		'preview_size'  => 'medium',
		'library'       => 'all',
	);

	// Campo: alert_desc_nologged
	$alert_desc_nologged = array(
		'key'          => 'field_67d8087000b4e',
		'label'        => 'Description for no-logged users',
		'name'         => 'alert_desc_nologged',
		'type'         => 'wysiwyg',
		'instructions' => 'Description for main alerts page archive (not repeated when filtered by geography)',
		'wrapper'      => array( 'width' => '50' ),
		'tabs'         => 'all',
		'toolbar'      => 'basic',
		'media_upload' => 0,
		'delay'        => 1,
	);

	// Campo: alert_video_nologged
	$alert_video_nologged = array(
		'key'     => 'field_67d808a800b4f',
		'label'   => 'Video for no-logged users',
		'name'    => 'alert_video_nologged',
		'type'    => 'oembed',
		'wrapper' => array( 'width' => '50' ),
	);

	// Registro del grupo
	acf_add_local_field_group(
		array(
			'key'                   => 'group_61eee8008c106',
			'title'                 => 'Alerts Home / Geo page PREFERENCES',
			'fields'                => array(
				$show_alerts_tooltip,
				$alerts_tooltip,
				$alerts_title_landing,
				$alerts_new_format_tip,
				$alerts_archive_desc,
				$show_alerts_sidebar_geo_filter,
				$show_alerts_slider,
				$alerts_to_show_on_home_block,
				$slider_alerts_recent,
				$alerts_tit_len,
				$alerts_get_access_link,
				$image_for_alerts,
				$alerts_thumb,
				$alerts_icon,
				$alert_desc_nologged,
				$alert_video_nologged,
			),
			'location'              => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'tm-alerts',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'field',
			'hide_on_screen'        => '',
			'active'                => true,
			'description'           => '',
		)
	);
}
