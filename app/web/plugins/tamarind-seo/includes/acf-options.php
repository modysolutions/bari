<?php
/**
 * ACF Field Group: Tamarind SEO Settings
 *
 * @package Tamarind_SEO
 */

namespace tamarind_seo\acf;

if ( function_exists( 'acf_add_local_field_group' ) ) {
	add_action( 'acf/init', __NAMESPACE__ . '\acf_init', 100 );
}

function acf_init(): void {
	$seo_taxonomies_tab = array(
		'key'       => 'field_tamarind_seo_taxonomies_tab',
		'label'     => __( 'Taxonomies', 'tamarind-seo' ),
		'type'      => 'tab',
		'placement' => 'left',
	);

	$seo_taxonomies_title = array(
		'key'       => 'field_tamarind_seo_taxonomies_title',
		'label'     => '',
		'type'      => 'message',
		'message'   => __( '<h3>Reset taxonomy SEO data</h3>', 'tamarind-seo' ),
		'new_lines' => 'wpautop',
		'esc_html'  => 0,
	);

	$seo_taxonomies_message = array(
		'key'       => 'field_tamarind_seo_taxonomies_message',
		'label'     => '',
		'type'      => 'message',
		'message'   => __( '<strong>Warning:</strong> This setting will delete all SEO information from all entities of the selected taxonomy.', 'tamarind-seo' ),
		'new_lines' => 'wpautop',
		'esc_html'  => 0,
	);

	$seo_taxonomies_active = array(
		'key'         => 'field_tamarind_seo_taxonomies_active',
		'label'       => __( 'Enable SEO update for taxonomies', 'tamarind-seo' ),
		'name'        => 'tamarind_seo_taxonomies_active',
		'type'        => 'true_false',
		'choices'     => array(
			'taxonomy' => __( 'On', 'tamarind-seo' ),
			'0'        => __( 'Off', 'tamarind-seo' ),
		),
		'ui'          => 1,
		'ui_on_text'  => __( 'On', 'tamarind-seo' ),
		'ui_off_text' => __( 'Off', 'tamarind-seo' ),
	);

	$seo_taxonomy_type = array(
		'key'           => 'field_tamarind_seo_taxonomy_type',
		'label'         => __( 'Select taxonomy', 'tamarind-seo' ),
		'placeholder'   => __( 'Select taxonomy', 'tamarind-seo' ),
		'name'          => 'tamarind_seo_taxonomy_type',
		'type'          => 'select',
		'choices'       => array(
			''                 => __( 'Select a taxonomy', 'tamarind-seo' ),
			'category'         => __( 'Categories', 'tamarind-seo' ),
			'tag'              => __( 'Tags', 'tamarind-seo' ),
			'regulatory_alert' => __( 'Regulatory alerts', 'tamarind-seo' ),
			'geography'        => __( 'Geographies', 'tamarind-seo' ),
			'topics'           => __( 'Topics', 'tamarind-seo' ),
		),
		'default_value' => 'category',
	);

	$fields = array(
		$seo_taxonomies_tab,
		$seo_taxonomies_title,
		$seo_taxonomies_message,
		$seo_taxonomies_active,
		$seo_taxonomy_type,
	);

	acf_add_options_sub_page(
		array(
			'page_title'  => __( 'SEO', 'tamarind-seo' ),
			'menu_title'  => __( 'SEO', 'tamarind-seo' ),
			'menu_slug'   => 'tamarind-seo-settings',
			'capability'  => 'manage_options',
			'parent_slug' => 'tamarind-base',
			'redirect'    => false,
		)
	);

	acf_add_local_field_group( array(
		'key'      => 'group_tm_seo_settings',
		'title'    => __( 'SEO Settings', 'tamarind-seo' ),
		'fields'   => $fields,
		'location' => array(
			array(
				array(
					'param'    => 'options_page',
					'operator' => '==',
					'value'    => 'tamarind-seo-settings',
				),
			),
		),
	) );
}