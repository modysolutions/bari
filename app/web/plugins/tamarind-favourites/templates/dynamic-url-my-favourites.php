<?php
/**
 * Dynamic URL: My Favourites.
 *
 * @package Tamarind_Favourites
 */

namespace tamarind_favourites;

defined( 'ABSPATH' ) || exit; ?>

<section class="tm-module tm-module--light">
	<h2 class="tm-title">
		<?php esc_html_e( 'My Favourites', 'tm-favourites' ); ?>
	</h2>
	<?php tamarind_favourites_display(); ?>
</section>
