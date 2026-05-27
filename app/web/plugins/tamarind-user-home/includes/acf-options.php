<?php
/**
 * ACF Options for Tamarind User Home
 *
 * @package Tamarind_UserHome
 */

namespace tamarind_userhome;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the ACF Options fields.
 */
if ( function_exists( 'acf_add_options_page' ) ) {
	add_action( 'acf/init', __NAMESPACE__ . '\register_acf_field_options' );
	add_filter( 'acf/load_field/key=field_trending_content_data', __NAMESPACE__ . '\acf_load_field_trending_content_data' );
}

/**
 * Defines the common layouts for Flexible fields.
 *
 * @return array
 */
function get_flexible_layouts() {
	$layouts = array();

	// Define layouts.
	$layout_types = array(
		'my_favourites'    => 'My Favourites',
		'recommendations'  => 'Recommendations',
		'trending_content' => 'Trending Content',
		'latest_content'   => 'Latest Content',
		'upcoming_events'  => 'Upcoming Events',
		'company_news'     => 'Company News',
	);

	foreach ( $layout_types as $name => $label ) {

		// Common fields for ALL modules.
		$field_title = array(
			'key'     => 'field_' . $name . '_title',
			'label'   => 'Title',
			'name'    => 'title',
			'type'    => 'text',
			'wrapper' => array( 'width' => '50%' ),
		);

		$field_limit = array(
			'key'     => 'field_' . $name . '_number_of_items',
			'label'   => 'Number of Items',
			'name'    => 'number_of_items',
			'type'    => 'number',
			'wrapper' => array( 'width' => '50%' ),
		);

		$field_container_style = array(
			'key'           => 'field_' . $name . '_container_style',
			'label'         => 'Container Style',
			'name'          => 'container_style',
			'type'          => 'radio',
			'choices'       => array(
				'dark'  => 'Dark',
				'light' => 'Light',
			),
			'default_value' => 'light',
			'layout'        => 'horizontal',
			'wrapper'       => array( 'width' => '50%' ),
		);

		$field_item_style = array(
			'key'           => 'field_' . $name . '_item_style',
			'label'         => 'Item Style',
			'name'          => 'item_style',
			'type'          => 'radio',
			'choices'       => array(
				'dark'  => 'Dark',
				'light' => 'Light',
			),
			'default_value' => 'dark',
			'layout'        => 'horizontal',
			'wrapper'       => array( 'width' => '50%' ),
		);

		$sub_fields = array(
			$field_title,
			$field_limit,
			$field_container_style,
			$field_item_style,
		);

		// Add specific fields for each layout.
		switch ( $name ) {
			case 'my_favourites':
				$sub_fields = array_merge( $sub_fields, get_favourites_subfields() );
				break;

			case 'recommendations':
				break;

			case 'trending_content':
				$sub_fields = array_merge( $sub_fields, get_trending_content_subfields() );
				break;

			case 'latest_content':
				break;

			case 'upcoming_events':
				// Remove: $field_container_style and $field_item_style.
				$sub_fields = array_slice( $sub_fields, 0, -2 );
				$sub_fields = array_merge( $sub_fields, get_upcoming_events_subfields() );
				break;

			case 'company_news':
				break;
		}

		$layouts[] = array(
			'key'        => 'layout_' . $name,
			'name'       => $name,
			'label'      => $label,
			'display'    => 'block',
			'sub_fields' => $sub_fields,
		);
	}

	return $layouts;
}


/**
 * Retrieves the subfields for the My Favourites layout.
 *
 * @return array The array of subfields for the My Favourites layout.
 */
function get_favourites_subfields() {
	$sub_fields = array(
		array(
			'key'               => 'field_no_saved_favourites_message',
			'label'             => 'No saved favourites message',
			'name'              => 'no_saved_favourites_message',
			'type'              => 'wysiwyg',
			'instructions'      => 'Enter the message to display when there are no saved favourites.',
			'required'          => 0,
			'conditional_logic' => 0,
			'wrapper'           => array(
				'width' => '',
				'class' => '',
				'id'    => '',
			),
			'default_value'     => '',
			'tabs'              => 'all',
			'toolbar'           => 'basic',
			'delay'             => 0,
		),
	);

	return $sub_fields;
}

/**
 * Retrieves the subfields for the Trending Content layout.
 *
 * @return array The array of subfields for the Trending Content layout.
 */
function get_trending_content_subfields() {

	$sub_fields = array(
		array(
			'key'           => 'field_trending_content_data',
			'label'         => 'Data',
			'name'          => 'trending_content_data',
			'type'          => 'relationship',
			'required'      => 0,
			'post_type'     => array(
				'post',
			),
			'taxonomy'      => array(), // This will be set in the filter.
			'filters'       => array(
				'search',
				'taxonomy',
			),
			'return_format' => 'id',
			'elements'      => array(
				'featured_image',
			),
		),
	);

	return $sub_fields;
}


/**
 * Retrieves the subfields for the Upcoming Events layout.
 *
 * @return array The array of subfields for the Upcoming Events layout.
 */
function get_upcoming_events_subfields() {

	$sub_fields = array(
		array(
			'key'               => 'field_link_to_events_page',
			'label'             => 'Link to Events Page',
			'name'              => 'link_to_events_page',
			'type'              => 'url',
			'instructions'      => 'Enter the URL of the events page',
			'required'          => 0,
			'conditional_logic' => 0,
			'wrapper'           => array(
				'width' => '',
				'class' => '',
				'id'    => '',
			),
			'default_value'     => '',
			'placeholder'       => 'https://',
		),
	);

	return $sub_fields;
}


/**
 * Filters the ACF field for the Trending Content layout.
 *
 * @param array $field The field array.
 * @return array The modified field array.
 */
function acf_load_field_trending_content_data( $field ) {
	// Verify if the taxonomy exists.
	if ( ! taxonomy_exists( 'content_types' ) ) {
		return $field;
	}

	// Retrieve all terms from the taxonomy.
	$terms = get_terms(
		array(
			'taxonomy'   => 'content_types',
			'hide_empty' => true, // Include terms with posts.
		)
	);

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return $field;
	}

	// Build the array in the format ACF requires.
	$taxonomy_terms = array();
	foreach ( $terms as $term ) {
		$taxonomy_terms[] = 'content_types:' . $term->slug;
	}

	// Assign the terms to the field.
	$field['taxonomy'] = $taxonomy_terms;

	// Ensure the taxonomy filter is enabled.
	if ( ! in_array( 'taxonomy', $field['filters'], true ) ) {
		$field['filters'][] = 'taxonomy';
	}

	return $field;
}


/**
 * Registers the ACF fields for Options Page.
 *
 * @return void
 */
function register_acf_field_options() {

	// Add the options page.
	acf_add_options_sub_page(
		array(
			'page_title'  => 'User Home',
			'menu_title'  => 'User Home',
			'menu_slug'   => 'tm-userhome-settings',
			'capability'  => 'edit_posts',
			'parent_slug' => 'tamarind-base',
			'redirect'    => false,
		)
	);

	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	// Tab "User Home Modules".
	$userhome_tab = array(
		'key'       => 'field_tm_userhome_tab',
		'label'     => 'User Home Modules',
		'name'      => 'tm_userhome_tab',
		'type'      => 'tab',
		'placement' => 'left',
	);

	// Flexible field for the "User Home Modules" tab.
	$flexible_content_modules = array(
		'key'     => 'field_tm_userhome_flexible_content_modules',
		'label'   => 'User Home Modules',
		'name'    => 'tm_userhome_flexible_content_modules',
		'type'    => 'flexible_content',
		'layouts' => get_flexible_layouts(), // Get the layouts.
	);

	// Tab "User Home Sidebar".
	$userhome_sidebar_tab = array(
		'key'       => 'field_tm_userhome_sidebar_tab',
		'label'     => 'User Home Sidebar',
		'name'      => 'tm_userhome_sidebar_tab',
		'type'      => 'tab',
		'placement' => 'left',
	);

	// Flexible field for the "User Home Sidebar" tab.
	$flexible_content_sidebar = array(
		'key'     => 'field_tm_userhome_flexible_content_sidebar',
		'label'   => 'User Home Sidebar',
		'name'    => 'tm_userhome_flexible_content_sidebar',
		'type'    => 'flexible_content',
		'layouts' => get_flexible_layouts(), // Get the layouts.
	);

	// Register the ACF fields.
	$acf_fields = array(
		$userhome_tab,
		$flexible_content_modules,
		$userhome_sidebar_tab,
		$flexible_content_sidebar,
	);

	acf_add_local_field_group(
		array(
			'key'      => 'tm_group_userhome_options',
			'title'    => __( 'User Home', 'tm-userhome' ),
			'fields'   => $acf_fields,
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'tm-userhome-settings',
					),
				),
			),
			'style'    => 'default',
		)
	);
}
