<?php
/**
 * Template for Recomendations layout.
 * 
 * @package Tamarind_UserHome
 */

namespace tamarind_userhome;

defined('ABSPATH') || exit;


// Get the layout values
$title = get_sub_field('title');
$number_of_items = get_sub_field('number_of_items');
$container_style = (get_sub_field('container_style')) ? ' tm-module--' . get_sub_field('container_style') : '';
$item_style = get_sub_field('item_style');

// Get Recomendations posts
$RecomendationsContents = get_query_recommendations($number_of_items); ?>

<section class="recomendations-module tm-module<?php echo esc_attr($container_style); ?>">

    <?php if ($title) { ?>
        <h2 class="new-module-label"><?php echo esc_html($title); ?></h2>
    <?php } ?>

    <div class="recomendations-module__inner">
        <?php \tamarind_base\print_slider($RecomendationsContents, 'default', 'readmore', $item_style); ?>
    </div>

</section>