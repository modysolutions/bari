<?php
/**
 * The template for displaying Content Types Taxonomy Archive.
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\taxonomies;

use function tamarind_templates_custom_lists\get_taxonomy_content_types_args;

defined( 'ABSPATH' ) || exit;


get_header();

$current_page = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

$term_content_type = get_term_by( 'slug', get_query_var( 'content_types' ), 'content_types' );
$content_type      = $term_content_type->name ? $term_content_type->name : '';

$geo_slug         = get_query_var( 'geography' );
$should_add_topic = array_key_exists( 'tm-topics', $_GET ) && ! empty( $_GET['tm-topics'] );
$topic = false;
$term_topic = false;
if( $should_add_topic ) {
	$topic = sanitize_text_field( $_GET['tm-topics'] );
	$term_topic = get_term_by( 'slug', $topic, 'topics' );
}
if ( ( 'americas' === $geo_slug ) ) {
	$geography = __( ' in Americas', 'tamarind' );
	$locations = array( 'north-america', 'south-america', 'central-america' );
	$args = \tamarind_templates_custom_lists\get_taxonomy_content_types_args(
		$term_content_type,
		$locations
	);
	query_posts( $args );
} elseif ( 'europe' === $geo_slug && $should_add_topic ) {
	$locations = array( 'europe' );
	$geography  = sprintf(__(" & %s in Europe", 'tamarind'), $term_topic->name);;
	$args = \tamarind_templates_custom_lists\get_taxonomy_content_types_args(
		$term_content_type,
		$locations,
		array( $topic )
	);
	query_posts( $args );
} elseif ( $should_add_topic ) {
	$geography = sprintf(__(" & %s", 'tamarind'), $term_topic->name);
	$args = \tamarind_templates_custom_lists\get_taxonomy_content_types_args(
		$term_content_type,
		array(),
		array( $topic )
	);
	query_posts( $args );
} else {
	$term_geo  = get_term_by( 'slug', get_query_var( 'geography' ), 'geography' );
	$geography = ( isset ( $term_geo->name ) && $term_geo->name != "" ) ? ' in ' . $term_geo->name : '';
}

$title_page = $content_type . $geography;

if ( 'alerts' === $term_content_type->slug ) {
	if ( is_plugin_active( 'subscribers-report/subscribers-report.php' ) && ( is_user_logged_in() ) ) {
		do_action( 'subscribers_report_add_pageview' );
	}
}
?>

	<div class="super_title">
		<div class="wrap"><?php echo esc_html( $title_page ); ?></div>
	</div>

	<div id="content-area" class="tm-layout-main tm-layout-wrapper">
		<main class="tm-layout-main__content tm-archive-taxonomy">	

			<?php
			$term_description = term_description();
			if ( ! empty( $term_description ) ) {
				?>
				<div class="taxonomy-description">
					<?php echo wp_kses_post( $term_description ); ?>
				</div>
				<?php
			}

			if ( have_posts() ) :
				?>
				<ul class="tm-layout-grid tm-layout-grid--fullwidth">
					<?php
					while ( have_posts() ) :
						the_post();
						$is_alert = \tamarind_base\is_alert();

						if ( $is_alert ) {
							// Use alert card for alert content type.
							\tamarind_base\print_post_card( get_post(), 'grid', 'horizontal-alert', 'taxonomy' );
						} else {
							// Use regular card for other content types.
							\tamarind_base\print_post_card( get_post(), 'grid', 'horizontal', 'taxonomy' );
						}
					endwhile;
					?>
				</ul>
				<div class="tm-pagination">
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo paginate_links(
						array(
							'format'   => '?paged=%#%',
							'current'  => max( 1, $current_page ),
							'mid_size' => 5,
						)
					);
					?>
				</div>
				<?php
			endif;

			$bottom_term_text = get_field( 'bottom_description', 'content_types_' . $term_content_type->term_id );
			if ( $bottom_term_text && ! empty( $bottom_term_text ) ) {
				?>
				<div class="taxonomy-description">
					<?php echo wp_kses_post( $bottom_term_text ); ?>
				</div>
				<?php
			}
			?>
		</main>
	</div>

<?php get_footer(); ?>
