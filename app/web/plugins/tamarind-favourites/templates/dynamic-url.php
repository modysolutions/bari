<?php
/**
 * Dynamic URL: Template for Favourites.
 *
 * @package Tamarind_Favourites
 */

namespace tamarind_favourites;

get_header();

$param = get_query_var( 'favourites' );

// Redirect if not logged in.
if ( redirect_if_not_logged_in() ) {
	return;
} ?>

<div class="super_title">
	<div class="wrap">
		<h1><?php esc_html_e( 'Favourites', 'tm-favourites' ); ?></h1>
	</div>
</div>

<div id="tm-user-favourites" class="tm-layout-main tm-layout-wrapper tm-layout-main--sidebar tm-layout-main--sidebar-left dynamic-url-<?php echo esc_attr( $param ); ?>" style="--gap:36px;">
	<main class="tm-layout-main__content">
		<?php
		if ( file_exists( PLUGIN_PATH . '/templates/dynamic-url-' . $param . '.php' ) ) {
			require_once 'dynamic-url-' . $param . '.php';
		}
		?>
	</main>
	<aside class="tm-layout-main__aside">
		<?php
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo tamarind_favourites_filter();
		?>
	</aside>
</div>

<?php
get_footer();
