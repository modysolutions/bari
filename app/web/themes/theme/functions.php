<?php

/**
 * Functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 * @link https://github.com/timber/starter-theme
 */

namespace App;

use App\Hooks\Acf;
use App\Hooks\Gutenberg;
use App\Hooks\App;
use App\Hooks\Security;
use App\Hooks\Theme;
use App\Hooks\Views;
use Timber\Timber;

// Load Composer dependencies.
require_once ABSPATH.'/../vendor/autoload.php';

define('APP_THEME_DIR', __DIR__);
define('APP_THEME_URI', get_template_directory_uri());
define('APP_THEME_DOMAIN', 'theme');

if (!is_plugin_active('advanced-custom-fields-pro/acf.php') || !\Timber::class) {
    add_action('admin_notices', function () {
        $acf_missing = !is_plugin_active('advanced-custom-fields-pro/acf.php');
        $timber_missing = !\Timber::class;

        if ($acf_missing || $timber_missing) {
            require_once APP_THEME_DIR .'/app/Views/admin/plugin-notice.php';
        }
    });
}

$gutenberg = new Gutenberg();
$gutenberg->init();

$mody = new App();
$mody->init();

$security = new Security();
$security->init();

$theme = new Theme();
$theme->init();

$views = new Views();
$views->init();
