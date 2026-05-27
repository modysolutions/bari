<?php
/**
 * User backend.
 *
 * @package Tamarind_Forms
 *
 * phpcs:disable WordPress.Files.FileName
 */

namespace tamarind_forms\users\backend;

defined( 'ABSPATH' ) || exit;


add_filter( 'theme_page_templates', __NAMESPACE__ . '\add_login_password_template' );
add_filter( 'template_include', __NAMESPACE__ . '\load_login_password_template' );
add_filter( 'register_url', __NAMESPACE__ . '\custom_register_url' );
add_filter( 'lostpassword_url', __NAMESPACE__ . '\custom_lostpassword_url' );
add_filter( 'auth_cookie_expiration', __NAMESPACE__ . '\custom_cookie_expiration', 99, 3 );
add_action( 'admin_init', __NAMESPACE__ . '\tamarind_admin_init' );



/**
 * Adds custom page template to WordPress admin template selector.
 *
 * @param array $templates Existing page templates.
 * @return array Modified templates array with our custom template added.
 */
function add_login_password_template( $templates ) {
	$templates['page-template-login-password.php'] = 'Login and Lost password Page';
	return $templates;
}


/**
 * Includes our custom template file when selected.
 *
 * @param string $template Current template path.
 * @return string Modified template path if our template is selected.
 */
function load_login_password_template( $template ) {
	if ( 'page-template-login-password.php' === get_page_template_slug() ) {
		$template = plugin_dir_path( __FILE__ ) . 'page-template-login-password.php';
	}
	return $template;
}


/**
 * Modifies the default WordPress registration page URL.
 *
 * Replaces the default registration URL with a custom one from ACF options
 * when the 'user_label_register_page' field is set.
 *
 * @param string $url The default registration URL.
 * @return string Modified registration URL.
 */
function custom_register_url( string $url ) : string {
	$register_page_url = get_field( 'user_label_register_page', 'option' );

	if ( ! empty( $register_page_url ) ) {
		return esc_url( $register_page_url );
	}

	return $url;
}


/**
 * Modifies the default WordPress lost password URL.
 *
 * Replaces the default lost password URL with a custom URL from ACF options
 * when the 'user_label_forgot_page' field is set in theme options.
 *
 * @param string $url The default lost password URL.
 * @return string The modified lost password URL if set, original URL otherwise.
 */
function custom_lostpassword_url( $url ) {
	$lostpassword_url = get_field( 'user_label_forgot_page', 'option' );

	if ( $lostpassword_url ) {
		$url = esc_url( $lostpassword_url );
	}
	return $url;
}

/**
 * Customizes the authentication cookie expiration based on user role.
 *
 * Clients get 1 day expiration, all other roles get 30 days expiration.
 * Uses WordPress constants for time calculation.
 *
 * @param int  $expiration The default expiration time in seconds.
 * @param int  $user_id The ID of the user.
 * @param bool $remember Whether the user chose to be remembered.
 *
 * @return int Modified expiration time in seconds.
 */
function custom_cookie_expiration( $expiration, $user_id, $remember ) {

	$user = get_userdata( $user_id );

	// If the user is a client, set expiration to 1 day.
	if ( in_array( 'client', (array) $user->roles, true ) ) {
		return DAY_IN_SECONDS;
	}

	// For all other roles, set expiration to 30 days.
	return 30 * DAY_IN_SECONDS;
}


/**
 * Add 'Registered' column in backend users list.
 */
function tamarind_admin_init() {
	if ( is_admin() ) {
		add_filter( 'manage_users_columns', __NAMESPACE__ . '\tamarind_users_columns' );
		add_action( 'manage_users_custom_column', __NAMESPACE__ . '\tamarind_users_custom_column', 10, 3 );
		add_filter( 'manage_users_sortable_columns', __NAMESPACE__ . '\tamarind_users_sortable_columns' );
		add_filter( 'request', __NAMESPACE__ . '\tamarind_users_orderby_column' );
	}
}

/**
 * Adds a 'Registered' column to the users list in the admin area.
 *
 * @param array $columns The existing columns.
 *
 * @return array Modified columns with 'Registered' added.
 */
function tamarind_users_columns( $columns ) {
	$columns['registerdate'] = _x( 'Registered', 'user', 'tamarind-forms' );
	return $columns;
}

/**
 * Displays the registration date in the 'Registered' column.
 *
 * @param string $value The current value of the column.
 * @param string $column_name The name of the column.
 * @param int    $user_id The ID of the user.
 *
 * @return string Formatted registration date or 'Unknown' if not available.
 */
function tamarind_users_custom_column( $value, $column_name, $user_id ) {

	global $mode;

	$list_mode = empty( $_REQUEST['mode'] ) ? 'list' : sanitize_text_field( $_REQUEST['mode'] );
	if ( 'registerdate' !== $column_name ) {
		return $value;
	} else {
		$user = get_userdata( $user_id );

		if ( is_multisite() && ( 'list' === $list_mode ) ) {
			$formated_date = __( 'Y/m/d', 'tamarind-forms' );
		} else {
			$formated_date = __( 'Y/m/d g:i:s a', 'tamarind-forms' );
		}

		$registered = strtotime( $user->user_registered );

		// If the date is negative or in the future, then something's wrong, so we'll be unknown.
		if ( ( $registered <= 0 ) || ( time() <= $registered ) ) {
			$registerdate = '<span class="recently-registered invalid-date">' . __( 'Unknown', 'tamarind-forms' ) . '</span>';
		} else {
			$registerdate = '<span class="recently-registered valid-date">' . date_i18n( $formated_date, $registered ) . '</span>';
		}

		return $registerdate;
	}
}

/**
 * Makes the 'Registered' column sortable.
 *
 * @param array $columns The existing sortable columns.
 *
 * @return array Modified columns with 'registerdate' as a sortable column.
 */
function tamarind_users_sortable_columns( $columns ) {
	$custom = array(
		// Meta column id => sortby value used in query.
		'registerdate' => 'registered',
	);
	return wp_parse_args( $custom, $columns );
}

/**
 * Orders the users by the 'registerdate' meta key.
 *
 * This function modifies the query to sort users based on their registration date.
 *
 * @param array $vars The query variables.
 *
 * @return array Modified query variables with custom ordering for 'registerdate'.
 */
function tamarind_users_orderby_column( $vars ) {
	if ( isset( $vars['orderby'] ) && 'registerdate' === $vars['orderby'] ) {
		$new_vars = array(
			'meta_key' => 'registerdate',
			'orderby'  => 'meta_value',
		);
		$vars = array_merge( $vars, $new_vars );
	}
	return $vars;
}
