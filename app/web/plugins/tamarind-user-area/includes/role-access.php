<?php
/**
 * Role Access functions
 *
 * @package Tamarind_UserArea
 */

namespace tamarind_userarea;

defined( 'ABSPATH' ) || exit;

/**
 * Check if Post Type is accessible by the user based on their role.
 *
 * @param string $post_type CPT to check.
 * @return bool True if the user has access, false otherwise.
 */
function check_cpt_access( $post_type ) {
	$current_user = wp_get_current_user();

	$only_client_cpts = array(
		'company-news',
		'notifications',
	);

	$only_client_roles = array(
		'client',
		'administrator',
		'editor',
		'editor_manager',
		'author',
	);

	// Check if the post type is not in the list of client CPTs.
	if ( ! in_array( $post_type, $only_client_cpts, true ) ) {
		return true;
	}

	// Check if the user has one of the roles that can access the CPT.
	if ( in_array( $post_type, $only_client_cpts, true ) && array_intersect( $only_client_roles, $current_user->roles ) ) {
		return true;
	}

	return false;
}

/**
 * Redirect to homepage if the user does not have access to the CPT. Single or archive.
 *
 * @return bool
 */
function redirect_if_no_access_cpt() {
	if ( ! check_cpt_access( get_post_type() ) ) {
		wp_safe_redirect( esc_url( home_url( '/' ) ) );
		exit;
	}
	return false;
}
