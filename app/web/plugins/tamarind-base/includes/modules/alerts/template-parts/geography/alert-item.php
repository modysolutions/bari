<?php
/**
 * Template Part: Single Alert Item
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\alerts;

defined( 'ABSPATH' ) || exit;


if ( ! isset( $template_vars ) ) {
	exit;
}
$user_can_read_alert = $template_vars['user_can_read_alert'] ?? false;
$alert_item_counter = $template_vars['alert_item_counter'] ?? 0;

$post_id = get_the_ID();

$cache_args = array(
	'subscription_plan' => \tamarind_subscriptions\subscription_plan\get_user_plan_id(),
	'post_id' => $post_id
);
$cache_key = 'alert_item_' . md5( serialize( $cache_args ) );
$cache_group = 'alerts';
$geography_terms = wp_cache_get( $cache_key, $cache_group );
if(!$geography_terms){
	$geography_terms = wp_get_post_terms( $post_id, 'geography' );
	wp_cache_set( $cache_key, $geography_terms, $cache_group, 10 * MINUTE_IN_SECONDS );
}
$geoterms_display = '';
foreach ( $geography_terms as $geo_term ) {
	$geoterms_display .= $geo_term->name;
	if ( ! empty( $geo_term->parent ) ) {
		$parent_term = get_term_by( 'id', $geo_term->parent, 'geography' );
		if ( $parent_term && ! empty( $parent_term->name ) ) {
			$geoterms_display = str_replace( $parent_term->name, '', $geoterms_display );
		}
	}
}

$can_read_class = $user_can_read_alert ? 'canread' : '';
$js_function_name = $user_can_read_alert ? 'multiTextToggleCollapse' : 'multiTextNoLogin';
$ellipsis_class = 'geoalert-' . $alert_item_counter;
?>
<dt class="<?php echo esc_attr( $can_read_class ); ?>">
	<p class="alert-title" style="position:static;margin:0">
		<?php echo get_the_date(); ?>
	</p>
</dt>
<dd class="tm-list__item--alert">
	<div id="tm-list__item--alert-<?php echo $post_id; ?>" class="tm-list--alert-content tm-list--alert-content--archive tm-list--alert-content--grey <?php echo esc_attr( $user_can_read_alert ? '' : 'card-alert-no-login ' ); ?><?php echo esc_attr( $ellipsis_class ); ?>">
		<?php
		if ( $user_can_read_alert ) {
			echo apply_filters( 'the_content', ( get_the_content() ) ); 
		} else {
			$content = apply_filters( 'the_content', get_the_content() );
			$allowed_html = array(
				'a'      => array(
					'id' => true,
					'title' => true,
				),
				'strong' => array(),
				'b'      => array(),
			);
			$clear_post = wp_kses( $content, $allowed_html );
			$words = explode( ' ', $clear_post );
			$first_words = implode( ' ', array_slice( $words, 0, 15 ) );
			echo '<p>' . $first_words . '...</p>';
		}
		?>	
	</div>
	<?php if ( $user_can_read_alert ){ ?>
		<button class="tm-list--alert-button" aria-controls="tm-list__item--alert-<?php echo $post_id; ?>" aria-expanded="false" title="Mostrar más">
			<i class='fa fa-plus'></i>
		</button>
	<?php } ?>
</dd>
