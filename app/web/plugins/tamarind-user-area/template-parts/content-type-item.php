<?php
/**
 * Template part for displaying a content type item
 *
 * @package TamarindUserArea
 *
 * @param array $args {
 *     @var int    $term_id
 *     @var string $slug
 *     @var string $name
 *     @var string $display_name
 *     @var bool   $included
 *     @var bool   $is_included
 * }
 */

defined( 'ABSPATH' ) || exit;
?>

<li data-canread="<?php echo esc_attr( $included ); ?>" class="type-<?php echo esc_attr( $slug ); ?>">
	<div>
		<span><?php echo wp_kses_post( $display_name ); ?></span>
		<?php
		if ( $is_included ) {
			echo function_exists( 'tamarind_base\get_svg_icon' )
				? \tamarind_base\get_svg_icon( 'include', '', esc_attr__( 'Included', 'tamarind-user-area' ) )
				: '<i class="fa fa-check" style="color:#c1d556;"></i>';
		} else {
			echo function_exists( 'tamarind_base\get_svg_icon' )
				? \tamarind_base\get_svg_icon( 'no-include', '', esc_attr__( 'Excluded', 'tamarind-user-area' ) )
				: '<i class="fa fa-times" style="color:#AC4343;"></i>';
		}
		?>
	</div>
</li>