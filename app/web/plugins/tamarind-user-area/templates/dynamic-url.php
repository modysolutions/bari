<?php

/**
 * Dynamic URL: Template for User Area.
 *
 * @package Tamarind_UserArea
 */

namespace tamarind_userarea;

get_header();

$param = get_query_var( 'userarea' );

$rol = wp_get_current_user()->roles[0];

// Redirect if not logged in.
if ( redirect_if_not_logged_in() ) {
	return;
}

?>

<div class="super_title">
	<div class="wrap">
		<h1><?php echo 'User area '; ?></h1>
	</div>
</div>

<div id="tm-user-area" class="tm-layout-main tm-layout-wrapper tm-layout-main--sidebar tm-layout-main--sidebar-left dynamic-url-<?php echo esc_attr($param); ?>">
	<main class="tm-layout-main__content">
		<?php
		if ( file_exists( PLUGIN_PATH . '/templates/dynamic-url-' . $param . '.php' ) ) {
			require_once 'dynamic-url-' . $param . '.php';
		}
		?>
	</main>
	<aside class="tm-layout-main__aside">
		<?php display_userarea_menu( $rol ); ?>
	</aside>
</div>

<?php
get_footer();
