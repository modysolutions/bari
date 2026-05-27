<?php

namespace tamarind_wp_cli;

use WP_CLI;

if ( defined( 'WP_CLI' ) && \WP_CLI ) {
    WP_CLI::add_command( 'tamarind post', __NAMESPACE__ . '\Post_Handler' );
}
class Post_Handler {
    public function relevancy(): void {
        if ( function_exists( 'set_all_content_type_relevancy_level' ) ) {
            set_all_content_type_relevancy_level();
        }
        \WP_CLI::success('Relevancy calculated');
    }

    public function delete_featured(): void {
        if ( function_exists( 'purge_featured_posts' ) ) {
            purge_featured_posts();
        }
        \WP_CLI::success('Purge Featured Posts');
    }

    public function set_toc(): void {
        if ( function_exists( '\tamarind_toc\replace_toc_on_save_post' ) ) {
            $query = new \WP_Query( array(
                'post_type' => array('post'),
                'posts_per_page' => -1,
            ) );
            $posts = $query->get_posts();
            \WP_CLI::success("Total Posts: " . count( $posts ));

            $counter = 0;
            foreach ( $posts as $post ) {
                if ( \tamarind_toc\replace_toc_on_save_post( $post->ID ) ) {
                    $counter++;
                }
            }
            \WP_CLI::success("Posts Toc updated: " . $counter);

            $option_key = 'generate_post_pdf';
            update_option($option_key, '');
            \WP_CLI::success("Clear generate-Post-PDFs");
        }
    }
}