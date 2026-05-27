<?php
/**
 * Expiration Modal
 *
 * @package tamarind_subscriptions
 */

use function tamarind_subscriptions\users\is_user_chinese;

if ( is_user_chinese() ) {
	$modal_title   = get_field( 'expiration_modal_title_chinese', 'options' );
	$modal_content = get_field( 'expiration_modal_content_chinese', 'options' );
	$modal_link    = get_field( 'expiration_modal_link_chinese', 'options' );
} else {
	$modal_title   = get_field( 'expiration_modal_title', 'options' );
	$modal_content = get_field( 'expiration_modal_content', 'options' );
	$modal_link    = get_field( 'expiration_modal_link', 'options' );
}

$modal_url = '';
if ( is_array( $modal_link ) && isset( $modal_link['url'] ) ) {
	$modal_url    = $modal_link['url'];
	$link_title   = $modal_link['title'];
	$modal_target = $modal_link['target'] ? $modal_link['target'] : '_self';
}

$nonce = wp_create_nonce( 'disable_expiration_modal_' . get_current_user_id() );
?>

<dialog id="expiration-modal" class="tm-modal" data-tm-auto-open="2000">
	<div class="tm-modal-content">
		<button class="tm-modal-close" type="button">×</button>
		<h3><?php echo esc_html( $modal_title ); ?></h3>
		<?php echo wp_kses_post( $modal_content ); ?>
		<?php
		if ( is_array( $modal_link ) && isset( $modal_link['url'] ) ) :
			?>
			<a class="tm-btn btn-default" href="<?php echo esc_url( $modal_url ); ?>" target="<?php echo esc_attr( $modal_target ); ?>"><?php echo esc_html( $link_title ); ?></a>
		<?php endif; ?>
	</div>
</dialog>
