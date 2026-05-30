<?php

namespace App\Hooks;

use Timber\Timber;

class Views {
    function init(): void {
        $this->action();
    }

    function action(): void {
        if (class_exists('Timber\Timber')) {
            add_action('timber/context', [$this, 'timber_context']);
            add_filter('timber/twig', [$this, 'timber_twig']);
            add_filter('timber/locations', [$this, 'timber_locations']);
        } else {
            add_action('admin_notices', [$this, 'admin_notice']);
        }
    }

    public function admin_notice(): void {
        $url = esc_url(admin_url('plugins.php'));
        echo <<<EOF
<div class='error'>
  <p>
    Timber not activated. Make sure you activate the plugin in <a href='{$url}'>{$url}</a>
  </p>
</div>
EOF;
    }

    public function timber_context(array $context): array {

        $context = Timber::context();
        if (function_exists('get_fields')) {
            $context['options'] = get_fields('options');
        }

        return array_merge($context, [
            'header_menu' => Timber::get_menu('header_menu'),
            'footer_top_menu' => Timber::get_menu('footer_top_menu'),
            'footer_bottom_menu' => Timber::get_menu('footer_bottom_menu'),
        ]);
    }

    public function timber_twig(\Twig\Environment $twig): \Twig\Environment {
        $twig->addFilter(new \Twig\TwigFilter('admin_url', function ($filename) {
            return admin_url($filename);
        }));

        $twig->addFilter(new \Twig\TwigFilter('print_id', function ($string) {
            $id = " id=\"{$string}\" ";
            return $string ? $id : '';
        }));

        return $twig;
    }

    public function timber_locations(array $locations): array {
        $locations['theme'] = [
            APP_THEME_DIR.'/app/Views',
        ];
        return $locations;
    }
}