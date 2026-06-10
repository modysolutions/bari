<?php

/**
 * Functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 * @link https://github.com/timber/starter-theme
 */

namespace App;

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

add_action('admin_notices', function () : void {
	Timber::render('admin-notice.twig', [
		'title' => __('Notice', APP_THEME_DOMAIN),
		'notice' => sprintf(
			__(
				'This is a test notice from the theme located at %s.',
				APP_THEME_DOMAIN
			),
			__DIR__
		),
	]);
});

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
