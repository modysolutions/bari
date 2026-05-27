<?php
/**
 * ACF Options for Case Studies.
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\case_studies;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'acf/init', __NAMESPACE__ . '\register_case_studies_acf_fields' );

/**
 * Register ACF fields.
 */
function register_case_studies_acf_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	// Header Tab.
	$header_tab = array(
		'key'       => 'field_5d303908e2563',
		'label'     => 'Header',
		'name'      => '',
		'type'      => 'tab',
		'placement' => 'top',
		'endpoint'  => 0,
	);

	$header_bg = array(
		'key'           => 'field_5d30391ae2564',
		'label'         => 'Background-image',
		'name'          => 'header_bg',
		'type'          => 'image',
		'return_format' => 'url',
		'preview_size'  => 'medium',
		'library'       => 'all',
	);

	// Profile & Challenges Tab.
	$profile_challenges_tab = array(
		'key'       => 'field_5d3038122857c',
		'label'     => 'Profile & Challeges',
		'name'      => '',
		'type'      => 'tab',
		'placement' => 'top',
		'endpoint'  => 0,
	);

	$profile_title = array(
		'key'           => 'field_5d3038372857d',
		'label'         => 'Profile title',
		'name'          => 'profile_title',
		'type'          => 'text',
		'default_value' => 'Client Profile',
	);

	$profile_details = array(
		'key'          => 'field_5d3038512857e',
		'label'        => 'Profile contents',
		'name'         => 'profile_details',
		'type'         => 'textarea',
		'instructions' => 'Client profile details...',
		'rows'         => 6,
	);

	$challenges_title = array(
		'key'           => 'field_5d30386f2857f',
		'label'         => 'Challenges title',
		'name'          => 'challenges_title',
		'type'          => 'text',
		'default_value' => 'Client Profile',
	);

	$challenges_list = array(
		'key'           => 'field_5d30388a28580',
		'label'         => 'Challenges list',
		'name'          => 'challenges-list',
		'type'          => 'wysiwyg',
		'instructions'  => 'Use bullet list',
		'default_value' => '<ul>
                            <li>One - Edit this</li>
                            <li>Two - Edit this</li>
                            <li>Three - Edit this</li>
                            <li>Four - Edit this (...)</li>
                            </ul>',
		'tabs'          => 'all',
		'toolbar'       => 'full',
		'media_upload'  => 1,
	);

	// Solution Tab.
	$solution_tab = array(
		'key'       => 'field_5d31bf3ae3f5d',
		'label'     => 'Solution',
		'name'      => '',
		'type'      => 'tab',
		'placement' => 'top',
		'endpoint'  => 0,
	);

	$solution_bg = array(
		'key'           => 'field_5d35609cf7515',
		'label'         => 'Solution background-image',
		'name'          => 'solution_background-image',
		'type'          => 'image',
		'instructions'  => 'There is a mountain default image, but you can change it. Be sure it\'s enough dark.',
		'return_format' => 'url',
		'preview_size'  => 'medium',
	);

	$solution_contents = array(
		'key'           => 'field_5d31bf54e3f5e',
		'label'         => 'Ecigintelligence solution contents',
		'name'          => 'solution_contents',
		'type'          => 'wysiwyg',
		'instructions'  => 'Please, use HEADING 3 for titles',
		'default_value' => '<h3>Title</h3>
                            <p>Lorem ipsum dolor sit amet...</p>
                            <h3>Title</h3>
                            <p>Lorem ipsum dolor sit amet...</p>',
		'tabs'          => 'all',
		'toolbar'       => 'full',
		'media_upload'  => 1,
	);

	$form_clone = array(
		'key'     => 'field_66ed65da3a7a5',
		'label'   => 'Form',
		'name'    => 'mod_new_form',
		'type'    => 'clone',
		'clone'   => array( 'group_tamarind_forms_select_form' ),
		'display' => 'seamless',
		'layout'  => 'block',
	);

	$form_title = array(
		'key'           => 'field_5d31c00ae3f60',
		'label'         => 'Form title',
		'name'          => 'form_title',
		'type'          => 'text',
		'default_value' => 'Let\'s have a chat!',
	);

	$form_shortcode = array(
		'key'   => 'field_5d31bff7e3f5f',
		'label' => 'Form shortcode',
		'name'  => 'form_shortcode',
		'type'  => 'text',
	);

	// Related Items Tab.
	$related_items_tab = array(
		'key'       => 'field_5d31c091e3f63',
		'label'     => 'Related items',
		'name'      => '',
		'type'      => 'tab',
		'placement' => 'top',
		'endpoint'  => 0,
	);

	$related_contents_title = array(
		'key'           => 'field_5d31c01ee3f61',
		'label'         => 'Related contents title',
		'name'          => 'related_contents_title',
		'type'          => 'text',
		'default_value' => 'Read related content from ECigIntelligence',
	);

	$related_items = array(
		'key'           => 'field_5d31c04ae3f62',
		'label'         => 'Related items',
		'name'          => 'related_items',
		'type'          => 'relationship',
		'instructions'  => 'Up to 3 items',
		'post_type'     => array( 'case_studies' ),
		'filters'       => array( 'search', 'taxonomy' ),
		'elements'      => array( 'featured_image' ),
		'min'           => 1,
		'max'           => 3,
		'return_format' => 'object',
	);

	// Agrupar todos los campos
	acf_add_local_field_group(
		array(
			'key'                   => 'group_5d30380c29935',
			'title'                 => 'Case studies (customised analysis)',
			'fields'                => array(
				$header_tab,
				$header_bg,
				$profile_challenges_tab,
				$profile_title,
				$profile_details,
				$challenges_title,
				$challenges_list,
				$solution_tab,
				$solution_bg,
				$solution_contents,
				$form_clone,
				$form_title,
				$form_shortcode,
				$related_items_tab,
				$related_contents_title,
				$related_items,
			),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'case_studies',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		)
	);
}
