<?php

namespace BariCli\Migration;

/**
 * AbstractMigration
 *
 * All migration files must return an anonymous class that extends this base.
 *
 * Example migration file:
 *
 *   return new class extends AbstractMigration {
 *       public function up(): void   { ... }
 *       public function down(): void { ... }
 *   };
 */
abstract class AbstractMigration {

    protected \wpdb $db;

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
    }

    /**
     * Apply the migration.
     */
    abstract public function up(): void;

    /**
     * Revert the migration.
     */
    abstract public function down(): void;

    // ─── Helpers ─────────────────────────────────────────────────────────────

    /**
     * Run a raw SQL statement (useful inside up() / down()).
     */
    protected function statement( string $sql ): void {
        $this->db->query( $sql );
    }

    /**
     * Create a table using dbDelta for safe, idempotent execution.
     *
     * @param string $sql Full CREATE TABLE statement.
     */
    protected function createTable( string $sql ): void {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * Drop a table if it exists.
     */
    protected function dropTable( string $table ): void {
        $table = esc_sql( $table );
        $this->db->query( "DROP TABLE IF EXISTS `{$table}`" );
    }

    /**
     * Check whether a table exists.
     */
    protected function tableExists( string $table ): bool {
        return (bool) $this->db->get_var(
            $this->db->prepare( 'SHOW TABLES LIKE %s', $table )
        );
    }

    /**
     * Check whether a column exists in a table.
     */
    protected function columnExists( string $table, string $column ): bool {
        $table  = esc_sql( $table );
        $result = $this->db->get_results(
            $this->db->prepare( "SHOW COLUMNS FROM `{$table}` LIKE %s", $column )
        );

        return ! empty( $result );
    }
}

