<?php
/**
 * Dynamic URL: Saved Searches.
 *
 * @package Tamarind_Search
 *
 */

namespace tamarind_search;

defined( 'ABSPATH' ) || exit; ?>

<section class="tm-module tm-module--light">
    <h2>
        <?php _e( 'My search history', 'tm-search' ); ?>
    </h2>
    <?php echo display_user_saved_searches(); ?>
</section>
