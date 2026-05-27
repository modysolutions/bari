<?php

namespace tamarind_wp_cli;

use WP_CLI;

defined( 'ABSPATH' ) || exit;

if ( defined( 'WP_CLI' ) && \WP_CLI ) {
    WP_CLI::add_command( 'tamarind index', __NAMESPACE__ . '\Index_Handler' );
}

class Index_Handler {
    public function all() : void {
        $posts = get_posts(['post_type' => 'post', 'posts_per_page' => -1, 'fields' => 'ids']);
        $indexer = new \tamarind_search\Indexer();

        $progress = \WP_CLI\Utils\make_progress_bar('Indexing content for search', count($posts));
        foreach ($posts as $id) {
            $indexer->sync_post($id);
            $progress->tick();
        }
        $progress->finish();
        \WP_CLI::success("Index updated.");
    }
}