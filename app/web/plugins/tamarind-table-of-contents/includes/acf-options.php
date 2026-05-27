<?php
/**
 * ACF Fields Options for Tamarind Table of Contents.
 *
 * @package Tamarind_Table_of_Contents
 *
 * phpcs:disable WordPress.Files.FileName
 */

namespace tamarind_toc;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the ACF fields.
 */
if ( function_exists( 'acf_add_options_page' ) ) {
	add_filter( 'acf/init', __NAMESPACE__ . '\register_acf_fields' );
}

/**
 * Registers the `technology` ACF fields.
 */
function register_acf_fields() {

	$html_toc = array(
		'key'           => 'field_62d058daa2a0e',
		'label'         => __( 'Content TOC generated automatically based on the content of the post. It is updated when the post is saved', 'tamarind-forms' ),
		'name'          => 'res_tocs',
		'type'          => 'wysiwyg',
		'default_value' => '',
		'media_upload'  => 0,
		'toolbar'       => 'basic',
		'wrapper'       => array(
			'width' => '50',
		),
	);

	$show_toc = array(
		'key'     => 'field_62d058daa29d5',
		'label'   => __( 'Show TOC in content for non-subscribers', 'tamarind-toc' ),
		'name'    => 'show_toc',
		'type'    => 'true_false',
		'ui'      => 1,
		'wrapper' => array(
			'width' => '50',
		),
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_tamarind_toc',
			'title'    => __( 'Table of Contents', 'tamarind-toc' ),
			'fields'   => array(
				$html_toc,
				$show_toc,
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
			'style'    => 'block',
			'active'   => true,
		)
	);

}


