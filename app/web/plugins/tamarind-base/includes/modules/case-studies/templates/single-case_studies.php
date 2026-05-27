<?php
/**
 * The template for displaying single "Case Studies" CPT.
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\case_studies;

defined( 'ABSPATH' ) || exit;


get_header();

if ( is_plugin_active( 'subscribers-report/subscribers-report.php' ) && ( is_user_logged_in() ) ) {
	do_action( 'subscribers_report_add_pageview' );
	do_action( 'subscribers_report_add_download' );
}

while ( have_posts() ) :
	the_post();
	?>
	<article clas="case-study">
		<header class="header-ca" style="background-image:url(<?php the_field( 'header_bg' ); ?>);?>">
			<div class="tm-layout-wrapper text-center">
				<h1 class="entrititle mt-0 mb-10 text-white "><?php the_title(); ?></h1>
			</div>
			<div class="arrow-container text-center">
				<a href="#landing-content" class="text-white"><i class="fa fa-angle-down"></i></a>
			</div>
		</header>
		<main>
			<section class="block block-content-casetudy" id="landing-content">
				<div class="tm-layout-wrapper">
					<div class="tm-layout-grid tm-layout-grid--xl">
						<div>
							<h3 class="text-dark-violet font-size-25 mt-0 mb-0"><?php the_field( 'profile_title' ); ?></h3>
							<div class="profile-text-content font-size-20 mt-20" style="max-width:450px">
								<?php the_field( 'profile_details' ); ?>
							</div>
						</div>
						<div>
							<h3 class="text-dark-violet font-size-25 mt-0 mb-0"><?php the_field( 'challenges_title' ); ?></h3>
							<div class="challenges-list-content font-size-15 mt-20 doteadalista">
								<?php the_field( 'challenges-list' ); ?>
							</div>
						</div>
					</div>
				</div>
			</section>

			<?php
				$solution_bg = get_field( 'solution_background-image' );

			if ( empty( $solution_bg ) ) {
				$plugin_root_url = plugin_dir_url( dirname( __FILE__, 4 ) );
				$solution_bg     = $plugin_root_url . 'assets/images/case-studies-bg.jpg';
			}
			?>

			<section class="block block-content-case-solution" style="background-image:url('<?php echo esc_url( $solution_bg ); ?>');">
				<div class="tm-layout-wrapper">
					<div class="tm-layout-grid tm-layout-grid--xl">
						<div class="solution-contents" >
							<div style="max-width:500px"><?php the_field( 'solution_contents' ); ?></div>
						</div>
						<div class="form-customised-analysis">
							<?php
							$form_id                           = get_field( 'select_form' );
							$type_form                         = get_field( 'select_type_form' );
							$style_form                        = get_field( 'select_style_form' );
							$select_zoho_action_form           = get_field( 'select_zoho_action_form' );
							$select_title_form                 = get_field( 'select_title_form' );
							$select_description_form           = get_field( 'select_description_form' );
							$select_submit_form                = get_field( 'select_submit_form' );
							$select_show_title_and_description = ( get_field( 'select_show_title_and_description' ) ) ? get_field( 'select_show_title_and_description' ) : '';
							$select_show_title_post            = ( get_field( 'select_show_title_post' ) ) ? get_field( 'select_show_title_post' ) : '';

							$param_display_form = array(
								'form_id'                 => $form_id,
								'type_form'               => $type_form,
								'style_form'              => $style_form,
								'select_zoho_action_form' => $select_zoho_action_form,
								'title_form'              => $select_title_form,
								'description_form'        => $select_description_form,
								'submit_form'             => $select_submit_form,
								'select_show_title_and_description' => $select_show_title_and_description,
								'select_show_title_post'  => $select_show_title_post,
							);

							$downloadable_file = '';

							if ( is_plugin_active( 'tamarind-forms/tamarind-forms.php' ) ) {
								\tamarind_forms\display_form\display_module_form( $param_display_form, $downloadable_file );
							}

							?>

						</div>
					</div>
				</div>
			</section>
		</main>
		<footer class="block">
			<div class="tm-layout-wrapper">
			<section class="related">
			<?php
			$related_items = get_field( 'related_items' );

			if ( $related_items ) {
				?>
				<div class="related-open related-open-single">
					<h2><?php esc_html_e( 'Read related content', 'tamarind_base' ); ?></h2>
					<div class="related-row tm-layout-grid tm-layout-grid--medium">
					<?php foreach ( $related_items as $related_item ) : ?>
						<?php
						setup_postdata( $related_item );
						$imagen          = wp_get_attachment_image_src( get_post_thumbnail_id( $related_item->ID ), 'large' );
						$plugin_root_url = plugin_dir_url( dirname( __FILE__, 4 ) );
						$related_arrow   = $plugin_root_url . 'assets/images/case-studies-arrow.png';
						?>
						<div class="related-post-block">
							<a href="<?php the_permalink(); ?>">
								<div class="background-related" style="background-image:url(<?php echo esc_url( $imagen[0] ); ?>)"></div>
							</a>
							<div class="related-box">
							<a class="title-link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
							<a class="arrow-link" href="<?php the_permalink(); ?>"><img src="<?php echo esc_url( $related_arrow ); ?>"></a>
							</div>
						</div>
					<?php endforeach; ?>
						</div>
					</div>
					<?php wp_reset_postdata(); ?>
				</div>
			<?php } ?>
			</section>
			</div>
		</footer>
	</article>

	<?php
endwhile;
get_footer();
