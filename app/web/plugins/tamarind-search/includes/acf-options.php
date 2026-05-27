<?php
/**
 * ACF Options for Tamarind Search
 *
 * @package Tamarind_Search
 */

namespace tamarind_search;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the ACF Options fields.
 */
if ( function_exists( 'acf_add_options_page' ) ) {
	add_filter( 'acf/init', __NAMESPACE__ . '\register_acf_field_options' );
	add_action( 'acf/init', __NAMESPACE__ . '\register_url_settings_tab_options' );
	add_action( 'acf/options_page/save', __NAMESPACE__ . '\flush_rewrite_rules_options_save', 10, 2 );
	add_action( 'acf/init', __NAMESPACE__ . '\create_user_saved_searches_acf_field' );
}

/**
 * Flush rewrite rules on the save options page.
 *
 * @param int|string $post_id The post-ID.
 * @param string     $menu_slug The menu slug.
 */
function flush_rewrite_rules_options_save( int|string $post_id, string $menu_slug ): void {
	if ( 'tm-favourites-settings' === $menu_slug ) {
		delete_option( 'rewrite_rules' );
	}

	if ( 'tm-search-settings' === $menu_slug ) {
		global $wpdb;
		$option_prefix     = 'tm_search_redirect_%';
		$options_to_delete = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
				$option_prefix
			)
		);

		// Loop through the found options and delete them.
		foreach ( $options_to_delete as $option_name ) {
			delete_option( $option_name );
		}

		// Get all rows for 'countries' and 'search_content_types' from ACF.
		$countries     = get_field( 'countries', 'option' ) ? get_field( 'countries', 'option' ) : array();
		$content_types = get_field( 'search_content_types', 'option' ) ? get_field( 'search_content_types', 'option' ) : array();
		$topics        = get_field( 'search_topics', 'option' ) ? get_field( 'search_topics', 'option' ) : array();
		$pages         = get_field( 'search_pages', 'option' ) ? get_field( 'search_pages', 'option' ) : array();

		check_acf_field_for_match( $countries, 'country', 'keywords', 'geography' );
		check_acf_field_for_match( $content_types, 'search_content_type', 'search_content_types_keywords', 'content_types' );
		check_acf_field_for_match( $topics, 'search_topic', 'search_topics_keywords', 'topics' );
		check_acf_field_for_match( $pages, 'search_page', 'search_pages_keywords' );
	}
}

/**
 * Set the options for the select field.
 *
 * @return array
 */
function set_select_url_options(): array {
	$choices = array(
		'saved_searches' => __( 'Saved searches', 'tm-search' ),
	);

	return array( '' => __( 'Select Key', 'tm-search' ) ) + $choices;
}


/**
 * Register the ACF fields for the URL repeater.
 *
 * @return array
 */
function register_url_repeater_settings(): array {
	$field_url_key_slug = array(
		'key'     => 'tm_search_key_url',
		'label'   => __( 'Key', 'tm-search' ),
		'name'    => 'search_url_key_slug',
		'type'    => 'select',
		'choices' => set_select_url_options(),
		'wrapper' => array(
			'width' => '33',
		),
	);

	$field_url_slug = array(
		'key'     => 'tm_search_url_value',
		'label'   => __( 'URL', 'tm-search' ),
		'name'    => 'search_url_slug',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '33',
		),
	);

	$field_url_label = array(
		'key'     => 'tm_search_url_label',
		'label'   => __( 'Label', 'tm-search' ),
		'name'    => 'search_url_label',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '33',
		),
	);

	return array(
		'key'          => 'tm_search_url_settings',
		'label'        => __( 'URL', 'tm-search' ),
		'name'         => 'search_url_settings',
		'type'         => 'repeater',
		'parent'       => 'group_tm_url_settings',
		'layout'       => 'block',
		'sub_fields'   => array(
			$field_url_key_slug,
			$field_url_slug,
			$field_url_label,
		),
		'button_label' => __( 'Add option', 'tm-search' ),
	);
}

/**
 * Registers the ACF fields for Countries Redirects
 *
 * @return array
 */
function register_repeater_countries_redirect(): array {
	$field_country = array(
		'key'                  => 'field_56a9d893ebd06',
		'label'                => __( 'Country', 'tm-search' ),
		'name'                 => 'country',
		'aria-label'           => '',
		'type'                 => 'taxonomy',
		'instructions'         => '',
		'required'             => 0,
		'conditional_logic'    => 0,
		'wrapper'              => array(
			'width' => '',
			'class' => '',
			'id'    => '',
		),
		'taxonomy'             => 'geography',
		'field_type'           => 'select',
		'allow_null'           => 0,
		'return_format'        => 'id',
		'multiple'             => 0,
		'add_term'             => 1,
		'load_terms'           => 0,
		'save_terms'           => 0,
		'parent_repeater'      => 'field_56a9d87febd05',
		'bidirectional_target' => array(),
	);

	$field_keywords = array(
		'key'               => 'field_56a9d8cfebd07',
		'label'             => 'Keywords',
		'name'              => 'keywords',
		'aria-label'        => '',
		'type'              => 'text',
		'instructions'      => __( 'Enter countries seperate by , whithout blanks', 'tm-search' ),
		'required'          => 0,
		'conditional_logic' => 0,
		'wrapper'           => array(
			'width' => '',
			'class' => '',
			'id'    => '',
		),
		'default_value'     => '',
		'placeholder'       => '',
		'prepend'           => '',
		'append'            => '',
		'formatting'        => 'html',
		'maxlength'         => '',
		'parent_repeater'   => 'field_56a9d87febd05',
	);

	return array(
		'key'               => 'field_56a9d87febd05',
		'label'             => 'Search redirect by Countries',
		'name'              => 'countries',
		'aria-label'        => '',
		'type'              => 'repeater',
		'instructions'      => __( 'This filter, overrides search terms and redirects them to a speciffic page, that shows contents filtered by country.', 'tm-search' ),
		'required'          => 0,
		'conditional_logic' => 0,
		'wrapper'           => array(
			'width' => '',
			'class' => '',
			'id'    => '',
		),
		'row_min'           => '',
		'row_limit'         => '',
		'layout'            => 'table',
		'button_label'      => 'Add Row',
		'min'               => 0,
		'max'               => 0,
		'collapsed'         => '',
		'rows_per_page'     => 20,
		'sub_fields'        => array(
			$field_country,
			$field_keywords,
		),
	);
}


/**
 * Registers the ACF fields for content_types Redirects
 *
 * @return array
 */
function register_repeater_content_types_redirect(): array {
	$field_content_type = array(
		'key'                  => 'field_635bd17f489d9',
		'label'                => __( 'ContentType', 'tm-search' ),
		'name'                 => 'search_content_type',
		'aria-label'           => '',
		'type'                 => 'taxonomy',
		'instructions'         => '',
		'required'             => 0,
		'conditional_logic'    => 0,
		'wrapper'              => array(
			'width' => '',
			'class' => '',
			'id'    => '',
		),
		'taxonomy'             => 'content_types',
		'add_term'             => 0,
		'save_terms'           => 0,
		'load_terms'           => 0,
		'return_format'        => 'id',
		'field_type'           => 'select',
		'allow_null'           => 0,
		'multiple'             => 0,
		'parent_repeater'      => 'field_635bd17f489d8',
		'bidirectional_target' => array(),
	);

	$field_keywords = array(
		'key'               => 'field_635bd17f489da',
		'label'             => __( 'Keywords', 'tm-search' ),
		'name'              => 'search_content_types_keywords',
		'aria-label'        => '',
		'type'              => 'text',
		'instructions'      => __( 'Enter content_types separated by , (without blanks, format CSV)', 'tm-search' ),
		'required'          => 0,
		'conditional_logic' => 0,
		'wrapper'           => array(
			'width' => '',
			'class' => '',
			'id'    => '',
		),
		'default_value'     => '',
		'readonly'          => 0,
		'maxlength'         => '',
		'placeholder'       => '',
		'prepend'           => '',
		'append'            => '',
		'parent_repeater'   => 'field_635bd17f489d8',
	);

	return array(
		'key'               => 'field_635bd17f489d8',
		'label'             => __( 'Search redirect by Content Types', 'tm-search' ),
		'name'              => 'search_content_types',
		'aria-label'        => '',
		'type'              => 'repeater',
		'instructions'      => __( 'This filter, overrides search terms and redirects them to a specific page, that shows contents filtered by content_type.', 'tm-search' ),
		'required'          => 0,
		'conditional_logic' => 0,
		'wrapper'           => array(
			'width' => '',
			'class' => '',
			'id'    => '',
		),
		'layout'            => 'table',
		'pagination'        => 0,
		'min'               => 0,
		'max'               => 0,
		'collapsed'         => '',
		'button_label'      => __( 'Add Row', 'tm-search' ),
		'rows_per_page'     => 20,
		'sub_fields'        => array(
			$field_content_type,
			$field_keywords,
		),
	);
}

/**
 * Registers the ACF fields for topics Redirects
 *
 * @return array
 */
function register_repeater_topics_redirect(): array {
	$field_search_topic = array(
		'key'                  => 'tm_search_topic',
		'label'                => __( 'Topic', 'tm-search' ),
		'name'                 => 'search_topic',
		'aria-label'           => '',
		'type'                 => 'taxonomy',
		'instructions'         => '',
		'required'             => 0,
		'conditional_logic'    => 0,
		'wrapper'              => array(
			'width' => '',
			'class' => '',
			'id'    => '',
		),
		'taxonomy'             => 'topics',
		'add_term'             => 0,
		'save_terms'           => 0,
		'load_terms'           => 0,
		'return_format'        => 'id',
		'field_type'           => 'select',
		'allow_null'           => 0,
		'multiple'             => 0,
		'parent_repeater'      => 'tm_topics_repeater',
		'bidirectional_target' => array(),
	);

	$field_keywords = array(
		'key'               => 'tm_search_topics_keywords',
		'label'             => __( 'Keywords', 'tm-search' ),
		'name'              => 'search_topics_keywords',
		'aria-label'        => '',
		'type'              => 'text',
		'instructions'      => __( 'Enter topics separated by , (without blanks, format CSV)', 'tm-search' ),
		'required'          => 0,
		'conditional_logic' => 0,
		'wrapper'           => array(
			'width' => '',
			'class' => '',
			'id'    => '',
		),
		'default_value'     => '',
		'readonly'          => 0,
		'maxlength'         => '',
		'placeholder'       => '',
		'prepend'           => '',
		'append'            => '',
		'parent_repeater'   => 'tm_topics_repeater',
	);

	return array(
		'key'               => 'tm_topics_repeater',
		'label'             => __( 'Search redirect by topics', 'tm-search' ),
		'name'              => 'search_topics',
		'aria-label'        => '',
		'type'              => 'repeater',
		'instructions'      => __( 'This filter, overrides search terms and redirects them to a specific page, that shows contents filtered by topic.', 'tm-search' ),
		'required'          => 0,
		'conditional_logic' => 0,
		'wrapper'           => array(
			'width' => '',
			'class' => '',
			'id'    => '',
		),
		'layout'            => 'table',
		'pagination'        => 0,
		'min'               => 0,
		'max'               => 0,
		'collapsed'         => '',
		'button_label'      => __( 'Add Row', 'tm-search' ),
		'rows_per_page'     => 20,
		'sub_fields'        => array(
			$field_search_topic,
			$field_keywords,
		),
	);
}

/**
 * Registers the ACF fields for page Redirects
 *
 * @return array
 */
function register_repeater_page_redirect(): array {
	$field_search_page = array(
		'key'                  => 'tm_search_page',
		'label'                => __( 'Page', 'tm-search' ),
		'name'                 => 'search_page',
		'aria-label'           => '',
		'type'                 => 'post_object',
		'instructions'         => '',
		'required'             => 0,
		'conditional_logic'    => 0,
		'wrapper'              => array(
			'width' => '',
			'class' => '',
			'id'    => '',
		),
		'post_type'            => array( 'page', 'post' ),
		'return_format'        => 'id',
		'field_type'           => 'select',
		'allow_null'           => 0,
		'multiple'             => 0,
		'parent_repeater'      => 'tm_pages_repeater',
		'bidirectional_target' => array(),
	);

	$field_keywords = array(
		'key'               => 'tm_search_pages_keywords',
		'label'             => __( 'Keywords', 'tm-search' ),
		'name'              => 'search_pages_keywords',
		'aria-label'        => '',
		'type'              => 'text',
		'instructions'      => __( 'Enter words separated by , (without blanks, format CSV)', 'tm-search' ),
		'required'          => 0,
		'conditional_logic' => 0,
		'wrapper'           => array(
			'width' => '',
			'class' => '',
			'id'    => '',
		),
		'default_value'     => '',
		'readonly'          => 0,
		'maxlength'         => '',
		'placeholder'       => '',
		'prepend'           => '',
		'append'            => '',
		'parent_repeater'   => 'tm_pages_repeater',
	);

	return array(
		'key'               => 'tm_pages_repeater',
		'label'             => __( 'Search redirect by word', 'tm-search' ),
		'name'              => 'search_pages',
		'aria-label'        => '',
		'type'              => 'repeater',
		'instructions'      => __( 'This filter, overrides search terms and redirects them to a specific page or post.', 'tm-search' ),
		'required'          => 0,
		'conditional_logic' => 0,
		'wrapper'           => array(
			'width' => '',
			'class' => '',
			'id'    => '',
		),
		'layout'            => 'table',
		'pagination'        => 0,
		'min'               => 0,
		'max'               => 0,
		'collapsed'         => '',
		'button_label'      => __( 'Add Row', 'tm-search' ),
		'rows_per_page'     => 20,
		'sub_fields'        => array(
			$field_search_page,
			$field_keywords,
		),
	);
}

/**
 * Registers the ACF fields for Options Page.
 *
 * @return void
 */
function register_acf_field_options(): void {
	acf_add_options_sub_page(
		array(
			'page_title'  => __( 'Search', 'tm-search' ),
			'menu_title'  => __( 'Search', 'tm-search' ),
			'menu_slug'   => 'tm-search-settings',
			'capability'  => 'manage_options',
			'parent_slug' => 'tamarind-base',
			'redirect'    => false,
		)
	);

	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	// Tab "Countries Redirects".
	$countries_tab = array(
		'key'       => 'field_tm_countries_tab',
		'label'     => __( 'Countries redirects', 'tm-search' ),
		'name'      => 'tm_countries_tab',
		'type'      => 'tab',
		'placement' => 'left',
	);

	// Countries Redirects.
	$countries_redirects = register_repeater_countries_redirect();

	// Tab "Content Types Redirects".
	$content_types_tab = array(
		'key'       => 'field_tm_content_types_tab',
		'label'     => __( 'Content types redirects', 'tm-search' ),
		'name'      => 'tm_content_types_tab',
		'type'      => 'tab',
		'placement' => 'left',
	);

	// Content Types Redirects.
	$content_types_redirects = register_repeater_content_types_redirect();

	$topics_tab = array(
		'key'       => 'field_tm_topics_tab',
		'label'     => __( 'Topics redirects', 'tm-search' ),
		'name'      => 'tm_topics_tab',
		'type'      => 'tab',
		'placement' => 'left',
	);

	$topics_redirects = register_repeater_topics_redirect();

	$pages_tab = array(
		'key'       => 'field_tm_pages_tab',
		'label'     => __( 'Pages redirects', 'tm-search' ),
		'name'      => 'tm_pages_tab',
		'type'      => 'tab',
		'placement' => 'left',
	);

	$pages_redirects = register_repeater_page_redirect();

	// Register the ACF fields.
	$acf_fields = array(
		$countries_tab,
		$countries_redirects,
		$content_types_tab,
		$content_types_redirects,
		$topics_tab,
		$topics_redirects,
		$pages_tab,
		$pages_redirects,
	);

	acf_add_local_field_group(
		array(
			'key'      => 'tm_group_search_options',
			'title'    => __( 'Search', 'tm-search' ),
			'fields'   => $acf_fields,
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'tm-search-settings',
					),
				),
			),
			'style'    => 'default',
		)
	);
}

/**
 * Registers the Search Tab for URL Settings.
 *
 * @return void
 */
function register_url_settings_tab_options(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	// Tab "Search".
	$search_tab = array(
		'key'       => 'field_tm_search_tab',
		'label'     => __( 'Search', 'tm-search' ),
		'name'      => 'tm_search_tab',
		'type'      => 'tab',
		'placement' => 'left',
		'parent'    => 'group_tm_url_settings', // Asociar al grupo de campos base.
	);

	// Message for "Search".
	$message_field_url_search = array(
		'key'     => 'field_tamarind_message_url_search',
		'name'    => 'message_url_search',
		'type'    => 'message',
		'message' => __( '<h3>Config dynamic URL</h3>Define URLs for <b>Search page</b>.', 'tm-search' ),
		'parent'  => 'group_tm_url_settings', // Asociar al grupo de campos base.
	);

	// URL Settings.
	$urls_settings = register_url_repeater_settings();

	// Register the Search Tab fields for URL Settings.
	acf_add_local_field( $search_tab );
	acf_add_local_field( $message_field_url_search );
	acf_add_local_field( $urls_settings );
}

/**
 * Create ACF Repeater Field for User Saved Searches.
 *
 * This function programmatically creates an ACF repeater field group for users
 * to store their saved searches. The repeater field includes subfields for
 * `search_word`, `search_url`, and `last_search`.
 *
 * @return void
 */
function create_user_saved_searches_acf_field(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	// Saved Searches Repeater Field.
	$field_search_word = array(
		'key'     => 'field_search_word',
		'label'   => __( 'Search Word', 'tm-search' ),
		'name'    => 'search_word',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '25',
		),
	);

	$field_search_url = array(
		'key'     => 'field_search_url',
		'label'   => __( 'Search URL', 'tm-search' ),
		'name'    => 'search_url',
		'type'    => 'url',
		'wrapper' => array(
			'width' => '25',
		),
	);

	$field_search_counter = array(
		'key'     => 'field_search_counter',
		'label'   => __( 'Search Counter', 'tm-search' ),
		'name'    => 'search_counter',
		'type'    => 'text',
		'wrapper' => array(
			'width' => '25',
		),
	);

	$field_last_search = array(
		'key'            => 'field_last_search',
		'label'          => __( 'Last Search Date', 'tm-search' ),
		'name'           => 'last_search',
		'type'           => 'date_time_picker',
		'display_format' => 'Y-m-d H:i:s',
		'return_format'  => 'Y-m-d H:i:s',
		'wrapper'        => array(
			'width' => '25',
		),
	);

	$saved_searches_repeater = array(
		'key'          => 'field_saved_searches',
		'label'        => __( 'Saved Searches', 'tm-search' ),
		'name'         => 'saved_searches',
		'type'         => 'repeater',
		'layout'       => 'block',
		'button_label' => __( 'Add Search', 'tm-search' ),
		'sub_fields'   => array(
			$field_search_word,
			$field_search_url,
			$field_search_counter,
			$field_last_search,
		),
	);

	// Define the ACF field group.
	acf_add_local_field_group(
		array(
			'key'                   => 'group_user_saved_searches',
			'title'                 => __( 'User Saved Searches', 'tm-search' ),
			'fields'                => array(
				$saved_searches_repeater,
			),
			'location'              => array(
				array(
					array(
						'param'    => 'user_form',
						'operator' => '==',
						'value'    => 'all',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'layout'                => 'block',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'hide_on_screen'        => '',
			'active'                => true,
			'description'           => '',
		)
	);
}
