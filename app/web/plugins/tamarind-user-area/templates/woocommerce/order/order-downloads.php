<?php
/**
 * Order Downloads.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/order/order-downloads.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter(
	'woocommerce_account_downloads_columns',
	function( $columns ) use ( $show_title ) {
		$columns['product-image'] = '';
		$columns = array(
			'product-image'    => $columns['product-image'],
			'download-product' => $columns['download-product'],
			'download-file'    => $columns['download-file'],
			'download-expires' => $columns['download-expires'],
			'download-actions' => $columns['download-actions'],
		);
		if ( ! $show_title ) {
			unset( $columns['download-remaining'] );
			unset( $columns['download-expires'] );
		}
		return $columns;
	}
);

?>
<section class="tm-user-area-downloads woocommerce-order-downloads">
	<div class="tm-layout-grid tm-layout-grid--fullwidth">
		<?php foreach ( $downloads as $download ) : ?>
			<div class="tm-layout-main">
				<?php foreach ( wc_get_account_downloads_columns() as $column_id => $column_name ) : ?>
					<div class="<?php echo esc_attr( 'tm-user-area-downloads--product-' . $column_id ); ?>">
						<?php
						if ( has_action( 'woocommerce_account_downloads_column_' . $column_id ) ) {
							do_action( 'woocommerce_account_downloads_column_' . $column_id, $download );
						} else {
							switch ( $column_id ) {
								case 'download-product':
									if ( $download['product_url'] ) {
										echo '<a href="' . esc_url( $download['product_url'] ) . '">' . esc_html( $download['product_name'] ) . '</a>';
									} else {
										echo esc_html( $download['product_name'] );
									}
									break;
								case 'download-file':
									printf(
										'<a href="%s" class="tm-btn btn-default" alt="%s" target="_blank">%s</a>',
										esc_url( $download['download_url'] ),
										esc_attr( $download['download_name'] ),
										__( 'Download', 'tamarind-user-area' ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									);
									break;
								case 'download-remaining':
									echo is_numeric( $download['downloads_remaining'] ) ? esc_html( $download['downloads_remaining'] ) : esc_html__( '&infin;', 'woocommerce' );
									break;
								case 'download-expires':
									if ( ! empty( $download['access_expires'] ) ) {
										echo 'Expires: <time datetime="' . esc_attr( date( 'Y-m-d', strtotime( $download['access_expires'] ) ) ) . '" title="' . esc_attr( strtotime( $download['access_expires'] ) ) . '">' . esc_html( date_i18n( get_option( 'date_format' ), strtotime( $download['access_expires'] ) ) ) . '</time>'; // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
									} else {
										esc_html_e( 'Expires: Never', 'woocommerce' );
									}
									break;
								case 'product-image':
									$product = wc_get_product( $download['product_id'] );
									if ( $product ) {
										printf(
											'<a href="%s" class="woocommerce-MyAccount-downloads-product-link">%s</a>',
											esc_url( get_permalink( $product->get_id() ) ),
											wp_kses_post( $product->get_image( 'thumbnail' ) )
										);
									} else {
										echo __( 'No image', 'your-textdomain' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									}
									break;
							}
						}
						?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
	</div>
</section>
