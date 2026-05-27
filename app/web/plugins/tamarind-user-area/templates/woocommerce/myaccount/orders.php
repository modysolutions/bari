<?php
/**
 * Orders
 *
 * Shows orders on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/orders.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.2.0
 */

namespace tamarind_userarea;

defined( 'ABSPATH' ) || exit;

add_filter(
	'woocommerce_account_orders_columns',
	function( $columns ) {
		$columns = array();
		$columns['order-number'] = $columns['order-number'];
		$columns['order-total']  = $columns['order-total'];
		$columns['order-date']   = $columns['order-date'];
		$columns['order-actions'] = $columns['order-actions'];
		return $columns;
	}
);

do_action( 'woocommerce_before_account_orders', $has_orders ); ?>

<?php if ( $has_orders ) : ?>

	<div class="tm-user-area-orders tm-layout-grid tm-layout-grid--fullwidth">
		<?php
		foreach ( $customer_orders->orders as $customer_order ) {
			$order      = wc_get_order( $customer_order ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$item_count = $order->get_item_count() - $order->get_item_count_refunded();

			$view_order = get_option_menu_link( 'purchased_reports_orders_view_order' );
			?>
			<div class="tm-user-area-orders--item tm-layout-main woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr( $order->get_status() ); ?> order">
				<?php
				foreach ( wc_get_account_orders_columns() as $column_id => $column_name ) {
					$is_order_number = 'order-number' === $column_id;
					?>
					<div class="<?php echo esc_attr( 'tm-user-area-order--' . $column_id ); ?>">
						<?php
						if ( has_action( 'woocommerce_my_account_my_orders_column_' . $column_id ) ) {
							do_action( 'woocommerce_my_account_my_orders_column_' . $column_id, $order );
						} else {
							$url_order = add_query_arg(
								array(
									'num'      => $order->get_order_number(),
									'_wpnonce' => wp_create_nonce( 'process_order_num' ),
								),
								$view_order['link']
							);
							switch ( $column_id ) {
								case 'order-number':
									?>
									<a href="<?php echo esc_url( $url_order ); ?>" aria-label="<?php echo esc_attr( sprintf( 'View order number %s', $order->get_order_number() ) ); ?>">
										<?php echo esc_html( _x( '#', 'hash before order number', 'woocommerce' ) . $order->get_order_number() ); ?>
									</a>
									<?php
									break;
								case 'order-date':
									?>
									<time datetime="<?php echo esc_attr( $order->get_date_created()->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></time>
									<?php
									break;
								case 'order-status':
									echo esc_html( 'Status: ' . wc_get_order_status_name( $order->get_status() ) );
									break;
								case 'order-total':
									echo wp_kses_post( sprintf( _n( '%1$s for %2$s item', '%1$s for %2$s items', $item_count, 'woocommerce' ), $order->get_formatted_order_total(), $item_count ) ); // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
									break;
								case 'order-actions':
									$actions = wc_get_account_orders_actions( $order );
									if ( ! empty( $actions ) ) {
										foreach ( $actions as $key => $get_action ) {
											/* translators: %s: order number */
											echo '<a href="' . esc_url( $url_order ) . '" class="woocommerce-button tm-btn btn-default button ' . sanitize_html_class( $key ) . '" aria-label="' . esc_attr( sprintf( __( 'View order number %s', 'woocommerce' ), $order->get_order_number() ) ) . '">' . esc_html( $get_action['name'] ) . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
										}
									}
									break;
								default:
							}
						}
						?>
					</div>
					<?php
				};
				?>
			</div>
			<?php
		}
		?>
	</div>

	<?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>

	<?php if ( 1 < $customer_orders->max_num_pages ) : ?>
		<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
			<?php if ( 1 !== $current_page ) : ?>
				<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button tm-btn btn-default" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page - 1 ) ); ?>"><?php esc_html_e( 'Previous', 'woocommerce' ); ?></a>
			<?php endif; ?>

			<?php if ( intval( $customer_orders->max_num_pages ) !== $current_page ) : ?>
				<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button tm-btn btn-default" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page + 1 ) ); ?>"><?php esc_html_e( 'Next', 'woocommerce' ); ?></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

<?php else : ?>
	<?php wc_print_notice( esc_html__( 'No order has been made yet.', 'woocommerce' ) . ' <a class="woocommerce-Button wc-forward button tm-btn btn-default" href="' . esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ) . '">' . esc_html__( 'Browse products', 'woocommerce' ) . '</a>', 'notice' ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment ?>
<?php endif; ?>

<?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>
