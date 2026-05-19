<?php

namespace App\Hooks;

class Mody {
    public function init(): void {
        add_action('init', [$this, 'wp_init'], 100);
        add_action('after_setup_theme', [$this, 'after_setup_theme']);
        add_action('wp_enqueue_scripts', [$this, 'wp_enqueue_scripts'], 100);
        add_action('template_redirect', [$this, 'template_redirect']);
        add_action('admin_head', [$this, 'admin_head']);
        add_action('wp_footer', [$this, 'wp_footer']);
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('wp', [$this, 'defer_scripts'], 10);
        add_filter('the_content', [$this, 'the_content'], 30);
        add_filter('acf/fields/wysiwyg/toolbars', [$this, 'wysiwyg_toolbar']);
    }

    public function wp_init(): void {
        register_nav_menus([
            'header_menu' => __('Header menu'),
            'footer_top_menu' => __('Footer top menu'),
            'footer_bottom_menu' => __('Footer bottom menu')
        ]);

        remove_action('wp_head', 'feed_links_extra', 3);
        remove_action('wp_head', 'feed_links', 2);
        remove_action('wp_head', 'rsd_link');
        remove_action('wp_head', 'wlwmanifest_link');
        remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);
        remove_action('wp_head', 'wp_generator');
        remove_action('wp_head', 'rest_output_link_wp_head');
        remove_action('wp_head', 'wp_oembed_add_discovery_links');
        remove_action('wp_head', 'wp_resource_hints', 2);
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('admin_print_styles', 'print_emoji_styles');
    }

    public function after_setup_theme(): void {
        add_theme_support('post-thumbnails');
        add_theme_support('title-tag');
    }

    public function wp_enqueue_scripts(): void {
        $config = [];
        foreach ($this->scripts() as $script) {
            wp_register_script($script['handle'], $script['url'], $script['deps'], $script['ver'], $script['args']);
            if ($script['handle'] === 'app') {
                wp_localize_script($script['handle'], 'AppSettings', $config);
            }
            wp_enqueue_script($script['handle']);
        }

        foreach ($this->styles() as $style) {
            wp_register_style($style['handle'], $style['url'], $style['deps'], $style['ver'], $style['media']);
            wp_enqueue_style($style['handle']);
        }
    }

    public function template_redirect(): void {
        if (is_category()) {
            return;
        }

        if (is_tax('news-category')) {
            return;
        }
        if (is_category() || is_tag() || is_date() || is_author() || is_tax() || is_attachment()) {
            global $wp_query;
            $wp_query->set_404();
        }
    }

    private function scripts(): array {
        $app = include(APP_THEME_DIR.'/dist/app.asset.php');
        return [
            [
                'handle' => 'app',
                'url' => APP_THEME_URI.'/dist/app.js',
                'ver' => $app['version'],
                'deps' => $app['dependencies'],
                'args' => ['in_footer' => false]
            ]
        ];
    }

    public function the_content(string $p): string {
        return preg_replace('/<p>\\s*?(<a rel=\"attachment.*?><img.*?><\\/a>|<img.*?>)?\\s*<\\/p>/s', '$1', $p);
    }

    public function wysiwyg_toolbar($toolbars): array {
        $toolbars['Simple Text'] = array();
        $toolbars['Simple Text'][1] = array(
            'bold',
            'italic',
            'underline',
        );
        return $toolbars;
    }

    public function admin_head(): void {
        echo '<style>.yoast-notice-go-premium, .wpseo-metabox-buy-premium, .yoast_premium_upsell_admin_block, .wpseo_content_cell #sidebar {display: none;}</style>';
    }

    public function wp_footer(): void {
        wp_deregister_script('wp-embed');
    }

    public function admin_menu(): void {
        if (function_exists('remove_menu_page')) {
            remove_menu_page('edit-comments.php');
        }

        remove_filter('update_footer', 'core_update_footer');
    }

    private function styles(): array {
        $app = include(APP_THEME_DIR.'/dist/app.asset.php');
        return [
            [
                'handle' => 'app',
                'url' => APP_THEME_URI.'/dist/app.css',
                'ver' => $app['version'],
                'deps' => null,
                'media' => 'all'
            ]
        ];
    }

    public function remove_wp_block_styles(): void {
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
    }

    public function defer_scripts(): void {
        add_filter('script_loader_tag', function ($tag, $handle, $src) {
            if ('app' === $handle) {
                return '<script src="'.esc_url($src).'" defer></script>';
            }
            return $tag;
        }, 10, 3);
    }
}
