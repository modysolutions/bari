<?php
/**
 * ACF Fields Options for Tamarind templates.
 *
 * @package tamarind_templates_custom_lists
 */

namespace tamarind_templates_custom_lists;

defined( 'ABSPATH' ) || exit;

add_filter( 'acf/init', __NAMESPACE__ . '\register_acf_fields' );
add_action( 'acf/options_page/save', __NAMESPACE__ . '\acf_save_post', 10, 2 );

/**
 * Registers ACF fields and options page for Tamarind templates.
 *
 * @return void
 */
function register_acf_fields(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	acf_add_options_page(
		array(
			'page_title'  => __( 'Templates', 'tamarind-templates-custom-lists' ),
			'menu_title'  => __( 'Templates', 'tamarind-templates-custom-lists' ),
			'menu_slug'   => 'tm-templates',
			'capability'  => 'manage_options',
			'parent_slug' => 'tamarind-base',
			'redirect'    => false,
		)
	);

	$tab_field_archives = array(
		'key'       => 'field_tamarind_templates_custom_lists_tab_archives',
		'label'     => __( 'Archives', 'tamarind-templates-custom-lists' ),
		'name'      => 'tab_archives',
		'type'      => 'tab',
		'placement' => 'left',
	);

	$content_types_archive_page_field = array(
		'key'           => 'tm_templates_content_types_archive_page',
		'label'         => __( 'Content types archive page', 'tamarind-templates-custom-lists' ),
		'name'          => 'content_types_archive_page',
		'type'          => 'post_object',
		'post_type'     => 'page',
		'return_format' => 'id',
		'ui'            => 1,
	);

	$content_types_archive_includes_field = array(
		'key'             => 'tm_templates_content_types_includes',
		'label'           => __( 'Included content types', 'tamarind-templates-custom-lists' ),
		'name'            => 'content_types_archive_included_content_types',
		'type'            => 'taxonomy',
		'taxonomy'        => 'content_types',
		'load_save_terms' => 1,
		'field_type'      => 'multi_select',
		'ui'              => 1,
		'instructions'    => sprintf(
			'%s <br> %s',
			__( 'Select the content types that will be included in the content types archive page.', 'tamarind-templates-custom-lists' ),
			__( 'In none is selected, all content_types will be displayed.', 'tamarind-templates-custom-lists' )
		),
	);

	$tab_podcasts = array(
		'key'       => 'field_tamarind_templates_custom_lists_tab_podcasts',
		'label'     => __( 'Podcasts', 'tamarind-templates-custom-lists' ),
		'name'      => 'tab_podcasts',
		'type'      => 'tab',
		'placement' => 'left',
	);

	$content_types_podcasts_title_field = array(
		'key'           => 'tm_templates_podcasts_title',
		'label'         => __( 'Podcasts archive title', 'tamarind-templates-custom-lists' ),
		'name'          => 'podcasts_archive_title',
		'type'          => 'text',
		'default_value' => __( 'Podcasts', 'tamarind-templates-custom-lists' ),
	);

	$content_types_podcasts_includes_field = array(
		'key'             => 'tm_templates_podcasts_includes',
		'label'           => __( 'Included content types', 'tamarind-templates-custom-lists' ),
		'name'            => 'podcasts_included_content_types',
		'type'            => 'taxonomy',
		'taxonomy'        => 'content_types',
		'load_save_terms' => 1,
		'field_type'      => 'multi_select',
		'ui'              => 1,
		'instructions'    => sprintf(
			'%s <br> %s',
			__( 'Select the content types that will be included in the podcasts archive page.', 'tamarind-templates-custom-lists' ),
			__( 'In none is selected, all content_types with the "podcasts" slug will be displayed.', 'tamarind-templates-custom-lists' )
		),
	);

	acf_add_local_field_group(
		array(
			'key'                   => 'tm_templates_settings_group',
			'title'                 => __( 'Template settings', 'tamarind-templates-custom-lists' ),
			'fields'                => array(
				$tab_field_archives,
				$content_types_archive_page_field,
				$content_types_archive_includes_field,
				$tab_podcasts,
				$content_types_podcasts_title_field,
				$content_types_podcasts_includes_field,
			),
			'location'              => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'tm-templates',
					),
				),
			),
			'menu_order'            => 1,
			'position'              => 'normal',
			'label_placement'       => 'top',
			'instruction_placement' => 'field',
			'hide_on_screen'        => '',
			'active'                => true,
			'description'           => '',
			'show_in_rest'          => 0,
		)
	);
}

/**
 * Saves custom fields data when a post is saved in WordPress using Advanced Custom Fields (ACF).
 * Updates meta-fields for a designated archive page and removes them from any previously designated pages.
 *
 * @param int|string $post_id The ID of the post being saved. If 'options', handles global options for archive page settings.
 * @param string     $menu_slug Menu slug for the ACF options page.
 *
 * @return void
 */
function acf_save_post( int|string $post_id, string $menu_slug ): void {
	if ( 'options' !== $post_id && 'tm-templates' !== $menu_slug ) {
		return;
	}

	$selected_page_id = get_field( 'tm_templates_content_types_archive_page', 'option' );
	$args             = array(
		'post_type'      => 'page',
		'meta_key'       => 'tm_is_content_types_archive_page', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'meta_value'     => 1, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		'posts_per_page' => -1,
		'fields'         => 'ids',
	);
	$old_pages        = get_posts( $args );

	if ( ! empty( $old_pages ) ) {
		foreach ( $old_pages as $page_id ) {
			delete_post_meta( $page_id, 'tm_is_content_types_archive_page' );
		}
	}

	if ( ! empty( $selected_page_id ) ) {
		update_post_meta( $selected_page_id, 'tm_is_content_types_archive_page', 1 );
	}
}
