<?php
/**
 * ACF Options for Taxonomies.
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\taxonomies;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'acf/init', __NAMESPACE__ . '\register_taxonomies_acf_options' );
add_action( 'acf/init', __NAMESPACE__ . '\register_taxonomies_acf_fields' );
add_action( 'acf/init', __NAMESPACE__ . '\register_geography_acf_fields' );
add_action( 'acf/init', __NAMESPACE__ . '\register_topics_acf_fields' );
add_action( 'acf/init', __NAMESPACE__ . '\register_content_types_acf_fields' );

/**
 * Register ACF Options.
 */
function register_taxonomies_acf_options(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_options_page(
		array(
			'page_title'  => __( 'Taxonomies Preferences', TM_LANGUAGE_DOMAIN ),
			'menu_title'  => __( 'Taxonomies', TM_LANGUAGE_DOMAIN ),
			'menu_slug'   => 'tm-taxonomies',
			'capability'  => 'manage_options',
			'parent_slug' => 'tamarind-base',
			'redirect'    => false,
		)
	);

	$tab_geo_banners = array(
		'key'               => 'field_602a30e9582f3',
		'label'             => __('Geography pages banners', TM_LANGUAGE_DOMAIN),
		'name'              => '',
		'aria-label'        => '',
		'type'              => 'tab',
		'instructions'      => '',
		'required'          => 0,
		'conditional_logic' => 0,
		'placement'         => 'left',
		'endpoint'          => 0,
		'selected'          => 0,
	);

	$show_csb = array(
		'key'               => 'field_602a30f9582f4',
		'label'             => __('Country subscriptions banner', TM_LANGUAGE_DOMAIN),
		'name'              => 'csb_show',
		'aria-label'        => '',
		'type'              => 'true_false',
		'instructions'      => __('For testing, add to a country url ?newsubsbanner - example: https://ecigintelligence.com/geography/africa/?newsubsbanner', TM_LANGUAGE_DOMAIN),
		'required'          => 0,
		'conditional_logic' => 0,
		'message'           => __('Show country subscriptions banner under reports', TM_LANGUAGE_DOMAIN),
		'default_value'     => 1,
		'ui'                => 0,
	);

	$banner_bg_color = array(
		'key'               => 'field_602a3251582f5',
		'label'             => __('Banner bgcolor', TM_LANGUAGE_DOMAIN),
		'name'              => 'csb_bgcolor',
		'aria-label'        => '',
		'type'              => 'color_picker',
		'instructions'      => __('Use dark / intense color, because text is always white', TM_LANGUAGE_DOMAIN),
		'required'          => 0,
		'conditional_logic' => 0,
		'default_value'     => '#28285b',
		'enable_opacity'    => false,
		'return_format'     => 'string',
	);

	$banner_text = array(
		'key'               => 'field_602a3281582f6',
		'label'             => __('Banner text', TM_LANGUAGE_DOMAIN),
		'name'              => 'csb_banner_text',
		'aria-label'        => '',
		'type'              => 'text',
		'instructions'      => __('', TM_LANGUAGE_DOMAIN),
		'required'          => 0,
		'conditional_logic' => 0,
		'default_value'     => __('Interested in this region? Check our new country subscriptions', TM_LANGUAGE_DOMAIN),
	);

	$banner_cta_link = array(
		'key'               => 'field_602a32b8582f7',
		'label'             => __('Banner button link', TM_LANGUAGE_DOMAIN),
		'name'              => 'csb_banner_button_link',
		'aria-label'        => '',
		'type'              => 'link',
		'instructions'      => __('Text & link for button cta: example - "Discover" , https://ecigintelligence.com/country-subscriptions/?origin=geopagebanner', TM_LANGUAGE_DOMAIN),
		'required'          => 0,
		'conditional_logic' => 0,
		'return_format'     => 'array',
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'group_5ee128567f17b',
			'title'                 => __('Landing calls to action (non logged in public users)', TM_LANGUAGE_DOMAIN),
			'fields'                => array(
				$tab_geo_banners,
				$show_csb,
				$banner_bg_color,
				$banner_text,
				$banner_cta_link,
			),
			'location'              => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'tm-taxonomies',
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


/**
 * Register ACF fields for taxonomies (content types, geography, topics, post tags).
 */
function register_taxonomies_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$short_description = array(
		'key'               => 'field_5ea33076cab3c',
		'label'             => __('Short description (home)', TM_LANGUAGE_DOMAIN),
		'name'              => 'short_description',
		'aria-label'        => '',
		'type'              => 'textarea',
		'instructions'      => __('It shows in homepage blocks under the term title () and before the group of recent posts. -- Only for content types. --', TM_LANGUAGE_DOMAIN),
		'required'          => 0,
		'conditional_logic' => 0,
		'rows'              => 4,
	);

	$bottom_description = array(
		'key'               => 'field_5ea3311a87338',
		'label'             => __('Bottom description', TM_LANGUAGE_DOMAIN),
		'name'              => 'bottom_description',
		'aria-label'        => '',
		'type'              => 'wysiwyg',
		'instructions'      => __('It shows at the BOTTOM of the items list in the taxonomy/category page (Content type page, Geography page, Topic page etc.)', TM_LANGUAGE_DOMAIN),
		'required'          => 0,
		'conditional_logic' => 0,
		'default_value'     => '',
		'tabs'              => 'all',
		'toolbar'           => 'full',
		'media_upload'      => 0,
		'delay'             => 0,
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'group_5ea32e13b2f64',
			'title'                 => __('Taxonomy term additional texts', TM_LANGUAGE_DOMAIN),
			'fields'                => array(
				$short_description,
				$bottom_description,
			),
			'location'              => array(
				array(
					array(
						'param'    => 'taxonomy',
						'operator' => '==',
						'value'    => 'content_types',
					),
				),
				array(
					array(
						'param'    => 'taxonomy',
						'operator' => '==',
						'value'    => 'geography',
					),
				),
				array(
					array(
						'param'    => 'taxonomy',
						'operator' => '==',
						'value'    => 'topics',
					),
				),
				array(
					array(
						'param'    => 'taxonomy',
						'operator' => '==',
						'value'    => 'post_tag',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen'        => '',
			'active'                => true,
			'description'           => '',
			'show_in_rest'          => false,
		)
	);
}

/**
 * Register ACF fields for Geography terms.
 */
function register_geography_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$friendly_title = array(
		'key'               => 'field_5eb994d20e163',
		'label'             => __('Friendly title (for pages and enhance SEO)', TM_LANGUAGE_DOMAIN),
		'name'              => 'geo_friendly_title',
		'aria-label'        => '',
		'type'              => 'text',
		'instructions'      => __('Leave taxonomy term geography country name as it is, use this box to fill a more detailed title.', TM_LANGUAGE_DOMAIN),
		'required'          => 0,
		'conditional_logic' => 0,
		'placeholder'       => 'Exampla: E-Cigarettes in the United States',
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'group_5eb994c165a9d',
			'title'                 => __('Geography friendly title', TM_LANGUAGE_DOMAIN),
			'fields'                => array(
				$friendly_title,
			),
			'location'              => array(
				array(
					array(
						'param'    => 'taxonomy',
						'operator' => '==',
						'value'    => 'geography',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen'        => '',
			'active'                => true,
			'description'           => '',
			'show_in_rest'          => false,
		)
	);
}

/**
 * Register ACF fields for Topics terms.
 */
function register_topics_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$topic_show_in_edit_post = array(
		'key'               => 'field_624ffafbea56d',
		'label'             => __('Show in Edit Post', TM_LANGUAGE_DOMAIN),
		'name'              => 'topic_show_in_edit_post',
		'aria-label'        => '',
		'type'              => 'true_false',
		'instructions'      => __('', TM_LANGUAGE_DOMAIN),
		'required'          => 0,
		'conditional_logic' => 0,
		'default_value'     => 0,
		'ui'                => 1,
		'ui_on_text'        => 'Visible',
		'ui_off_text'       => 'Hidden',
	);

	$topic_module_landing = array(
		'key'               => 'field_64c1309e2b530',
		'label'             => __('Module Landing', TM_LANGUAGE_DOMAIN),
		'name'              => 'topic_module_landing',
		'aria-label'        => '',
		'type'              => 'true_false',
		'instructions'      => __('', TM_LANGUAGE_DOMAIN),
		'required'          => 0,
		'conditional_logic' => 0,
		'default_value'     => 0,
		'ui_on_text'        => 'Visible',
		'ui_off_text'       => 'Hidden',
		'ui'                => 1,
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'group_624ffae40359f',
			'title'                 => __('Topics preferences', TM_LANGUAGE_DOMAIN),
			'fields'                => array(
				$topic_show_in_edit_post,
				$topic_module_landing,
			),
			'location'              => array(
				array(
					array(
						'param'    => 'taxonomy',
						'operator' => '==',
						'value'    => 'topics',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen'        => '',
			'active'                => true,
			'description'           => '',
			'show_in_rest'          => 0,
		)
	);
}

/**
 * Register ACF fields for Content Types terms.
 */
function register_content_types_acf_fields(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$short_title = array(
		'key'               => 'field_554213dd57832',
		'label'             => __('Short Title', TM_LANGUAGE_DOMAIN),
		'name'              => 'short_title',
		'aria-label'        => '',
		'type'              => 'text',
		'instructions'      => __('', TM_LANGUAGE_DOMAIN),
		'required'          => 0,
		'conditional_logic' => 0,
		'wrapper'           => array(
			'width' => '33',
		),
		'readonly'          => 0,
	);

	$background_color = array(
		'key'               => 'field_6335535e86a02',
		'label'             => __('Background Color', TM_LANGUAGE_DOMAIN),
		'name'              => 'background_color',
		'aria-label'        => '',
		'type'              => 'color_picker',
		'instructions'      => __('', TM_LANGUAGE_DOMAIN),
		'required'          => 0,
		'conditional_logic' => 0,
		'wrapper'           => array(
			'width' => '33',
		),
		'default_value'     => '',
		'enable_opacity'    => 0,
		'return_format'     => 'string',
	);

	$text_color = array(
		'key'               => 'field_633554f5d3552',
		'label'             => __('Text Color', TM_LANGUAGE_DOMAIN),
		'name'              => 'text_color',
		'aria-label'        => '',
		'type'              => 'color_picker',
		'instructions'      => '',
		'required'          => 0,
		'conditional_logic' => 0,
		'wrapper'           => array(
			'width' => '33',
		),
		'default_value'     => '',
		'enable_opacity'    => 0,
		'return_format'     => 'string',
	);

	$allow_pdf_download = array(
		'key'               => 'field_63c52f8da43f3',
		'label'             => __('Allow PDF download', TM_LANGUAGE_DOMAIN),
		'name'              => 'allow_pdf_download_content_type',
		'aria-label'        => '',
		'type'              => 'true_false',
		'instructions'      => '',
		'required'          => 0,
		'conditional_logic' => 0,
		'default_value'     => 0,
		'ui_on_text'        => '',
		'ui_off_text'       => '',
		'ui'                => 1,
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'group_5bb33aea1c3f6',
			'title'                 => __('Content Type preferences', TM_LANGUAGE_DOMAIN),
			'fields'                => array(
				$short_title,
				$background_color,
				$text_color,
				$allow_pdf_download,
			),
			'location'              => array(
				array(
					array(
						'param'    => 'taxonomy',
						'operator' => '==',
						'value'    => 'content_types',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen'        => '',
			'active'                => true,
			'description'           => '',
			'show_in_rest'          => 0,
		)
	);
}
