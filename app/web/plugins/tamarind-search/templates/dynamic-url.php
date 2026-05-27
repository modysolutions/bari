<?php

/**
 * Dynamic URL: Template for Search.
 *
 * @package Tamarind_Search
 */

namespace tamarind_search;

get_header();

$param = get_query_var('search');

// Redirect if not logged in.
redirect_if_not_logged_in(); ?>

<div class="super_title">
	<div class="wrap">
		<h1><?php echo __( 'Search', 'tm-search' ); ?></h1>
	</div>
</div>

<div id="tm-search" class="tm-layout-main tm-layout-wrapper dynamic-url-<?php echo esc_attr($param); ?>">
	<main class="tm-layout-main__content">
		<?php
		if (file_exists(PLUGIN_PATH . '/templates/dynamic-url-' . $param . '.php')) {
			require_once 'dynamic-url-' . $param . '.php';
		}
		?>
	</main>
</div>

<?php
get_footer();
