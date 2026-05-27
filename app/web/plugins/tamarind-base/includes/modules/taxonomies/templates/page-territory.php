<?php
/**
 * Template Name: Page Territory
 *
 * @package Tamarind_Base
 */

namespace tamarind_base\taxonomies;

defined( 'ABSPATH' ) || exit;

get_header(); ?>

<div id="geography-content">
	<div class="super_title">
		<div class="wrap">
			<h1><?php the_title(); ?></h1>
		</div>
	</div>

	<div id="content-area" class="tm-layout-main tm-layout-wrapper">
		<main class="tm-layout-main__content tm-archive-taxonomy">
			<?php if ( ! empty( get_the_content() )  ) : ?>
					<div class="term-description term-description-geographies mt-30 mb-30">
						<?php the_content(); ?>
					</div>
				</div>
			<?php endif; ?>

			<?php require plugin_dir_path( __FILE__ ) . '../template-parts/modules.php'; ?>

			<?php if ( ! is_user_logged_in() ) : ?>
				<?php require plugin_dir_path( __FILE__ ) . '../template-parts/banner-cta-register.php'; ?>
			<?php endif; ?>			
		</main>
	</div>
</div>
<?php
get_footer();
