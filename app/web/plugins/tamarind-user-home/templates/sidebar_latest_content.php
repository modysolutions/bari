<?php

/**
 * Template for Latest Content layout.
 * 
 * @package Tamarind_UserHome
 */

namespace tamarind_userhome;

defined('ABSPATH') || exit;


// Get the layout values
$title = get_sub_field('title');
$number_of_items = get_sub_field('number_of_items');
$container_style = (get_sub_field('container_style')) ? ' tm-sidebar-module--' . get_sub_field('container_style') : '';
$item_style = get_sub_field('item_style');

// Get the latest content
$widgetLatestContents = get_query_latest_content($number_of_items); ?>

<section class="latest-content-sidebar tm-sidebar-module<?php echo esc_attr($container_style); ?>">
        
    <h2 class="tm-sidebar-module__title"><?php echo esc_html($title); ?></h2>

    <?php \tamarind_base\print_post_list($widgetLatestContents, 'default', $item_style); ?>

</section>