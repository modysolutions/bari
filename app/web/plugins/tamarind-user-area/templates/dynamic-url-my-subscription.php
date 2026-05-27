<?php
/**
 * Dynamic URL:  My Subscription.
 *
 * @package Tamarind_UserArea
 *
 * phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed
 */

namespace tamarind_userarea;

defined( 'ABSPATH' ) || exit;

use function tamarind_subscriptions\subscription_plan\{get_data_plan};

$data_plan  = get_data_plan();
$plan_name  = $data_plan['plan_name'];

if ( ! $plan_name ) {
	echo '<div class="box-info">';
	echo wp_kses_post( get_field( 'no_subscription_message', 'option' ) );
	echo '</div>';
	return;
}
?>

<div class="ecig-subscription">
    <h2 class="usersubtitles">Subscriptions</h2>
	<div class="box-info">
		<p>My current subscription: <strong><?php echo esc_html( $plan_name ); ?></strong></p>
		<p><?php echo wp_kses_post( get_date_expiration_text() ); ?></p>
	</div>
    <div class="tm-layout-main subscription-includes mb-25">
        <div class="subscription-includes__included">
			<?php
			$include_icon = function_exists( 'tamarind_base\get_svg_icon' ) ? \tamarind_base\get_svg_icon( 'include', '', esc_attr__( 'Included', 'tamarind-user-area' ) ) : '<i class="fa fa-check" style="color:#c1d556;"></i>';
			?>
            <h4>Includes:</h4>
            <ul class="subscription-features">
                <li><strong>Region:</strong> <span> <?php echo wp_kses_post( get_regions_included() ); ?></span></li>
                <li>Newsletters: <span><?php echo $include_icon; ?></span></li>
                <li>Search: all archives <span><?php echo $include_icon; ?></span></li>
                <?php echo contents_included(); ?>
            </ul>
        </div>
        <div class="subscription-includes__excluded">
            <h4>Doesn't include:</h4>
            <ul class="subscription-features not-included-features">
                <?php
                    $included = false;
                    echo contents_included( $included );
                ?>
            </ul>
        </div>
    </div>
	<div class="box-info">
		<?php
		echo wp_kses_post( get_field( 'subscription_upgrade', 'option' ) );
		?>
	</div>
</div>
