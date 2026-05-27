<?php
/**
 * ACF Options for Company News
 *
 * @package Tamarind_Company_News
 */

namespace tamarind_company_news;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Registers the ACF Options fields.
 */
if ( function_exists( 'acf_add_options_page' ) ) {
	add_action( 'acf/init', __NAMESPACE__ . '\register_company_news_acf_fields' );
}

/**
 * Registers the ACF fields for the Company News post type.
 *
 * This function adds a local field group with a URL field for related links.
 */
function register_company_news_acf_fields() {

	acf_add_local_field_group(
		array(
			'key'      => 'group_company_news_fields',
			'title'    => 'Company News Additional Fields',
			'fields'   => array(
				array(
					'key'               => 'field_company_news_url',
					'label'             => 'Related URL',
					'name'              => 'company_news_url',
					'type'              => 'url',
					'instructions'      => 'Add a relevant URL related to this news item',
					'required'          => 1,
					'conditional_logic' => 0,
					'default_value'     => '',
					'placeholder'       => 'https://example.com',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'company-news',
					),
				),
			),
		)
	);
}
