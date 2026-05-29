<?php

namespace BariCli\Migration;

/**
 * Migrator
 *
 * Core migration engine responsible for:
 *  - Bootstrapping the migrations tracking table.
 *  - Discovering pending / ran migration files.
 *  - Running pending migrations (migrate).
 *  - Rolling back the last batch (rollback).
 *  - Scaffolding new migration files (create).
 *  - Reporting the full status of all migrations (status).
 */
class Migrator {

    private \wpdb  $db;
    private string $table;
    private string $migrations_dir;

    public function __construct() {
        global $wpdb;
        $this->db             = $wpdb;
        $this->table          = $wpdb->prefix . '_migrations';
        $this->migrations_dir = BARI_MIGRATIONS_DIR;
    }

    // ─── Public API ──────────────────────────────────────────────────────────

    /**
     * Scaffold a new migration file and return its absolute path.
     *
     * @param string $name Human-readable migration name (e.g. "create users table").
     */
    public function create( string $name ): string {
        $this->ensureDirectory();

        $timestamp = gmdate( 'YmdHis' );
        $slug      = strtolower( trim( preg_replace( '/[^a-zA-Z0-9]+/', '_', $name ), '_' ) );
        $filename  = "{$timestamp}_{$slug}.php";
        $filepath  = $this->migrations_dir . '/' . $filename;

        $stub = file_get_contents( BARI_CLI_DIR . '/src/Migration/stubs/migration.stub' );
        file_put_contents( $filepath, $stub );

        return $filepath;
    }

    /**
     * Run all pending migrations and return the list of files that were executed.
     *
     * @return string[] Migration filenames that were run.
     */
    public function migrate(): array {
        $this->ensureTable();

        $pending = $this->getPending();

        if ( empty( $pending ) ) {
            return [];
        }

        $batch = $this->getNextBatch();
        $ran   = [];

        foreach ( $pending as $filename ) {
            $filepath = $this->migrations_dir . '/' . $filename;

            /** @var AbstractMigration $migration */
            $migration = $this->loadMigration( $filepath );
            $migration->up();

            $this->db->insert(
                $this->table,
                [
                    'migration'   => $filename,
                    'batch'       => $batch,
                    'executed_at' => current_time( 'mysql' ),
                ],
                [ '%s', '%d', '%s' ]
            );

            $ran[] = $filename;
        }

        return $ran;
    }

    /**
     * Roll back the last batch of migrations and return the files reverted.
     *
     * @return string[] Migration filenames that were rolled back.
     */
    public function rollback(): array {
        $this->ensureTable();

        $last_batch = $this->getLastBatch();

        if ( ! $last_batch ) {
            return [];
        }

        $rows = $this->db->get_results(
            $this->db->prepare(
                "SELECT migration FROM {$this->table} WHERE batch = %d ORDER BY id DESC",
                $last_batch
            ),
            ARRAY_A
        );

        $rolled_back = [];

        foreach ( $rows as $row ) {
            $filename = $row['migration'];
            $filepath = $this->migrations_dir . '/' . $filename;

            if ( file_exists( $filepath ) ) {
                /** @var AbstractMigration $migration */
                $migration = $this->loadMigration( $filepath );
                $migration->down();
            }

            $this->db->delete( $this->table, [ 'migration' => $filename ], [ '%s' ] );
            $rolled_back[] = $filename;
        }

        return $rolled_back;
    }

    /**
     * Return an array of status rows for every migration file discovered.
     *
     * Each row: [ 'migration' => string, 'batch' => int|null, 'status' => 'Ran'|'Pending', 'executed_at' => string|null ]
     *
     * @return array<int, array<string, mixed>>
     */
    public function status(): array {
        $this->ensureTable();

        $ran_rows = $this->db->get_results(
            "SELECT migration, batch, executed_at FROM {$this->table} ORDER BY id ASC",
            ARRAY_A
        );

        $ran_index = [];
        foreach ( $ran_rows as $row ) {
            $ran_index[ $row['migration'] ] = $row;
        }

        $all_files = $this->getMigrationFiles();
        $status    = [];

        foreach ( $all_files as $filename ) {
            if ( isset( $ran_index[ $filename ] ) ) {
                $status[] = [
                    'migration'   => $filename,
                    'batch'       => (int) $ran_index[ $filename ]['batch'],
                    'status'      => 'Ran',
                    'executed_at' => $ran_index[ $filename ]['executed_at'],
                ];
            } else {
                $status[] = [
                    'migration'   => $filename,
                    'batch'       => null,
                    'status'      => 'Pending',
                    'executed_at' => null,
                ];
            }
        }

        return $status;
    }

    // ─── Internal Helpers ────────────────────────────────────────────────────

    /**
     * Return filenames of migrations not yet recorded in the tracking table.
     *
     * @return string[]
     */
    private function getPending(): array {
        $ran = $this->getRan();
        $all = $this->getMigrationFiles();

        return array_values( array_diff( $all, $ran ) );
    }

    /**
     * Return filenames that have already been executed, ordered by id.
     *
     * @return string[]
     */
    private function getRan(): array {
        $rows = $this->db->get_col(
            "SELECT migration FROM {$this->table} ORDER BY id ASC"
        );

        return $rows ?: [];
    }

    /**
     * Return all migration filenames found in the migrations directory, sorted ascending.
     *
     * @return string[]
     */
    private function getMigrationFiles(): array {
        if ( ! is_dir( $this->migrations_dir ) ) {
            return [];
        }

        $files = glob( $this->migrations_dir . '/*.php' );

        if ( ! $files ) {
            return [];
        }

        sort( $files );

        return array_map( 'basename', $files );
    }

    /**
     * Return the batch number to assign to the next migrate run.
     */
    private function getNextBatch(): int {
        $last = $this->getLastBatch();

        return $last + 1;
    }

    /**
     * Return the highest batch number currently in the tracking table (0 if empty).
     */
    private function getLastBatch(): int {
        $max = $this->db->get_var( "SELECT MAX(batch) FROM {$this->table}" );

        return $max ? (int) $max : 0;
    }

    /**
     * Load a migration file and return its AbstractMigration instance.
     *
     * Each file is loaded in an isolated closure scope to prevent conflicts
     * when multiple migrations are loaded during the same request.
     *
     * @param string $filepath Absolute path to the migration file.
     */
    private function loadMigration( string $filepath ): AbstractMigration {
        $migration = ( static function ( string $file ): AbstractMigration {
            return require $file;
        } )( $filepath );

        if ( ! $migration instanceof AbstractMigration ) {
            throw new \RuntimeException(
                sprintf(
                    'Migration file "%s" must return an instance of AbstractMigration.',
                    basename( $filepath )
                )
            );
        }

        return $migration;
    }

    /**
     * Create the migrations tracking table if it does not already exist.
     */
    public function ensureTable(): void {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $this->db->get_charset_collate();

        $sql = "CREATE TABLE {$this->table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            migration varchar(255) NOT NULL,
            batch int(11) NOT NULL,
            executed_at datetime DEFAULT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY migration (migration)
        ) {$charset_collate};";

        dbDelta( $sql );
    }

    /**
     * Create the migrations directory if it does not already exist.
     */
    private function ensureDirectory(): void {
        if ( ! is_dir( $this->migrations_dir ) ) {
            mkdir( $this->migrations_dir, 0755, true );
        }
    }
}

