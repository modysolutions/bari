<?php
/**
 * Search template
 *
 * @package Tamarind_Search
 */

namespace tamarind_search;

use function tamarind_templates_custom_lists\get_taxonomy_content_types_args;

defined( 'ABSPATH' ) || exit;


get_header();

$current_page = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
$title_page   = sprintf(
        esc_html__( 'Search Results for: %s', 'tamarind' ),
        '<span class="search-term">' . get_search_query() . '</span>'
);
?>

<div class="super_title">
    <div class="wrap"><?php echo  $title_page ; ?></div>
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
                    \tamarind_base\print_post_card( get_post(), 'grid', 'horizontal', 'taxonomy' );
                endwhile;
                ?>
            </ul>
            <div class="tm-pagination">
                <?php
                echo paginate_links(
                        array(
                                'format'   => '?paged=%#%',
                                'current'  => max( 1, $current_page ),
                                'mid_size' => 5,
                        )
                );
                ?>
            </div>
        <?php endif; ?>
    </main>
</div>

<?php get_footer(); ?>
