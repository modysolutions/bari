<?php

/**
 * Add payment method form form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-add-payment-method.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 7.8.0
 */

namespace tamarind_userarea;

defined('ABSPATH') || exit;

$available_gateways = WC()->payment_gateways->get_available_payment_gateways();

echo 'Form Add Payment Method';

if ($available_gateways) : ?>
	<form id="add_payment_method" method="post">
		<div id="payment" class="woocommerce-Payment">
			<ul class="woocommerce-PaymentMethods payment_methods methods">
				<?php
				// Chosen Method.
				if (count($available_gateways)) {
					current($available_gateways)->set_current();
				}

				foreach ($available_gateways as $gateway) {
				?>
					<li class="woocommerce-PaymentMethod woocommerce-PaymentMethod--<?php echo esc_attr($gateway->id); ?> payment_method_<?php echo esc_attr($gateway->id); ?>">
						<input id="payment_method_<?php echo esc_attr($gateway->id); ?>" type="radio" class="input-radio" name="payment_method" value="<?php echo esc_attr($gateway->id); ?>" <?php checked($gateway->chosen, true); ?> />
						<label for="payment_method_<?php echo esc_attr($gateway->id); ?>"><?php echo wp_kses_post($gateway->get_title()); ?> <?php echo wp_kses_post($gateway->get_icon()); ?></label>
						<?php
						$suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

						// Registrar y encolar estilos.
						wp_register_style('stripe_styles', plugins_url('assets/css/stripe-styles.css', WC_STRIPE_MAIN_FILE), [], WC_STRIPE_VERSION);
						wp_enqueue_style('stripe_styles');

						// Registrar y encolar scripts dependientes.
						wp_register_script('jquery-payment', plugins_url('assets/js/jquery.payment.min.js', WC_STRIPE_MAIN_FILE), ['jquery'], WC_STRIPE_VERSION, true);
						wp_enqueue_script('jquery-payment');

						wp_register_script('stripe', 'https://js.stripe.com/v3/', [], '3.0', true);
						wp_enqueue_script('stripe');

						// Registrar y localizar el script woocommerce_stripe.
						wp_register_script(
							'woocommerce_stripe',
							plugins_url('assets/js/stripe' . $suffix . '.js', WC_STRIPE_MAIN_FILE),
							['jquery-payment', 'stripe'],
							WC_STRIPE_VERSION,
							true
						);
						wp_enqueue_script('woocommerce_stripe');

						// Agregamos los parámetros inline usando wp_add_inline_script().
						$wc_stripe_params = [
							'title'                     => 'Credit card (Stripe)',
							'key'                       => 'pk_live_22mpdmFRLmdsKCQfM0FWEU99', // Clave pública de Stripe.
							'i18n_terms'                => 'Please accept the terms and conditions first',
							'i18n_required_fields'      => 'Please fill in required checkout fields first',
							'updateFailedOrderNonce'    => wp_create_nonce('wc_stripe_update_failed_order'),
							'updatePaymentIntentNonce'  => wp_create_nonce('wc_stripe_update_payment_intent'),
							'orderId'                   => '0',
							'checkout_url'              => home_url('/?wc-ajax=checkout'),
							'stripe_locale'             => 'en',
							'no_prepaid_card_msg'       => "Sorry, we're not accepting prepaid cards at this time. Your credit card has not been charged. Please try with an alternative payment method.",
							'no_sepa_owner_msg'         => 'Please enter your IBAN account name.',
							'no_sepa_iban_msg'          => 'Please enter your IBAN account number.',
							'payment_intent_error'      => "We couldn't initiate the payment. Please try again.",
							'sepa_mandate_notification' => 'email',
							'allow_prepaid_card'        => 'yes',
							'inline_cc_form'            => 'no',
							'is_checkout'               => 'no',
							'return_url'                => home_url('/complete-process/order-received/?utm_nooverride=1'),
							'ajaxurl'                   => admin_url('admin-ajax.php'),
							'stripe_nonce'              => wp_create_nonce('wc_stripe'),
							'statement_descriptor'      => 'ECigIntelligence',
							'elements_options'          => [],
							'sepa_elements_options'     => [
								'supportedCountries' => ['SEPA'],
								'placeholderCountry' => 'GB',
								'style'              => ['base' => ['fontSize' => '15px']],
							],
							'invalid_owner_name'        => 'Billing First Name and Last Name are required.',
							'is_change_payment_page'    => 'no',
							'is_add_payment_page'       => 'yes',
							'is_pay_for_order_page'     => 'no',
							'elements_styling'          => '',
							'elements_classes'          => '',
							'add_card_nonce'            => wp_create_nonce('wc_stripe_add_payment_method'),
							'create_payment_intent_nonce' => wp_create_nonce('wc_stripe_create_payment_intent'),
							'cpf_cnpj_required_msg'     => 'CPF/CNPJ is a required field',
							'invalid_number'            => 'The card number is not a valid credit card number.',
							'invalid_expiry_month'      => "The card's expiration month is invalid.",
							'invalid_expiry_year'       => "The card's expiration year is invalid.",
							'invalid_cvc'               => "The card's security code is invalid.",
							'incorrect_number'          => 'The card number is incorrect.',
							'incomplete_number'         => "The card number is incomplete.",
							'incomplete_cvc'            => "The card's security code is incomplete.",
							'incomplete_expiry'         => "The card's expiration date is incomplete.",
							'expired_card'              => 'The card has expired.',
							'incorrect_cvc'             => "The card's security code is incorrect.",
							'incorrect_zip'             => "The card's zip code failed validation.",
							'postal_code_invalid'       => 'Invalid zip code, please correct and try again.',
							'invalid_expiry_year_past'  => "The card's expiration year is in the past.",
							'card_declined'             => 'The card was declined.',
							'missing'                   => 'There is no card on a customer that is being charged.',
							'processing_error'          => 'An error occurred while processing the card.',
							'invalid_sofort_country'    => 'The billing country is not accepted by Sofort. Please try another country.',
							'email_invalid'             => 'Invalid email address, please correct and try again.',
							'invalid_request_error'     => 'Unable to save this payment method, please try again or use alternative method.',
							'amount_too_large'          => 'The order total is too high for this payment method.',
							'amount_too_small'          => 'The order total is too low for this payment method.',
							'country_code_invalid'      => 'Invalid country code, please try again with a valid country code.',
							'tax_id_invalid'            => 'Invalid Tax Id, please try again with a valid tax id.',
							'invalid_wallet_type'       => 'Invalid wallet payment type, please try again or use an alternative method.',
							'payment_intent_authentication_failure' => 'We are unable to authenticate your payment method. Please choose a different payment method and try again.',
						];						

						wp_add_inline_script(
							'woocommerce_stripe',
							'var wc_stripe_params = ' . wp_json_encode($wc_stripe_params) . ';',
							'before'
						);

						if ($gateway->has_fields() || $gateway->get_description()) {
							echo '<div class="woocommerce-PaymentBox woocommerce-PaymentBox--' . esc_attr($gateway->id) . ' payment_box payment_method_' . esc_attr($gateway->id) . '" style="display: block;">';
							$gateway->payment_fields();
							echo '</div>';
						}
						?>
					</li>
				<?php
				}
				?>
			</ul>

			<?php do_action('woocommerce_add_payment_method_form_bottom'); ?>

			<div class="form-row">
				<?php wp_nonce_field('woocommerce-add-payment-method', 'woocommerce-add-payment-method-nonce'); ?>
				<button type="submit" class="woocommerce-Button woocommerce-Button--alt button alt<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>" id="place_order" value="<?php esc_attr_e('Add payment method', 'woocommerce'); ?>"><?php esc_html_e('Add payment method', 'woocommerce'); ?></button>
				<input type="hidden" name="woocommerce_add_payment_method" id="woocommerce_add_payment_method" value="1" />
			</div>
		</div>
	</form>
<?php else : ?>
	<?php wc_print_notice(esc_html__('New payment methods can only be added during checkout. Please contact us if you require assistance.', 'woocommerce'), 'notice'); ?>
<?php endif; ?>