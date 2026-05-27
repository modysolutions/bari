<?php
// featured items in search results
$featured_query_args = array(
    'post_type' => 'post',
    'posts_per_page' => -1,
    'post_status' => 'publish',
    // 's' => $search_string,
    'meta_key' => 'featured_in_search',
    'meta_value' => 1,
    'order_by' => 'date',
    'order' => 'DESC'
);
query_posts($featured_query_args);

if (have_posts()) {
?>
    <div class="new-search-results-featured">
        <?php
        while (have_posts()) {
            the_post();
            $search_string_lowercase = strtolower($search_string);
            $featured_words = " " . strtolower(get_field('feature_search_words')) . " "; //ojo al space delantero
            if ($featured_words != "" && strpos($featured_words, $search_string_lowercase) != false) {
                include(plugin_dir_path(__FILE__) . 'classic-search-result.php');
            }
        }
        ?>
    </div>
<?php
}
wp_reset_query();
?>