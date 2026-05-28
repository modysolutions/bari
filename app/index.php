<?php
/**
 * Front to the WordPress application.
 */
define( 'WP_USE_THEMES', true );
define( 'ROOT_DIR', __DIR__ );

// Point to the custom 'wp' core directory
require __DIR__ . '/wp/wp-blog-header.php';