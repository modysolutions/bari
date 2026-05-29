<?php

/**
 * Functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 * @link https://github.com/timber/starter-theme
 */

namespace App;

use App\Hooks\App;
use App\Hooks\Blocks;
use App\Hooks\Gutenberg;
use App\Hooks\Patterns;
use App\Hooks\Security;
use App\Hooks\Theme;
use App\Hooks\Views;
use Timber\Timber;

// Load Composer dependencies.
require_once ABSPATH . '/../vendor/autoload.php';

define( 'APP_THEME_DIR',    __DIR__ );
define( 'APP_THEME_URI',    get_template_directory_uri() );
define( 'APP_THEME_DOMAIN', 'theme' );

// ─── Dependency notices ───────────────────────────────────────────────────────

add_action( 'admin_notices', function () {
    $acf_missing    = ! ( function_exists( 'acf' ) || class_exists( 'ACF' ) );
    $timber_missing = ! class_exists( 'Timber\Timber' );

    if ( $acf_missing || $timber_missing ) {
        require_once APP_THEME_DIR . '/app/Views/admin/plugin-notice.php';
    }
} );

// ─── Boot services ────────────────────────────────────────────────────────────

( new Acf() )->init();
( new App() )->init();
( new Blocks() )->init();
( new Gutenberg() )->init();
( new Patterns() )->init();
( new Security() )->init();
( new Theme() )->init();
( new Views() )->init();
