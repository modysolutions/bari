<?php
/**
 * Handle Redirect search results
 *
 * @package Tamarind_Search
 */

namespace tamarind_search;

defined( 'ABSPATH' ) || exit;
 
/**
 * Redirect search results to Geography and Content Types pages based on search terms 
 */
add_action('pre_get_posts', __NAMESPACE__ . '\redirect_search');

function redirect_search($query): void {
    // Default post-type to avoid undefined index errors
    $pt = 'post'; // Prevents "undefined index 'post_type'" errors caused by some plugins

    // Get the post-type from the query
    if (isset($query->query['post_type'])) {
        $pt = $query->query['post_type'];
    }

    // Check if it's not an admin query, not a product search, and is the main search query
    if (!is_admin() && $pt != 'product' && $query->is_main_query() && $query->is_search()) {
        // Get the search term and sanitize it
        $word = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $word = mb_strtolower($word, 'UTF-8');

	    // Save the search for logged-in users
	    if (is_user_logged_in()) {
		    $search_url = home_url($_SERVER['REQUEST_URI']); // Capture the full URL with all parameters
		    save_user_search($word, $search_url);
	    }

	    $word_lowercase = mb_strtolower($word, 'UTF-8');
	    $option_key = 'tm_search_redirect_' . sanitize_title($word_lowercase);

	    // First, check for the cached option
	    $cached_redirect_url = get_option($option_key);
	    if ($cached_redirect_url) {
		    wp_safe_redirect(esc_url($cached_redirect_url));
		    exit;
	    }

        // Check if the search term exists in the redirect map
        if (isset($redirect_map[$word])) {
            wp_safe_redirect(home_url($redirect_map[$word]));
            exit;
        }
    }
}

/**
 * Check ACF repeatable field for a match with the search term
 *
 * @param array $acf_field The ACF repeatable field
 * @param string $term_field The field name for the term ID
 * @param string $keyword_field The field name for the keywords
 * @param null|string $taxonomy The taxonomy to which the term belongs
 *
 * @return void
 */
function check_acf_field_for_match( array $acf_field, string $term_field, string $keyword_field, ?string $taxonomy = null ): void {
    foreach ($acf_field as $row) {
        if (empty($row[$term_field]) || empty($row[$keyword_field])) {
            continue;
        }
        $keywords = mb_strtolower($row[$keyword_field], 'UTF-8');
        $keywords_array = explode(",", $keywords);

        foreach($keywords_array as $keyword) {
            $term = get_term_by('id', $row[$term_field], $taxonomy);
			$post = get_post($row[$term_field]);
            
            $redirect_url = false;
            if ($term && !is_wp_error($term)) {
	            $redirect_url = home_url("/{$taxonomy}/{$term->slug}");
            } elseif($post && !$taxonomy) {
	            $redirect_url = get_permalink($post);
	        }

			if ($redirect_url) {
				$option_key = 'tm_search_redirect_' . sanitize_title($keyword);
				update_option($option_key, $redirect_url);
			}
        }
    }
}
