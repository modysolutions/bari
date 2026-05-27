<?php
/**
 * Expiration Banner countdown
 *
 * @package tamarind_subscriptions
 */

use function tamarind_subscriptions\users\is_user_chinese;

$days_remaining = $args['days_remaining'] ?? 0;

$banner_link                      = get_field( 'expiration_banner_link', 'options' );
$banner_background_color          = get_field( 'expiration_banner_background_color', 'options' );
$banner_background_featured_color = get_field( 'expiration_banner_background_featured_color', 'options' );
$banner_style                     = get_field( 'expiration_banner_style', 'options' );
$banner_fontsize                  = get_field( 'expiration_banner_fontsize', 'options' );

if ( empty( $banner_background_color ) ) {
	$banner_background_color = '#f5f5f5';
}

if ( empty( $banner_style ) ) {
	$banner_style = 'light';
}

$banner_url = '';
if ( is_array( $banner_link ) && isset( $banner_link['url'] ) ) {
	$banner_url    = $banner_link['url'];
	$banner_target = $banner_link['target'] ? $banner_link['target'] : '_self';
}

$banner_url_class = ( ! empty( $banner_url ) ) ? ' expire-banner-url' : '';

if ( is_user_chinese() ) {
	$banner_text = get_field( 'expiration_banner_text_chinese', 'options' );
	if ( 0 >= $days_remaining ) {
		$banner_text = get_field( 'expiration_banner_expired_chinese', 'options' );
	} else {
		// Replace {days} with the remaining days.
		$banner_text = str_replace( '{days}', $days_remaining, $banner_text );
	}
} else {
	$banner_text = get_field( 'expiration_banner_text', 'options' );
	if ( 0 >= $days_remaining ) {
		$banner_text = esc_html__( 'Your subscription has expired!', 'tamarind_subscriptions' );
	} else {
		// Replace {days} with the remaining days.
		$banner_text = str_replace( '{days}', $days_remaining, $banner_text );
	}
}
?>

<div class="expire-banner-content <?php echo esc_attr( 'expire-banner-' . $banner_style ); ?> <?php echo esc_attr( 'expire-banner-' . $banner_fontsize ); ?><?php echo esc_attr( $banner_url_class ); ?>" style="background-image: linear-gradient(-23deg, <?php echo esc_attr( $banner_background_color ); ?> 0%, <?php echo esc_attr( $banner_background_color ); ?> 25%, <?php echo esc_attr( $banner_background_color ); ?> 51%, <?php echo esc_attr( $banner_background_featured_color ); ?> 100%);">
	<div class="wrap">
		<div class="expire-banner-text">
			<?php
			if ( ! empty( $banner_url ) ) :
				?>
				<a href="<?php echo esc_url( $banner_url ); ?>" target="<?php echo esc_attr( $banner_target ); ?>">
					<?php echo wp_kses_post( $banner_text ); ?>
				</a>
				<?php
			else :
				echo wp_kses_post( $banner_text );
			endif;
			?>
		</div>
	</div>
</div>
