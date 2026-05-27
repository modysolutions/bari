<?php
/**
 * Tamarind Templates Custom Lists Plugin Functions
 *
 * This file contains functions for registering and handling custom lists for special taxonomies
 * and post states in the Tamarind Templates WordPress plugin.
 *
 * @package TamarindTemplatesCustomLists
 */

namespace tamarind_templates_custom_lists;

defined( 'ABSPATH' ) || exit;

add_filter( 'theme_page_templates', __NAMESPACE__ . '\theme_page_templates' );
add_filter( 'template_include', __NAMESPACE__ . '\template_include' );
add_filter( 'display_post_states', __NAMESPACE__ . '\display_post_states', 10, 2 );
add_filter( 'pre_get_posts', __NAMESPACE__ . '\tamarind_modify_podcasts_archive_query' );

/**
 * Registers and merges custom templates with the existing templates.
 *
 * @param array $templates An array of existing templates.
 *
 * @return array The merged array of existing templates and custom templates.
 */
function theme_page_templates( array $templates ): array {
	$custom_templates = get_custom_templates();

	return array_merge( $templates, $custom_templates );
}

/**
 * Filters the template path and allows custom templates to be used for posts.
 *
 * @param string $template The path to the current template.
 *
 * @return string The filtered template path, potentially replaced with a custom template path.
 */
function template_include( string $template ): string {
	$qo = get_queried_object();

	if ( $qo instanceof \WP_Term ) {
		$content_types_term_templates = array(
			'podcasts' => 'taxonomy-content-types-podcasts.php',
		);

		$current_taxonomy = $qo->taxonomy;
		$current_term     = $qo->slug;
		if ( 'content_types' === $current_taxonomy && $current_term && array_key_exists( $current_term, $content_types_term_templates ) ) {
			$template_file = $content_types_term_templates[ $current_term ];
			$template_path = tamarind_templates_custom_lists_PATH . 'templates/' . $template_file;
			if ( file_exists( $template_path ) ) {
				return $template_path;
			}
		}
		return $template;
	}

	$post = get_post();
	if ( ! $post ) {
		return $template;
	}

	$page_template_slug   = get_post_meta( $post->ID, '_wp_page_template', true );
	$registered_templates = get_custom_templates();
	if ( array_key_exists( $page_template_slug, $registered_templates ) ) {
		$new_template = tamarind_templates_custom_lists_PATH . 'templates/' . $page_template_slug;
		if ( file_exists( $new_template ) ) {
			return $new_template;
		}
	}

	return $template;
}

/**
 * Returns a multidimensional array with all the custom templates we create. This needs to be populated by hand.
 *
 * @return array
 */
function get_custom_templates(): array {
	return array(
		'page-content-types.php' => __( 'Content types archive', 'tamarind-templates-custom-lists' ),
	);
}

/**
 * Modifies the display of post-states in the WordPress admin list table.
 * Adds a custom state for posts marked as content types archive pages.
 *
 * @param array    $post_states An array of post-state labels.
 * @param \WP_Post $post The post object currently being processed.
 *
 * @return array The modified array of post state labels.
 */
function display_post_states( array $post_states, \WP_Post $post ): array {
	if ( intval( get_post_meta( $post->ID, 'tm_is_content_types_archive_page', true ) ) === 1 ) {
		$post_states['tm_is_content_types_archive_page'] = __( 'Content types archive', 'tamarind-templates-custom-lists' );
	}

	return $post_states;
}

/**
 * Modifies the main query for the podcasts archive page to include specific post types and meta queries.
 *
 * @param \WP_Query $query The WP_Query instance (passed by reference).
 */
function tamarind_modify_podcasts_archive_query( \WP_Query $query ) : void {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}

	if ( $query->is_tax( 'content_types', 'podcasts' ) ) {
		$selected = function_exists( 'get_field' ) ? get_field( 'podcasts_included_content_types', 'option' ) : array();
		
		$posts_per_page = 20;
		$query->set( 'post_type', 'post' );
		$query->set( 'posts_per_page', $posts_per_page );
		$query->set( 'orderby', 'date' );
		$query->set( 'order', 'DESC' );

		if ( ! empty( $selected ) ) {
			// Add tax_query to filter by selected content types. Important relation 'OR' to include posts with any of the selected terms.
			$query->set(
				'tax_query',
				array(
					'relation' => 'OR',
					array(
						'taxonomy' => 'content_types',
						'field'    => 'term_id',
						'terms'    => $selected,
						'operator' => 'IN',
					),
				)
			);
		}
	}
}

function get_taxonomy_content_types_args( \WP_Term $term_content_type, array $location, array $topics = null) : array {
	$response = array(
		'post_type'      => 'post',
		'posts_per_page' => get_option( 'posts_per_page', '25' ),
		'post_status'    => 'publish',
		'tax_query'      => array(
			array(
				'taxonomy' => 'content_types',
				'field'    => 'slug',
				'terms'    => $term_content_type,
				'operator' => 'IN',
			),
		),
	);

	if ( ! empty( $location ) ) {
		$response['tax_query'][] = array(
			'taxonomy' => 'geography',
			'field'    => 'slug',
			'terms'    => $location,
			'operator' => 'IN',
		);
	}

	if ( ! empty( $topics ) ) {
		$response['tax_query'][] = array(
			'taxonomy' => 'topics',
			'field'    => 'slug',
			'terms'    => $topics,
			'operator' => 'IN',
		);
	}
	return $response;
}
