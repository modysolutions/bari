<?php

namespace tamarind_seo\functions;

defined( 'ABSPATH' ) || exit;

add_action( 'acf/options_page/save', __NAMESPACE__ . '\options_page_save', 10, 2 );

function options_page_save( int|string $post_id, string $menu_slug ): void {
	if ( 'options' !== $post_id && 'tamarind-seo-settings' !== $menu_slug ) {
		return;
	}

	$tamarind_seo_posts_active      = get_field( 'tamarind_seo_posts_active', 'option' );
	$tamarind_seo_taxonomies_active = get_field( 'tamarind_seo_taxonomies_active', 'option' );
	$entity_type_name               = $tamarind_seo_posts_active ? 'post' : ( $tamarind_seo_taxonomies_active ? 'taxonomy' : false );

	if ( false === $entity_type_name ) {
		return;
	}

	$entity_type_selector     = $entity_type_name === 'taxonomy' ? 'tamarind_seo_taxonomy_type' : false;
	$seo_title_selector       = $entity_type_name === 'taxonomy' ? 'tamarind_seo_taxonomy_seo_title' : false;
	$seo_description_selector = $entity_type_name === 'taxonomy' ? 'tamarind_seo_taxonomy_seo_description' : false;

	if ( $entity_type_selector === false || $seo_title_selector === false || $seo_description_selector === false ) {
		return;
	}

	$entity_type              = get_field( $entity_type_selector, 'option' );
	$seo_title_template       = get_field( $seo_title_selector, 'option' );
	$seo_description_template = get_field( $seo_description_selector, 'option' );
	$wp_seo_data              = array(
		'title' => $seo_title_template,
		'desc'  => $seo_description_template,
	);
	perform_bulk_update( $entity_type_name, $entity_type, $wp_seo_data );

	update_field( $entity_type_selector, false, 'option' );
	update_field( $seo_title_selector, false, 'option' );
	update_field( $seo_description_selector, false, 'option' );
	update_field( 'tamarind_seo_posts_active', false, 'option' );
	update_field( 'tamarind_seo_taxonomies_active', false, 'option' );
}

/**
 * Performs a bulk update of SEO title and description fields for posts or taxonomy terms
 * in the Yoast SEO indexable database table.
 *
 * @param string $content_type The type of content to update ('post' or 'taxonomy').
 * @param string $entity_name The specific post-type or taxonomy name to target.
 * @param array $wp_seo_data
 *
 * @return void
 */
function perform_bulk_update( string $content_type, string $entity_name, array $wp_seo_data ): void {
	if ( ! defined( 'WPSEO_PATH' ) ) {
		return;
	}

	if ( ! class_exists( 'WPSEO_Meta' ) ) {
		require_once WPSEO_PATH . 'inc/class-wpseo-meta.php';
	}
	if ( ! class_exists( 'WPSEO_Taxonomy_Meta' ) ) {
		require_once WPSEO_PATH . 'inc/options/class-wpseo-taxonomy-meta.php';
	}

	if ( 'taxonomy' === $content_type ) {
		$wpseo_taxonomy_meta = get_option( 'wpseo_taxonomy_meta' );
		if ( empty( $wpseo_taxonomy_meta ) ) {
			return;
		}
		$new_wpseo_taxonomy_meta = array_merge( $wpseo_taxonomy_meta, array( $entity_name => array() ) );;
		update_option( 'wpseo_taxonomy_meta', $new_wpseo_taxonomy_meta );
	}
}