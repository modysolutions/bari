<?php

namespace tamarind_search;

defined('ABSPATH') || exit;

add_filter('get_search_form', __NAMESPACE__ . '\\get_search_form', 10, 2);

function get_search_form( $form, $args ): string {
    ob_start();
    require \tamarind_search\PLUGIN_PATH  . '/templates/searchform.php';
    return ob_get_clean();
}
