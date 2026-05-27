<?php
/**
 * My Subscription functions
 *
 * @package Tamarind_UserArea
 *
 * phpcs:disable Generic.WhiteSpace.DisallowSpaceIndent.SpacesUsed
 */

namespace tamarind_userarea;

defined( 'ABSPATH' ) || exit;

use function tamarind_subscriptions\subscription_plan\{get_data_plan};
use function tamarind_subscriptions\users\{get_date_expiration_plan};

/**
 * Get region name by region id
 *
 * @param string $id The region ID used to fetch the region name.
 * @return string
 */
function get_region_name( $id ) {
    return get_term_by( 'id', $id, 'geography' )->name;
}

/**
 * Get regions included in the subscription
 *
 * @return string
 */
function get_regions_included() {
    $regions_list = '';
    $region_count = 1;

    $data_plan            = get_data_plan();
    $subscription_regions = $data_plan['plan_geo_tax'];

    if ( $subscription_regions && ! empty( $subscription_regions ) ) {
        $size_region = count( $subscription_regions );
        foreach ( $subscription_regions as $region_id ) {
            $add_region    = ( $region_count > 1 ) ? ( ( $region_count === $size_region ) ? ' and ' : ', ' ) : '';
            $regions_list .= $add_region . get_region_name( $region_id );
            ++$region_count;
        }
    } else {
        $regions_list = 'All regions';
    }
    return $regions_list;
}

/**
 * Get the content types included in the subscription
 *
 * @param bool $is_included  Whether to include or exclude content types in the result.
 * @return string
 */
function contents_included( $is_included = true ) {
    $result = '';

    $terms_args        = array( 'hide_empty' => false );
    $all_content_types = get_terms( 'content_types', $terms_args );
    $data_plan         = get_data_plan();
    $terms_content     = $data_plan['plan_contents'];

    if ( '' === $terms_content ) {
        return false;
    }

    foreach ( $all_content_types as $content_type ) {
        $term_id  = $content_type->term_id;
        $validate = in_array( $term_id, $terms_content, true );

        if ( $validate === $is_included ) {
            $name_content = $content_type->name;

            switch ( $content_type->slug ) {
                case 'media':
                    $name_content = __( 'Marketing & retailing', 'tm-user-area' ) . '<br>' .
                    __( '(incl. advertising / retail image library)', 'tm-user-area' );
                    break;
                case 'live-alerts-eu':
                    $name_content = __( 'EU Regulatory alerts and monthly consolidation', 'tm-user-area' );
                    break;
                case 'live-alerts-us':
                    $name_content = __( 'US Regulatory alerts and monthly consolidation', 'tm-user-area' );
                    break;
                case 'database':
                    $name_content = __( 'Global market database: consolidated monthly', 'tm-user-area' );
                    break;
            }

			$template_data = array(
                'term_id'      => $term_id,
                'slug'         => $content_type->slug,
                'name'         => $content_type->name,
                'display_name' => $name_content,
                'included'     => $validate,
                'is_included'  => $is_included,
            );

            $template_path = plugin_dir_path( __FILE__ ) . '../template-parts/content-type-item.php';

            if ( file_exists( $template_path ) ) {
                ob_start();
				extract( $template_data );
                include $template_path;
                $result .= ob_get_clean();
            }
        }
    }

    return $result;
}

/**
 * Get the expiration date text of the user's subscription plan.
 *
 * @return string
 */
function get_date_expiration_text(): string {
    $result = '';

	$expiration_date_field = get_date_expiration_plan();
    if ( false !== $expiration_date_field ) {
        $expiration_date = new \DateTime( $expiration_date_field );
        $today_date      = new \DateTime( gmdate( 'Y-m-d' ) );

        $result .= 'Your subscription finishes on <b>' . $expiration_date->format( 'd F Y' ) . '</b>.<br />';
        $result .= 'Subscription will expire in <b>' . $expiration_date->diff( $today_date )->format( '%a' ) . ' days.</b>';
    }
    return $result;
}
