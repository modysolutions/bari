<?php
/**
 * Plugin Name:     Tamarind Forms
 * Plugin URI:      https://www.omitsis.com
 * Description:     Forms Settings for Tamarind.
 * Author:          Omitsis
 * Author URI:      https://www.omitsis.com
 * Text Domain:     tamarind-forms
 * Domain Path:     /languages
 * Version:         1.1.1
 *
 * @package         Tamarind_Forms
 */

namespace tamarind_forms;

/**
 * Version number of the omitsis base plugin
 */
$version = '1.1.1';
if(file_exists(dirname(__FILE__) . '/dist/css/tamarind-forms.min.css')) {
	$version = filemtime(dirname(__FILE__) . '/dist/css/tamarind-forms.min.css');
}
define('TM_FORMS_VERSION', $version);

defined( 'ABSPATH' ) || exit;

add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\tamarind_forms_enqueue_scripts' );
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\tamarind_forms_enqueue_gravity_form_assets', 5 );
add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\tamarind_forms_enqueue_admin_scripts' );

/**
 * Enqueue the styles and scripts
 */
function tamarind_forms_enqueue_scripts() {
	$plugin_url = plugin_dir_url( __FILE__ );

	wp_enqueue_style( 'tm-forms', $plugin_url . 'dist/css/tamarind-forms.min.css', array(), TM_FORMS_VERSION );
	wp_enqueue_script( 'tm-forms', $plugin_url . 'dist/js/tamarind-forms.min.js', array(), TM_FORMS_VERSION, true );
}

/**
 * Pre-enqueue Gravity Forms assets for landing modules that render forms in the body.
 */
function tamarind_forms_enqueue_gravity_form_assets() {
	if ( is_admin() || ! function_exists( '\gravity_form_enqueue_scripts' ) ) {
		return;
	}

	$post_id = get_queried_object_id();
	if ( ! $post_id ) {
		return;
	}

	$meta = get_post_meta( $post_id );
	if ( empty( $meta ) ) {
		return;
	}

	$form_ids = array();
	foreach ( $meta as $key => $values ) {
		if ( strpos( $key, 'landing_modules_' ) !== 0 || substr( $key, -strlen( 'select_form' ) ) !== 'select_form' ) {
			continue;
		}
		$value = is_array( $values ) ? reset( $values ) : $values;
		if ( is_numeric( $value ) && (int) $value > 0 ) {
			$form_ids[] = (int) $value;
		}
	}

	if ( empty( $form_ids ) ) {
		return;
	}

	$form_ids = array_unique( $form_ids );
	foreach ( $form_ids as $form_id ) {
		\gravity_form_enqueue_scripts( $form_id, true );
	}
}

/**
 * Enqueue the admin styles and scripts
 */
function tamarind_forms_enqueue_admin_scripts() {
	$plugin_url = plugin_dir_url( __FILE__ );

	wp_enqueue_style( 'tm-forms-admin', $plugin_url . 'assets/css/admin.css', array(), TM_FORMS_VERSION );
}

require_once __DIR__ . '/includes/acf-options.php';
require_once __DIR__ . '/includes/hidden-fields.php';
require_once __DIR__ . '/includes/consent-fields.php';
require_once __DIR__ . '/includes/display-form.php';
require_once __DIR__ . '/includes/modals-form.php';
require_once __DIR__ . '/includes/dinamic-fields.php';
require_once __DIR__ . '/includes/styles-hooks.php';
require_once __DIR__ . '/includes/validation-hooks.php';
require_once __DIR__ . '/includes/zoho.php';

// types of forms.
require_once __DIR__ . '/includes/type-forms.php';

// USERS.
require_once __DIR__ . '/includes/users/acf-user-fields.php';
require_once __DIR__ . '/includes/users/users-backend.php';
require_once __DIR__ . '/includes/users/user-register.php';
require_once __DIR__ . '/includes/users/user-update.php';
require_once __DIR__ . '/includes/users/user-woocommerce.php';
require_once __DIR__ . '/includes/users/user-login.php';
require_once __DIR__ . '/includes/users/reset-password.php';
