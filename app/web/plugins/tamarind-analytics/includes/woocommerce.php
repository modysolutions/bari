<?php
/**
 * Handle woocommerce analytics functions
 *
 * @package Tamarind_Analytics
 * phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed
 */

namespace tamarind_analytics;

add_action( 'wp_footer', __NAMESPACE__ . '\\tm_ga4_view_item_event', 20 );

add_action( 'woocommerce_add_to_cart', __NAMESPACE__ . '\\tm_ga4_store_add_to_cart_event', 10, 6 );
add_action( 'wp_footer', __NAMESPACE__ . '\\tm_ga4_print_add_to_cart_event_on_cart', 20 );

add_action( 'woocommerce_before_checkout_form', __NAMESPACE__ . '\\tm_ga4_begin_checkout_event', 20 );
add_action( 'woocommerce_cart_updated', __NAMESPACE__ . '\\tm_ga4_reset_begin_checkout_flag' );
add_action( 'woocommerce_add_to_cart', __NAMESPACE__ . '\\tm_ga4_reset_begin_checkout_flag' );
add_action( 'woocommerce_remove_cart_item', __NAMESPACE__ . '\\tm_ga4_reset_begin_checkout_flag' );
add_action( 'woocommerce_cart_item_restored', __NAMESPACE__ . '\\tm_ga4_reset_begin_checkout_flag' );

add_action( 'woocommerce_thankyou', __NAMESPACE__ . '\\tm_ga4_purchase_event', 20, 1 );

/**
 * Emit GA4 'view_item' event on single product pages.
 */
function tm_ga4_view_item_event() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	if ( ! tm_can_track_current_user() ) {
		return;
	}

	global $product;

	if ( ! $product instanceof \WC_Product ) {
		return;
	}

	$item = array(
		'item_id'       => $product->get_sku() ? $product->get_sku() : (string) $product->get_id(),
		'item_name'     => $product->get_name(),
		'price'         => (float) wc_get_price_to_display( $product ),
		'item_category' => '',
		'quantity'      => 1,
	);

	$terms = get_the_terms( $product->get_id(), 'product_cat' );
	if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
		$item['item_category'] = $terms[0]->name;
	}

	$payload = array(
		'event'     => 'view_item',
		'ecommerce' => array(
			'currency' => get_woocommerce_currency(),
			'items'    => array( $item ),
		),
	);

	tm_ga4_render_template(
		'template-parts/ga4-add-payload.php',
		array( 'payload' => $payload )
	);
}

/**
 * Store GA4 'add_to_cart' event in WooCommerce session on add to cart.
 *
 * @param string $cart_item_key Cart item key.
 * @param int    $product_id Product ID.
 * @param int    $quantity Quantity added.
 * @param int    $variation_id Variation ID.
 * @param array  $variation Variation data.
 * @param array  $cart_item_data Cart item data.
 */
function tm_ga4_store_add_to_cart_event( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
	if ( ! tm_can_track_current_user() ) {
		return;
	}

	if ( ! function_exists( 'WC' ) || ! WC()->session ) {
		return;
	}

	$product = wc_get_product( $variation_id ? $variation_id : $product_id );
	if ( ! $product ) {
		return;
	}

	$item_category = '';
	$terms = get_the_terms( $product_id, 'product_cat' );
	if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
		$item_category = $terms[0]->name;
	}

	$item = array(
		'item_id'       => $product->get_sku() ? $product->get_sku() : (string) $product->get_id(),
		'item_name'     => $product->get_name(),
		'price'         => (float) wc_get_price_to_display( $product ),
		'quantity'      => (int) $quantity,
		'item_category' => (string) $item_category,
	);

	if ( $variation_id ) {
		$item['item_variant'] = $product->get_name();
	}

	$payload = array(
		'event'     => 'add_to_cart',
		'ecommerce' => array(
			'currency' => get_woocommerce_currency(),
			'items'    => array( $item ),
		),
	);

	WC()->session->set( 'tm_ga4_add_to_cart_event', $payload );
}

/**
 * Print GA4 'add_to_cart' event on cart page if stored in session.
 */
function tm_ga4_print_add_to_cart_event_on_cart() {
	if ( ! function_exists( 'is_cart' ) || ! is_cart() ) {
		return;
	}

	if ( ! tm_can_track_current_user() ) {
		return;
	}

	if ( ! function_exists( 'WC' ) || ! WC()->session ) {
		return;
	}

	$payload = WC()->session->get( 'tm_ga4_add_to_cart_event' );
	if ( empty( $payload ) || ! is_array( $payload ) ) {
		return;
	}

	WC()->session->__unset( 'tm_ga4_add_to_cart_event' );

	tm_ga4_render_template(
		'template-parts/ga4-add-payload.php',
		array( 'payload' => $payload )
	);
}

/**
 * Get cart items formatted for GA4 ecommerce events.
 *
 * @return array<int, array<string, mixed>> Array of cart items.
 */
function tm_ga4_cart_items(): array {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return array();
	}

	$items = array();
	foreach ( WC()->cart->get_cart() as $cart_item ) {
		if ( empty( $cart_item['data'] ) || ! ( $cart_item['data'] instanceof \WC_Product ) ) {
			continue;
		}

		$product       = $cart_item['data'];
		$product_id    = (int) $product->get_id();
		$quantity      = isset( $cart_item['quantity'] ) ? (int) $cart_item['quantity'] : 1;
		$item_category = '';

		$cat_product_id = $product->is_type( 'variation' ) ? (int) $product->get_parent_id() : $product_id;
		$terms = get_the_terms( $cat_product_id, 'product_cat' );
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			$item_category = $terms[0]->name;
		}

		$item = array(
			'item_id'       => $product->get_sku() ? $product->get_sku() : (string) $product_id,
			'item_name'     => $product->get_name(),
			'price'         => (float) wc_get_price_to_display( $product ),
			'quantity'      => $quantity,
			'item_category' => (string) $item_category,
		);

		if ( $product->is_type( 'variation' ) ) {
			$item['item_variant'] = $product->get_name();
		}

		$items[] = $item;
	}

	return $items;
}

/**
 * Emit GA4 'begin_checkout' event on checkout page.
 */
function tm_ga4_begin_checkout_event() {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
		return;
	}

	// Do not send on order received (thank you) page.
	if ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) {
		return;
	}

	if ( ! tm_can_track_current_user() ) {
		return;
	}

	if ( ! function_exists( 'WC' ) || ! WC()->cart || ! WC()->session ) {
		return;
	}

	// Avoid duplicates on page reload.
	if ( WC()->session->get( 'tm_ga4_begin_checkout_sent' ) ) {
		return;
	}

	$items = tm_ga4_cart_items();
	if ( empty( $items ) ) {
		return;
	}

	$value   = (float) WC()->cart->get_subtotal();
	$payload = array(
		'event'     => 'begin_checkout',
		'ecommerce' => array(
			'currency' => get_woocommerce_currency(),
			'value'    => $value,
			'items'    => $items,
		),
	);

	WC()->session->set( 'tm_ga4_begin_checkout_sent', 1 );

	tm_ga4_render_template(
		'template-parts/ga4-add-payload.php',
		array( 'payload' => $payload )
	);
}

/**
 * Reset 'begin_checkout' sent flag in session.
 */
function tm_ga4_reset_begin_checkout_flag() {
	if ( function_exists( 'WC' ) && WC()->session ) {
		WC()->session->__unset( 'tm_ga4_begin_checkout_sent' );
	}
}

/**
 * Emit GA4 'purchase' event on thank you page.
 *
 * @param int $order_id Order ID.
 */
function tm_ga4_purchase_event( $order_id ) {
	if ( ! $order_id ) {
		return;
	}

	if ( ! tm_can_track_current_user() ) {
		return;
	}

	if ( ! function_exists( 'WC' ) || ! WC()->session ) {
		return;
	}

	$sent_key = 'tm_ga4_purchase_sent_' . (int) $order_id;
	if ( WC()->session->get( $sent_key ) ) {
		return; // No duplicates.
	}

	$order = wc_get_order( $order_id );
	if ( ! $order instanceof \WC_Order ) {
		return;
	}

	$items = array();
	foreach ( $order->get_items() as $item ) {
		if ( ! $item instanceof \WC_Order_Item_Product ) {
			continue;
		}

		$product    = $item->get_product();
		$product_id = $product ? $product->get_id() : (int) $item->get_product_id();
		$qty        = (int) $item->get_quantity();

		$cat_product_id = $product && $product->is_type( 'variation' ) ? (int) $product->get_parent_id() : (int) $product_id;
		$item_category = '';
		$terms = get_the_terms( $cat_product_id, 'product_cat' );
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			$item_category = $terms[0]->name;
		}

		// Price unitary (line net / qty). This avoids issues with line discounts.
		$line_total = (float) $item->get_total(); // no tax.
		$unit_price = $qty > 0 ? (float) ( $line_total / $qty ) : (float) $line_total;

		$item_id = '';
		if ( $product instanceof \WC_Product ) {
			$item_id = $product->get_sku() ? $product->get_sku() : (string) $product_id;
		} else {
			$item_id = (string) $product_id;
		}

		$row = array(
			'item_id'       => $item_id,
			'item_name'     => $item->get_name(),
			'price'         => $unit_price,
			'quantity'      => $qty,
			'item_category' => (string) $item_category,
		);

		if ( $product instanceof \WC_Product && $product->is_type( 'variation' ) ) {
			$row['item_variant'] = $product->get_name();
		}

		$items[] = $row;
	}

	// Coupons (GA4 supports coupon).
	$coupons = $order->get_coupon_codes();
	$coupon  = ! empty( $coupons ) ? (string) $coupons[0] : '';

	$payload = array(
		'event'     => 'purchase',
		'ecommerce' => array(
			'transaction_id' => (string) $order->get_order_number(),
			'affiliation'    => (string) get_bloginfo( 'name' ),
			'value'          => (float) $order->get_total(), // Final total (includes taxes+shipping-discounts).
			'tax'            => (float) $order->get_total_tax(),
			'shipping'       => (float) $order->get_shipping_total(),
			'currency'       => (string) $order->get_currency(),
			'coupon'         => $coupon,
			'items'          => $items,
		),
	);

	WC()->session->set( $sent_key, 1 );

	tm_ga4_render_template(
		'template-parts/ga4-add-payload.php',
		array( 'payload' => $payload )
	);
}
