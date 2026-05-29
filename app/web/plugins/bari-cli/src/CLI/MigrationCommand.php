<?php

namespace BariCli\CLI;

use BariCli\Migration\Migrator;

/**
 * Manages database migrations for the Barí Framework.
 *
 * ## EXAMPLES
 *
 *   wp migration create "create users table"
 *   wp migration migrate
 *   wp migration rollback
 *   wp migration status
 */
class MigrationCommand {

    private Migrator $migrator;

    public function __construct() {
        $this->migrator = new Migrator();
    }

    // ─── Commands ────────────────────────────────────────────────────────────

    /**
     * Create a new migration file.
     *
     * ## ARGUMENTS
     *
     * <name>
     * : Descriptive name for the migration (e.g. "create orders table").
     *
     * ## EXAMPLES
     *
     *   wp migration create "create orders table"
     *
     * @subcommand create
     *
     * @param string[] $args       Positional arguments.
     * @param string[] $assoc_args Named arguments / flags.
     */
    public function create( array $args, array $assoc_args ): void {
        if ( empty( $args[0] ) ) {
            \WP_CLI::error( 'Please provide a name for the migration. Example: wp migration create "create orders table"' );
        }

        $name = $args[0];

        try {
            $filepath = $this->migrator->create( $name );
            \WP_CLI::success( "Migration created: {$filepath}" );
        } catch ( \Throwable $e ) {
            \WP_CLI::error( "Failed to create migration: {$e->getMessage()}" );
        }
    }

    /**
     * Run all pending migrations.
     *
     * ## EXAMPLES
     *
     *   wp migration migrate
     *
     * @subcommand migrate
     *
     * @param string[] $args
     * @param string[] $assoc_args
     */
    public function migrate( array $args, array $assoc_args ): void {
        try {
            $ran = $this->migrator->migrate();
        } catch ( \Throwable $e ) {
            \WP_CLI::error( "Migration failed: {$e->getMessage()}" );
        }

        if ( empty( $ran ) ) {
            \WP_CLI::log( \WP_CLI::colorize( '%YNothing to migrate.%n' ) );
            return;
        }

        foreach ( $ran as $filename ) {
            \WP_CLI::log( \WP_CLI::colorize( "%GMigrated:%n  {$filename}" ) );
        }

        $count = count( $ran );
        \WP_CLI::success( "Ran {$count} " . ( $count === 1 ? 'migration' : 'migrations' ) . '.' );
    }

    /**
     * Roll back the last batch of migrations.
     *
     * ## EXAMPLES
     *
     *   wp migration rollback
     *
     * @subcommand rollback
     *
     * @param string[] $args
     * @param string[] $assoc_args
     */
    public function rollback( array $args, array $assoc_args ): void {
        try {
            $rolled_back = $this->migrator->rollback();
        } catch ( \Throwable $e ) {
            \WP_CLI::error( "Rollback failed: {$e->getMessage()}" );
        }

        if ( empty( $rolled_back ) ) {
            \WP_CLI::log( \WP_CLI::colorize( '%YNothing to roll back.%n' ) );
            return;
        }

        foreach ( $rolled_back as $filename ) {
            \WP_CLI::log( \WP_CLI::colorize( "%RRolled back:%n {$filename}" ) );
        }

        $count = count( $rolled_back );
        \WP_CLI::success( "Rolled back {$count} " . ( $count === 1 ? 'migration' : 'migrations' ) . '.' );
    }

    /**
     * Show the run status of all migrations.
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Render output in a particular format.
     * ---
     * default: table
     * options:
     *   - table
     *   - csv
     *   - json
     *   - yaml
     * ---
     *
     * ## EXAMPLES
     *
     *   wp migration status
     *   wp migration status --format=json
     *
     * @subcommand status
     *
     * @param string[]              $args
     * @param array<string, string> $assoc_args
     */
    public function status( array $args, array $assoc_args ): void {
        try {
            $rows = $this->migrator->status();
        } catch ( \Throwable $e ) {
            \WP_CLI::error( "Could not retrieve migration status: {$e->getMessage()}" );
        }

        if ( empty( $rows ) ) {
            \WP_CLI::log( \WP_CLI::colorize( '%YNo migrations found.%n' ) );
            return;
        }

        // Colour-code the status column for table output.
        $format = \WP_CLI\Utils\get_flag_value( $assoc_args, 'format', 'table' );

        if ( $format === 'table' ) {
            $rows = array_map( static function ( array $row ): array {
                $row['status'] = $row['status'] === 'Ran'
                    ? \WP_CLI::colorize( '%G' . $row['status'] . '%n' )
                    : \WP_CLI::colorize( '%Y' . $row['status'] . '%n' );

                $row['batch']       = $row['batch'] ?? '—';
                $row['executed_at'] = $row['executed_at'] ?? '—';

                return $row;
            }, $rows );
        }

        \WP_CLI\Utils\format_items(
            $format,
            $rows,
            [ 'migration', 'status', 'batch', 'executed_at' ]
        );
    }
}

