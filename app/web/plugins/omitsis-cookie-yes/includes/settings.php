<?php
/**
 * Settings.
 *
 * @package OmitsisCookieYes
 */

namespace OmitsisCookieYes\settings;

defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', __NAMESPACE__ . '\register_admin_menu' );
add_action( 'admin_init', __NAMESPACE__ . '\register_settings' );
add_filter( 'plugin_action_links', __NAMESPACE__ . '\add_plugin_settings_link', 10, 2 );

/**
 * Add settings link to plugin actions
 *
 * @param  array  $plugin_actions Current plugin actions.
 * @param  string $plugin_file Plugin file.
 * @since  1.2.0
 * @return array
 */
function add_plugin_settings_link( $plugin_actions, $plugin_file ) {
	$new_actions = array();

	if ( basename( plugin_dir_path( __DIR__ ) ) . '/omitsis-cookie-yes.php' === $plugin_file ) {
		/* translators: %s: Settings page URL */
		$new_actions['omitsis-cookie-yes-settings'] = sprintf( __( '<a href="%s">Settings</a>', 'omitsis-cookie-yes' ), esc_url( admin_url( 'options-general.php?page=omitsis-cookie-yes' ) ) );
	}

	return array_merge( $new_actions, $plugin_actions );
}

/**
 * Register the plugin menu item.
 */
function register_admin_menu() {
	add_submenu_page(
		'options-general.php',
		__( 'Omitsis Cookie Yes', 'omitsis-cookie-yes' ),
		__( 'Omitsis Cookie Yes', 'omitsis-cookie-yes' ),
		'manage_options',
		'omitsis-cookie-yes',
		__NAMESPACE__ . '\render_settings_page'
	);
}

/**
 * Renders the settings page.
 */
function render_settings_page() {
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Omitsis Cookie Yes', 'omitsis-cookie-yes' ); ?></h1>
		<form action="options.php" method="post">
			<?php
			settings_fields( 'omitsis-cookie-yes' );
			do_settings_sections( 'omitsis-cookie-yes' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

/**
 * Renders the settings section.
 */
function render_settings_section() {
}

/**
 * Add the omitsis_cookie_yes_id setting.
 */
function register_settings() {
	register_setting(
		'omitsis-cookie-yes',
		'omitsis_cookie_yes_id',
		array( 'sanitize_callback' => 'sanitize_text_field' )
	);

	add_settings_field(
		'omitsis_cookie_yes_id',
		__( 'Cookie Yes ID', 'omitsis-cookie-yes' ),
		__NAMESPACE__ . '\cookie_yes_id_field_html',
		'omitsis-cookie-yes',
		'omitsis-cookie-yes'
	);

	add_settings_section(
		'omitsis-cookie-yes',
		__( 'Omitsis Cookie Yes', 'omitsis-cookie-yes' ),
		__NAMESPACE__ . '\render_settings_section',
		'omitsis-cookie-yes'
	);
}

/**
 * Render the HTML for the CookieYes ID field.
 */
function cookie_yes_id_field_html() {
	$has_env = (bool) getenv( 'OMITSIS_COOKIE_YES_ID' );
	printf(
		'<input type="text" id="omitsis_cookie_yes_id" name="omitsis_cookie_yes_id" value="%1$s" %2$s class="regular-text code%3$s" />',
		esc_attr( get_option( 'omitsis_cookie_yes_id' ) ),
		disabled( $has_env, true, false ),
		$has_env ? ' disabled' : '',
	);
}
