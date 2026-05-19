<?php

namespace App\Hooks;

class Theme {
    public function init() : void {
        $this->action();
    }

    public function action() : void {
        add_action('after_switch_theme', [$this, 'after_switch_theme']);
    }

    public function after_switch_theme() : void {
        if (get_option('scaffold_defaultPosts')) {
            return;
        }
        wp_delete_post(1, true);
        wp_delete_post(2, true);
        wp_delete_post(3, true);
        wp_delete_comment(1, true);

        $home_page = array(
            'post_type' => 'page',
            'post_title' => 'Home',
            'post_status' => 'publish',
            'post_author' => 1,
            'post_name' => '',
            'page_template' => 'home.php'
        );
        $post_id = wp_insert_post($home_page);
        update_option('page_on_front', $post_id);
        update_option('show_on_front', 'page');
        update_post_meta($post_id, '_yoast_wpseo_metadesc', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.');

        add_option('scaffold_defaultPosts', 'removed');
    }
}