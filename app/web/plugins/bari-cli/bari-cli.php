<?php

/**
 * Plugin Name: Bari CLI
 * Description: WP-CLI commands and database migration engine for the Barí WordPress Framework.
 * Version:     1.0.0
 * Author:      Mody Solutions
 * License:     GPL-2.0-or-later
 */

namespace BariCli;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ─── Constants ───────────────────────────────────────────────────────────────

define( 'BARI_CLI_VERSION', '1.0.0' );
define( 'BARI_CLI_DIR', __DIR__ );

/**
 * BARI_MIGRATIONS_DIR
 *
 * Override this constant in wp-config.php to store migrations outside the plugin:
 *
 *   define( 'BARI_MIGRATIONS_DIR', get_template_directory() . '/database/migrations' );
 */
if ( ! defined( 'BARI_MIGRATIONS_DIR' ) ) {
    define( 'BARI_MIGRATIONS_DIR', __DIR__ . '/migrations' );
}

// ─── Autoloader ──────────────────────────────────────────────────────────────

spl_autoload_register( function ( string $class ): void {
    $prefix   = 'BariCli\\';
    $base_dir = BARI_CLI_DIR . '/src/';

    if ( strncmp( $prefix, $class, strlen( $prefix ) ) !== 0 ) {
        return;
    }

    $relative = substr( $class, strlen( $prefix ) );
    $file     = $base_dir . str_replace( '\\', '/', $relative ) . '.php';

    if ( file_exists( $file ) ) {
        require $file;
    }
} );

// ─── WP-CLI Commands ─────────────────────────────────────────────────────────

if ( defined( 'WP_CLI' ) && \WP_CLI ) {
    \WP_CLI::add_command( 'migration', CLI\MigrationCommand::class );
}

