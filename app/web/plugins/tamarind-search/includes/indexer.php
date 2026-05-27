<?php

/**
 * Indexer for search results
 *
 * @package Tamarind_Search
 */
namespace tamarind_search;

class Indexer {
    public function sync_post($post_id): void {
        $post = get_post($post_id);

        $topics = wp_get_post_terms($post_id, 'topics', ['fields' => 'names']) ?? array();
        $geos = wp_get_post_terms($post_id, 'geography', ['fields' => 'names']) ?? array();
        $content_type = wp_get_post_terms($post_id, 'content_types', ['fields' => 'names']) ?? array();

        $buffer = $post->post_title . ' ' . $post->post_title . ' ' .
                  $post->post_content . ' ' .
                  implode(' ', $topics) . ' ' .
                  implode(' ', $content_type) . ' ' .
                  implode(' ', $geos);

        global $wpdb;
        $wpdb->replace($wpdb->prefix . 'tamarind_search_index', [
            'post_id' => $post_id,
            'content_buffer' => strip_tags($buffer),
            'post_type' => $post->post_type,
            'tax_topics' => json_encode($topics),
            'tax_geographies' => json_encode($geos),
            'tax_content_type' => json_encode($content_type),
            'last_updated' => current_time('mysql'),
            'relevancy_score' => get_field('relevancy_level', $post_id) ?? 0,
            'published_date' => $post->post_date,
        ]);
    }
}