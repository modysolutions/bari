<?php
/**
 * Template Name: Video demos New
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\page_templates;

defined( 'ABSPATH' ) || exit;

use function tamarind_subscriptions\subscription_plan\{get_data_plan};

get_header();
if ( ! get_field( 'public_videos' ) ) {
	$data_plan = get_data_plan();
}
?>
<div class="super_title">
	<div class="wrap"><h1><?php the_title(); ?></h1></div>
</div>
<div id="main-content" class="content-sidebar-wrap" style="margin-top:50px;">
	<div class="tm-layout-wrapper">
		<?php
		while ( have_posts() ) {
			the_post();
			?>
			<div class="description-content">
				<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
					<div class="entry-content">
						<?php the_content(); ?>
					</div>
				</article>
			</div> 
			<?php
		}
		?>

		<?php
		if ( have_rows( 'videos' ) ) {
			?>
			<section class="videos-training mb-60 tm-layout-grid tm-layout-grid--xl">
				<?php
				while ( have_rows( 'videos' ) ) {
					the_row();
					$video_title       = get_sub_field( 'video_title' );
					$video_embed_code  = get_sub_field( 'video_embed_code' );
					$video_description = get_sub_field( 'video_description' );

					$video_html = '<div class="video-item"><article class="video-training">
						<h3 class="video-training-title">
							' . esc_html( $video_title ) . '
						</h3>
						<div class="video-training-video mt-20 mb-20">
							' . $video_embed_code . '
						</div>
						<div class="video-training-description bg-lightviolet mt-20 mb-20">
						' . wp_kses_post( $video_description ) . '
						</div>
					</article></div>';

					if ( ! get_field( 'public_videos' ) ) {
						$videos_subscription_plan = get_sub_field( 'videos_subscription_plan' );
						if ( ! is_array( $videos_subscription_plan ) ) {
							$videos_subscription_plan = array();
						}

						if ( in_array( $data_plan['plan_id'], $videos_subscription_plan, true ) ) {
							echo $video_html;
						}
					} else {
						echo $video_html;
					}
				}
				?>
			</section>
			<?php
		}
		?>
	</div>
</div>

<?php
get_footer();
