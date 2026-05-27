<?php
/**
 * Login and Register forms for WooCommerce Shop.
 *
 * @package Tamarind_Forms
 */

namespace TamarindForms\Users\Woocommerce;

defined( 'ABSPATH' ) || exit;

/**
 * Initializes the WooCommerce login/register functionality.
 */
add_action(
	'init',
	function () {
		// Verify ACF function exists.
		if ( ! function_exists( 'get_field' ) ) {
			return;
		}

		$enable_pre_checkout = get_field( 'enable_pre-checkout', 'option' );
		$pre_checkout_page   = get_field( 'pre-checkout_page', 'option' );

		if ( ! $enable_pre_checkout || empty( $pre_checkout_page ) ) {
			return;
		}

		/**
		 * Registers the login/register shortcode.
		 */
		add_shortcode( 'wc_login_register', __NAMESPACE__ . '\\render_login_register_form' );

		/**
		 * Handles pre-checkout redirect logic.
		 */
		add_action( 'template_redirect', __NAMESPACE__ . '\\handle_pre_checkout_redirect', 20 );

		/**
		 * Modifies WooCommerce button text.
		 */
		add_filter( 'gettext', __NAMESPACE__ . '\\modify_checkout_button_text', 20, 3 );
	}
);

/**
 * Renders the combined login/register form.
 *
 * @return string HTML content for the form
 */
function render_login_register_form() {
	ob_start();
	do_action( 'woocommerce_before_customer_login_form' );
	?>
	<div class="tm-layout-grid tm-layout-grid--xl with-divider" id="customer_login">
		<div class="col-1">
			<h3><?php esc_html_e( 'Existing customer', 'woocommerce' ); ?></h3>
			<div class="gform_wrapper gform-theme gform-theme--foundation gform-theme--framework gform-theme--orbital tm-form_wrapper tm-form-style-default_wrapper">
				<?php wp_login_form(); ?>
			</div>    
			<p>
				<a class="wp-login-lost-password" href="<?php echo esc_url( get_field( 'user_label_forgot_page', 'option' ) ); ?>">
					<?php esc_html_e( 'Lost your password?', 'woocommerce' ); ?>
				</a>
			</p>
		</div>
		<div class="col-2">
			<h3><?php echo esc_html( sprintf( __( 'New at %s?', 'woocommerce' ), get_option( 'blogname' ) ) ); ?></h3>
			<?php
			if ( is_plugin_active( 'tamarind-forms/tamarind-forms.php' ) ) {
				\tamarind_forms\display_form\display_form( 'register_user_shop', true, get_the_id() );
			}
			?>
		</div>
	</div>
	<?php
	do_action( 'woocommerce_after_customer_login_form' );
	return ob_get_clean();
}

/**
 * Handles redirect logic for pre-checkout flow.
 */
function handle_pre_checkout_redirect() {
	if ( ! function_exists( 'wc' ) ) {
		return;
	}

	$pre_checkout_page = get_field( 'pre-checkout_page', 'option' );
	if ( ! $pre_checkout_page ) {
		error_log( 'Pre-checkout page is not set in ACF.' );
		return;
	}

	$redirect_page_id = $pre_checkout_page;

	if ( ! is_user_logged_in() && is_checkout() ) {
		error_log( 'Redirecting guest to pre-checkout page.' );
		wp_safe_redirect( get_permalink( $redirect_page_id ) );
		exit;
	} elseif ( is_user_logged_in() && is_page( $redirect_page_id ) ) {
		error_log( 'Redirecting logged-in user to checkout page.' );
		wp_safe_redirect( get_permalink( wc_get_page_id( 'checkout' ) ) );
		exit;
	}
}

/**
 * Modifies the "Proceed to checkout" button text.
 *
 * @param string $translated_text Translated text.
 * @param string $text Original text.
 * @param string $domain Text domain.
 * @return string Modified text.
 */
function modify_checkout_button_text( $translated_text, $text, $domain ) {
	if ( 'woocommerce' === $domain && 'Proceed to checkout' === $text ) {
		$translated_text = __( 'Next', 'woocommerce' );
	}
	return $translated_text;
}
